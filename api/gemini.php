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
 * A escolha é ditada pelo que a chave realmente alcança, e não pela cota
 * nominal. O Google restringe os modelos mais antigos a contas existentes:
 * gemini-1.5-flash, gemini-2.0-flash e gemini-2.5-flash devolvem 404 aqui
 * ("no longer available to new users"), por mais generoso que seja o limite
 * publicado para eles.
 *
 * O gemini-3.6-flash é o que esta chave atende — produziu peças completas em
 * 25 a 33 s. Ele raciocina antes de escrever, o que o deixa lento; por isso o
 * thinkingConfig abaixo mantém esse esforço no nível baixo.
 */
const MODELO_PADRAO = 'gemini-3.6-flash';

/**
 * Alternativas, em ordem de uso. Cada modelo tem balde de cota próprio, então
 * a redundância cobre tanto congestionamento quanto limite atingido — e agora
 * também indisponibilidade, já que um 404 avança a fila em vez de encerrá-la.
 *
 * gemini-flash-latest fecha a fila por ser um apelido que o Google aponta para
 * um modelo vigente: sobrevive à aposentadoria de qualquer nome específico.
 */
const MODELO_RESERVA = 'gemini-3.5-flash';
const MODELO_ULTIMO  = 'gemini-flash-latest';

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


function url_do_modelo(string $modelo): string
{
    return sprintf(
        'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
        rawurlencode($modelo)
    );
}

/**
 * Uma tentativa de geração. Devolve [status, dadosDecodificados, erroCurl].
 * $limite é o tempo máximo em segundos, para que a soma das tentativas caiba
 * no teto de 60 s da função.
 */
function gerar(string $url, string $chave, string $prompt, float $temperatura, int $tetoSaida, int $limite = 55): array
{
    $config = [
        'temperature'     => $temperatura,
        'topP'            => 0.9,
        'maxOutputTokens' => $tetoSaida,
    ];

    /**
     * O gemini-3.6-flash raciocina bastante antes de escrever: num prompt de
     * 6 tokens gastou 96 tokens em raciocínio, e com o prompt de uma peça
     * inteira chegou a não emitir byte algum em 55 s. Redigir peça é geração
     * estruturada, não dedução difícil, então o nível baixo devolve dentro do
     * tempo da função sem perder técnica.
     *
     * A linha 2.0 não tem raciocínio prévio e recusaria o campo.
     */
    if (!str_starts_with($url, 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0')) {
        $config['thinkingConfig'] = ['thinkingLevel' => 'low'];
    }

    $corpo = [
        'contents' => [[
            'role'  => 'user',
            'parts' => [['text' => $prompt]],
        ]],
        'generationConfig' => $config,
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        // Abaixo do maxDuration da função (60 s), para que um Gemini lento vire
        // um JSON de erro legível em vez de um corte seco da plataforma.
        CURLOPT_TIMEOUT        => $limite,
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
 * O teto conta raciocínio mais resposta. 8192 dá folga para a peça inteira sem
 * truncar; o que resolvia a demora não era apertar este número, e sim conter o
 * raciocínio pelo thinkingConfig. O resumo é curto por definição.
 */
$tetoSaida = $tarefa === 'resumo' ? 2048 : 8192;

/**
 * Cadeia de modelos.
 *
 * Cada modelo tem balde de cota próprio no plano gratuito, então um 429 no
 * primeiro não impede o segundo de atender. A ordem vai do mais disponível ao
 * mais sofisticado: só se chega ao fim da fila quando os anteriores recusaram.
 *
 * Um GEMINI_MODEL definido no ambiente encabeça a fila e não é descartado.
 */
$cadeia = array_values(array_unique(array_filter([
    $modelo,
    MODELO_PADRAO,
    MODELO_RESERVA,
    MODELO_ULTIMO,
])));

/**
 * Só vale trocar de modelo quando a recusa é do modelo, não do pedido:
 *   404 — aquele modelo não existe para esta chave; outro pode existir
 *   429 — cota daquele modelo esgotada; outro balde pode estar livre
 *   503 — congestionamento momentâneo
 *   500/502/504 — instabilidade do lado do Google
 *   resposta nula — silêncio até o timeout, que é indisponibilidade na prática
 *
 * O 404 esteve fora desta lista e não deveria: é o erro mais específico de
 * modelo que existe, e excluí-lo fazia a fila parar na primeira porta fechada.
 * Já 400, 401 e 403 dizem respeito ao pedido ou à chave, se repetiriam
 * idênticos em qualquer modelo, e encerram a fila na hora.
 */
function vale_tentar_outro(int $status, ?array $dados): bool
{
    return $dados === null || in_array($status, [404, 429, 500, 502, 503, 504], true);
}

$inicio    = microtime(true);
$tentativas = [];
$status = 0; $dados = null; $erroCurl = '';

foreach ($cadeia as $candidato) {
    $decorrido = (int) (microtime(true) - $inicio);
    $restante  = 55 - $decorrido;

    // Sem tempo para uma tentativa útil, a fila para aqui.
    if ($restante < 12) {
        break;
    }

    // Teto de 30 s por tentativa: preserva orçamento para o próximo da fila, e
    // um modelo que não respondeu em 30 s dificilmente responderia em 50.
    [$status, $dados, $erroCurl] = gerar(
        url_do_modelo($candidato), $chave, $prompt, $temperatura, $tetoSaida,
        min(30, $restante)
    );

    $modelo = $candidato;
    $tentativas[] = ['modelo' => $candidato, 'status' => $dados === null ? 0 : $status];

    if (!vale_tentar_outro($status, $dados)) {
        break;
    }
}

if ($dados === null) {
    responder([
        'erro'       => 'Nenhum modelo do Gemini respondeu a tempo.',
        'detalhe'    => $erroCurl,
        'tentativas' => $tentativas,
        'reptivel'   => true,
    ], 502);
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
        $status === 404 => 'Nenhum dos modelos disponíveis atendeu esta chave (' .
            implode(', ', array_column($tentativas, 'modelo')) . '). ' .
            'Defina GEMINI_MODEL com um modelo que a sua conta alcance.',
        $status === 503 =>
            'O modelo está sob alta demanda no Google neste momento. Tente novamente em alguns instantes — ' .
            'se persistir, troque GEMINI_MODEL por um modelo menos concorrido.',
        default => 'O Gemini respondeu com erro.',
    };

    responder([
        'erro'       => $mensagem,
        'status'     => $status,
        'detalhe'    => $doGoogle !== '' ? $doGoogle : null,
        // Mostra o caminho percorrido: ajuda a distinguir "um modelo ocupado"
        // de "a conta inteira sem cota".
        'tentativas' => $tentativas,
        // Congestionamento passa sozinho; vale o cliente insistir.
        'reptivel'   => in_array($status, [503, 500, 502, 504], true),
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
    // Quando houve troca de modelo, o histórico explica por que a resposta
    // demorou mais e qual balde de cota acabou sendo usado.
    'tentativas' => count($tentativas) > 1 ? $tentativas : null,
    'recebidoEm' => gmdate('c'),
]);
