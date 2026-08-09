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

if (!in_array($tarefa, ['peca', 'auditoria', 'resumo'], true)) {
    responder(['erro' => 'Campo "tarefa" deve ser "peca", "auditoria" ou "resumo".'], 422);
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
function gerar(string $url, string $chave, string $prompt, float $temperatura, int $tetoSaida): array
{
    $corpo = [
        'contents' => [[
            'role'  => 'user',
            'parts' => [['text' => $prompt]],
        ]],
        'generationConfig' => [
            'temperature'     => $temperatura,
            'topP'            => 0.9,
            'maxOutputTokens' => $tetoSaida,
        ],
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        // Abaixo do maxDuration da função (60 s), para que um Gemini lento vire
        // um JSON de erro legível em vez de um corte seco da plataforma. Sobram
        // 5 s para serializar a resposta e devolver.
        CURLOPT_TIMEOUT        => 55,
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
 * Uma única tentativa por requisição.
 *
 * Uma peça inteira leva de 25 a 35 s. Repetir aqui dentro custaria o dobro e
 * estouraria os 60 s de teto da função, então a retentativa vive no navegador,
 * que não tem esse limite: a variação de temperatura chega pelo campo
 * "temperatura" do corpo.
 */
$temperatura = (float) ($entrada['temperatura'] ?? 0.35);
$temperatura = max(0.0, min(1.5, $temperatura));

/**
 * Teto de saída por tarefa.
 *
 * 4096 tokens equivalem a cerca de 14 mil caracteres — bem acima das peças
 * observadas, que ficam entre 8 e 10 mil. O teto anterior, de 8192, não
 * melhorava o resultado e alongava a geração: com o prompt completo ela passou
 * de 52 s e estourou o tempo da requisição. O resumo é curto por definição.
 */
$tetoSaida = $tarefa === 'resumo' ? 1536 : 4096;

[$status, $dados, $erroCurl] = gerar($url, $chave, $prompt, $temperatura, $tetoSaida);

if ($dados === null) {
    responder(['erro' => 'Falha de rede ao contatar o Gemini.', 'detalhe' => $erroCurl], 502);
}

if ($status !== 200) {
    // A mensagem do Google é devolvida sem a chave, que nunca entra no corpo.
    $doGoogle = (string) ($dados['error']['message'] ?? '');

    $mensagem = match (true) {
        $status === 429 => (function () use ($doGoogle) {
            // O Google informa quanto falta; repassar isso vale mais do que
            // "tente novamente mais tarde".
            preg_match('/retry in ([\d.]+)s/i', $doGoogle, $m);
            $espera = isset($m[1]) ? ' Tente de novo em cerca de ' . ceil((float) $m[1]) . ' segundos.' : '';
            return 'A cota da API do Gemini foi atingida.' . $espera .
                   ' No plano gratuito o limite é baixo; para uso contínuo, ative o faturamento no Google AI Studio.';
        })(),
        $status === 401 || $status === 403 =>
            'A GEMINI_API_KEY foi recusada pelo Google. Verifique se a chave está correta e ativa.',
        $status === 404 =>
            'O modelo configurado não está disponível para esta chave. Ajuste GEMINI_MODEL nas variáveis de ambiente.',
        default => 'O Gemini respondeu com erro.',
    };

    responder([
        'erro'     => $mensagem,
        'status'   => $status,
        'detalhe'  => $doGoogle !== '' ? $doGoogle : null,
        // 429 é transitório: passado o intervalo, a mesma peça costuma sair.
        'reptivel' => false,
    ], 502);
}

$texto  = extrair_texto($dados);
$motivo = $dados['candidates'][0]['finishReason'] ?? null;

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
        // Sinaliza ao cliente que insistir com outra temperatura tende a resolver.
        'reptivel' => $motivo === 'RECITATION',
    ], 502);
}

responder([
    'modelo'     => $modelo,
    'tarefa'     => $tarefa,
    'texto'      => $texto,
    'uso'        => $dados['usageMetadata'] ?? null,
    'recebidoEm' => gmdate('c'),
]);
