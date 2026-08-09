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
 * Vercel. Não há modo simulado: faltando a chave, ou falhando a chamada, o
 * endpoint responde com erro explícito — nunca com um texto plausível que
 * pudesse ser confundido com produção da IA.
 */

declare(strict_types=1);

/**
 * Avisos do PHP jamais podem ir para o corpo: eles saem antes dos headers,
 * inutilizam http_response_code() e transformam a resposta num JSON inválido
 * para o cliente. Vão para o log da função, que é onde se lê diagnóstico.
 */
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

/**
 * Sobrescrevível pela variável de ambiente GEMINI_MODEL.
 *
 * gemini-2.5-flash foi aposentado para novos usuários e devolve 404. Quando
 * este também for, GET ?modelos=1 lista o que a chave enxerga no momento.
 */
const MODELO_PADRAO = 'gemini-3.6-flash';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

/** Encerra a requisição devolvendo JSON. */
function responder(array $corpo, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($corpo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$chave  = getenv('GEMINI_API_KEY') ?: '';
$modelo = getenv('GEMINI_MODEL') ?: MODELO_PADRAO;

/**
 * Diagnóstico: GET ?modelos=1 devolve os modelos que a chave enxerga hoje.
 * Existe porque a disponibilidade muda com o tempo — um modelo válido na
 * escrita do código pode ser aposentado depois — e adivinhar o nome custa
 * um deploy a cada tentativa.
 */
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && isset($_GET['modelos'])) {
    if ($chave === '') {
        responder(['erro' => 'GEMINI_API_KEY não configurada.'], 503);
    }

    $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models?pageSize=200');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => ['x-goog-api-key: ' . $chave],
    ]);
    $bruto  = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $lista  = json_decode((string) $bruto, true);

    if ($status !== 200 || !is_array($lista)) {
        responder(['erro' => 'Não foi possível listar modelos.', 'status' => $status], 502);
    }

    $geram = [];
    foreach ($lista['models'] ?? [] as $m) {
        if (in_array('generateContent', $m['supportedGenerationMethods'] ?? [], true)) {
            $geram[] = str_replace('models/', '', $m['name'] ?? '');
        }
    }

    responder(['modeloEmUso' => $modelo, 'disponiveis' => $geram]);
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

/* ------------------------------------------------------------------------
   Chamada real ao Gemini

   Sem simulação: se a chave faltar, o endpoint diz isso em vez de devolver um
   texto plausível que passaria por resposta da IA.
   ------------------------------------------------------------------------ */

if ($chave === '') {
    responder([
        'erro' => 'GEMINI_API_KEY não está configurada no ambiente. ' .
                  'Defina-a em Vercel → Settings → Environment Variables e refaça o deploy.',
    ], 503);
}

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
    // Abaixo do maxDuration da função (60 s), para que um Gemini lento vire
    // um JSON de erro legível em vez de um corte seco da plataforma.
    CURLOPT_TIMEOUT        => 52,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'x-goog-api-key: ' . $chave,
    ],
    CURLOPT_POSTFIELDS => json_encode($corpo, JSON_UNESCAPED_UNICODE),
]);

$resposta = curl_exec($ch);
$status   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$erroCurl = curl_error($ch);
// curl_close() não é chamado: desde o PHP 8.0 não tem efeito, e no 8.5 emite
// deprecação — que vazaria para o corpo e quebraria o JSON.

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
    'modelo'     => $modelo,
    'tarefa'     => $tarefa,
    'texto'      => $texto,
    'uso'        => $dados['usageMetadata'] ?? null,
    'recebidoEm' => gmdate('c'),
]);
