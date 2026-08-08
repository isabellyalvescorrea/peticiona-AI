<?php
/**
 * Controlador de entrada para a Vercel.
 *
 * O runtime PHP da Vercel só reconhece funções dentro de /api, enquanto as
 * páginas deste projeto vivem na raiz. Em vez de mover os arquivos — o que
 * quebraria os caminhos dos includes e as URLs do desenvolvimento local —
 * este arquivo traduz a rota pedida para a página correspondente.
 *
 * A rota chega pelo parâmetro __rota, injetado pelas regras do vercel.json.
 * Depender dele, e não de REQUEST_URI, torna o resultado independente de como
 * a plataforma reescreve a URL internamente.
 *
 * Em desenvolvimento local este arquivo não é usado: `php -S` serve as páginas
 * da raiz diretamente.
 */

declare(strict_types=1);

$raiz = dirname(__DIR__);

/** Páginas servíveis. Tudo fora desta lista responde 404 — inclusive includes/. */
const PAGINAS_PUBLICAS = [
    'index.php',
    'dashboard.php',
    'gerador-de-pecas.php',
    'analisador-de-contratos.php',
    'meus-clientes.php',
];

$rota = (string) ($_GET['__rota'] ?? '');

// Normaliza: remove barras nas pontas, descarta query string residual e
// impede qualquer travessia de diretório.
$rota = trim($rota, '/');
$rota = (string) strtok($rota, '?');
$rota = str_replace('\\', '/', $rota);

if ($rota === '' || $rota === 'index') {
    $rota = 'index.php';
}

if (!str_ends_with($rota, '.php')) {
    $rota .= '.php';
}

// basename() elimina qualquer "../" antes da checagem na lista.
$rota = basename($rota);

if (!in_array($rota, PAGINAS_PUBLICAS, true)) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$arquivo = $raiz . '/' . $rota;

if (!is_file($arquivo)) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// O painel marca o item ativo do menu por basename(SCRIPT_NAME). Sem este
// ajuste, todas as telas internas enxergariam "api/index.php" e nenhuma
// apareceria como ativa.
$_SERVER['SCRIPT_NAME']  = '/' . $rota;
$_SERVER['PHP_SELF']     = '/' . $rota;
$_SERVER['SCRIPT_FILENAME'] = $arquivo;

require $arquivo;
