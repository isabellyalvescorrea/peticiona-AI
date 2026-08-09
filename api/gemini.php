<?php
/**
 * Ponte servidor → Gemini.
 *
 * O front-end nunca fala com a API do Google diretamente. Ele faz POST aqui, e
 * é esta função que acrescenta a chave e encaminha. O motivo é simples: todo
 * JavaScript do projeto é servido publicamente (assets/js/*.js responde 200 a
 * qualquer visitante), então uma chave embutida no cliente ficaria legível para
 * qualquer pessoa que abrisse o navegador — e seria consumida na conta de quem
 * a publicou.
 *
 * A chave vem da variável de ambiente GEMINI_API_KEY, configurada no painel da
 * Vercel. Enquanto ela não existir, o endpoint responde em modo simulado, com
 * a flag "simulado": true — o que permite exercitar o fluxo inteiro (payload,
 * renderização, gravação em JSON, recálculo do painel) sem chave nenhuma.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

/** Encerra a requisição devolvendo JSON. */
function responder(array $corpo, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($corpo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    responder([
        'erro' => 'Método não permitido. Use POST.',
        'metodoRecebido' => $_SERVER['REQUEST_METHOD'] ?? null,
    ], 405);
}

$bruto = file_get_contents('php://input') ?: '';

if (strlen($bruto) > 200000) {
    responder(['erro' => 'Payload acima do limite de 200 KB.'], 413);
}

$entrada = json_decode($bruto, true);

if (!is_array($entrada)) {
    responder(['erro' => 'Corpo da requisição não é um JSON válido.'], 400);
}

$prompt = trim((string) ($entrada['prompt'] ?? ''));
$tarefa = (string) ($entrada['tarefa'] ?? 'peca');

if ($prompt === '') {
    responder(['erro' => 'O campo "prompt" é obrigatório.'], 422);
}

if (!in_array($tarefa, ['peca', 'auditoria'], true)) {
    responder(['erro' => 'Campo "tarefa" deve ser "peca" ou "auditoria".'], 422);
}

$chave  = getenv('GEMINI_API_KEY') ?: '';
$modelo = getenv('GEMINI_MODEL') ?: 'gemini-2.5-flash';

/* ------------------------------------------------------------------------
   Modo simulado — enquanto não houver chave configurada
   ------------------------------------------------------------------------ */

if ($chave === '') {
    require_once __DIR__ . '/../includes/simulacao-gemini.php';

    responder([
        'simulado'  => true,
        'modelo'    => $modelo,
        'tarefa'    => $tarefa,
        'texto'     => resposta_simulada($tarefa, $entrada),
        'aviso'     => 'GEMINI_API_KEY não configurada. Resposta gerada localmente para validação do fluxo.',
        'recebidoEm'=> gmdate('c'),
    ]);
}

/* ------------------------------------------------------------------------
   Chamada real
   ------------------------------------------------------------------------ */

$url = sprintf(
    'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
    rawurlencode($modelo)
);

$corpo = [
    'contents' => [[
        'role'  => 'user',
        'parts' => [['text' => $prompt]],
    ]],
    'generationConfig' => [
        'temperature'     => 0.35,   // técnica jurídica pede consistência, não criatividade
        'topP'            => 0.9,
        'maxOutputTokens' => 8192,
    ],
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 60,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'x-goog-api-key: ' . $chave,
    ],
    CURLOPT_POSTFIELDS => json_encode($corpo, JSON_UNESCAPED_UNICODE),
]);

$resposta = curl_exec($ch);
$status   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$erroCurl = curl_error($ch);
curl_close($ch);

if ($resposta === false) {
    responder(['erro' => 'Falha de rede ao contatar o Gemini.', 'detalhe' => $erroCurl], 502);
}

$dados = json_decode((string) $resposta, true);

if ($status !== 200 || !is_array($dados)) {
    responder([
        'erro'      => 'O Gemini respondeu com erro.',
        'status'    => $status,
        // A mensagem do Google é devolvida sem a chave, que nunca entra no corpo.
        'detalhe'   => is_array($dados) ? ($dados['error']['message'] ?? null) : null,
    ], 502);
}

$texto = '';
foreach ($dados['candidates'][0]['content']['parts'] ?? [] as $parte) {
    $texto .= $parte['text'] ?? '';
}

if (trim($texto) === '') {
    responder([
        'erro'   => 'O Gemini não retornou texto.',
        'motivo' => $dados['candidates'][0]['finishReason'] ?? null,
    ], 502);
}

responder([
    'simulado'   => false,
    'modelo'     => $modelo,
    'tarefa'     => $tarefa,
    'texto'      => $texto,
    'uso'        => $dados['usageMetadata'] ?? null,
    'recebidoEm' => gmdate('c'),
]);
