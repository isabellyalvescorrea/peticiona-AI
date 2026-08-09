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
 *
 * Fica desligado por padrão: é um GET público que dispararia uma chamada
 * autenticada ao Google a cada acesso. Para usá-lo, defina GEMINI_DEBUG=1
 * nas variáveis de ambiente e remova depois.
 */
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && isset($_GET['modelos'])) {
    if ((getenv('GEMINI_DEBUG') ?: '') !== '1') {
        responder(['erro' => 'Diagnóstico desativado. Defina GEMINI_DEBUG=1 para habilitar.'], 404);
    }
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

/** Uma tentativa de geração. Devolve [status, dadosDecodificados, erroCurl]. */
function gerar(string $url, string $chave, string $prompt, float $temperatura): array
{
    $corpo = [
        'contents' => [[
            'role'  => 'user',
            'parts' => [['text' => $prompt]],
        ]],
        'generationConfig' => [
            'temperature'     => $temperatura,
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
        CURLOPT_TIMEOUT        => 24,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'x-goog-api-key: ' . $chave,
        ],
        CURLOPT_POSTFIELDS => json_encode($corpo, JSON_UNESCAPED_UNICODE),
    ]);

    $bruto  = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $erro   = curl_error($ch);
    // curl_close() não é chamado: desde o PHP 8.0 não tem efeito, e no 8.5 emite
    // deprecação — que vazaria para o corpo e quebraria o JSON.

    return [$status, $bruto === false ? null : json_decode((string) $bruto, true), $erro];
}

/** Concatena as partes textuais de um candidato. */
function extrair_texto(?array $dados): string
{
    $texto = '';
    foreach ($dados['candidates'][0]['content']['parts'] ?? [] as $parte) {
        $texto .= $parte['text'] ?? '';
    }
    return trim($texto);
}

/**
 * Peça processual é texto altamente padronizado, e o Gemini às vezes barra a
 * própria saída como RECITATION por reconhecê-la como reprodução de material
 * conhecido. O bloqueio é intermitente: variar a temperatura afasta a geração
 * do trecho memorizado e costuma resolver na segunda tentativa.
 *
 * Duas tentativas de 24 s cabem no orçamento de 60 s da função.
 */
$temperaturas = [0.35, 0.75];
$dados = null;
$status = 0;
$erroCurl = '';
$texto = '';
$motivo = null;

foreach ($temperaturas as $i => $temperatura) {
    [$status, $dados, $erroCurl] = gerar($url, $chave, $prompt, $temperatura);

    if ($dados === null) {
        responder(['erro' => 'Falha de rede ao contatar o Gemini.', 'detalhe' => $erroCurl], 502);
    }

    if ($status !== 200) {
        responder([
            'erro'    => 'O Gemini respondeu com erro.',
            'status'  => $status,
            // A mensagem do Google é devolvida sem a chave, que nunca entra no corpo.
            'detalhe' => $dados['error']['message'] ?? null,
        ], 502);
    }

    $texto  = extrair_texto($dados);
    $motivo = $dados['candidates'][0]['finishReason'] ?? null;

    if ($texto !== '') {
        break;
    }

    // Só vale insistir quando o bloqueio é do tipo que a variação resolve.
    if ($motivo !== 'RECITATION' || $i === count($temperaturas) - 1) {
        break;
    }
}

if ($texto === '') {
    $explicacoes = [
        'RECITATION' => 'O modelo interrompeu a geração por identificar o texto como reprodução de ' .
                        'material conhecido — comum em peças de linguagem muito padronizada. ' .
                        'Detalhe mais os fatos do caso e gere novamente.',
        'SAFETY'     => 'O conteúdo foi barrado pelos filtros de segurança do modelo. ' .
                        'Revise os termos empregados na descrição dos fatos.',
        'MAX_TOKENS' => 'A peça excedeu o limite de tamanho. Reduza o volume de fatos e pedidos.',
    ];

    responder([
        'erro'   => $explicacoes[$motivo] ?? 'O Gemini não retornou texto.',
        'motivo' => $motivo,
    ], 502);
}

responder([
    'modelo'     => $modelo,
    'tarefa'     => $tarefa,
    'texto'      => $texto,
    'uso'        => $dados['usageMetadata'] ?? null,
    'recebidoEm' => gmdate('c'),
]);
