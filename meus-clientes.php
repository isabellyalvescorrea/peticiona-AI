<?php
/** Painel — Meus Clientes & Processos (protótipo visual). */
require_once __DIR__ . '/includes/config.php';

$pagina_titulo    = 'Meus Clientes — ' . APP_NAME;
$painel_titulo    = 'Meus Clientes & Processos';
$painel_subtitulo = 'Carteira, números de processo e peças arquivadas';

$scripts_extra = [asset('assets/js/dados.js')];

// Primeiro acesso: carteira ainda vazia. A estrutura de cada registro fica
// documentada aqui para quando a persistência entrar:
// nome, documento, processo, area, vara, situacao, pecas, ultima.
$clientes = [];

// Os contadores derivam da carteira: com ela vazia, todos exibem zero.
$resumo = [
    ['rotulo' => 'Clientes ativos',            'valor' => (string) count($clientes)],
    ['rotulo' => 'Processos em curso',         'valor' => (string) count(array_column($clientes, 'processo'))],
    ['rotulo' => 'Peças arquivadas',           'valor' => (string) array_sum(array_column($clientes, 'pecas'))],
    ['rotulo' => 'Prazos nos próximos 7 dias', 'valor' => (string) count($clientes)],
];

require __DIR__ . '/includes/header-painel.php';
?>

<!-- Resumo da carteira -->
<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 xl:gap-5" aria-label="Resumo da carteira">
  <?php $chaves_resumo = ['clientes', 'processos', 'pecas', 'prazos']; ?>
  <?php foreach ($resumo as $i => $r): ?>
    <article class="cartao revelar rounded-lg px-6 py-5" data-atraso="<?= $i * 70 ?>">
      <p class="text-[10.5px] uppercase tracking-[0.18em] text-silver"><?= e($r['rotulo']) ?></p>
      <p class="mt-3 text-[32px] font-light leading-none text-silk" data-resumo="<?= e($chaves_resumo[$i]) ?>"><?= e($r['valor']) ?></p>
    </article>
  <?php endforeach; ?>
</section>

<!-- Listagem -->
<section class="cartao revelar mt-5 overflow-hidden rounded-lg xl:mt-6" aria-label="Listagem de clientes" data-atraso="100">

  <div class="flex flex-col gap-4 border-b border-gold/[0.1] px-6 py-5 sm:px-7 lg:flex-row lg:items-center lg:justify-between">
    <div>
      <h2 class="text-[16px] font-medium text-silk">Carteira de Clientes</h2>
      <p id="contador-clientes" class="mt-1 text-[12px] text-silver"><?= count($clientes) ?> registros</p>
    </div>
    <div class="flex w-full flex-col gap-3 sm:flex-row lg:w-auto">
      <label for="filtro-clientes" class="sr-only">Filtrar clientes</label>
      <input id="filtro-clientes" type="search" autocomplete="off"
             class="campo w-full rounded-md px-4 py-2.5 text-[13.5px] sm:w-[300px]"
             placeholder="Filtrar por nome, processo ou área…">
      <button type="button" class="btn-contorno whitespace-nowrap rounded-md px-5 py-2.5 text-[13.5px] font-medium">
        Cadastrar cliente
      </button>
    </div>
  </div>

  <!-- Estado vazio e listagem convivem no DOM; dados.js alterna entre os dois
       conforme a carteira, sem recarregar a página. -->
  <div data-vazio="clientes" class="flex min-h-[320px] flex-col items-center justify-center px-8 py-16 text-center">
    <p class="titulo-secao text-silk/[0.8]">Carteira vazia</p>
    <p class="mt-5 max-w-[440px] text-[14px] leading-[1.75] text-silver">
      Nenhum cliente cadastrado na sua carteira. Cadastre um cliente ou gere uma nova
      peça para popular este espaço.
    </p>
    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
      <a href="gerador-de-pecas.php" class="btn-ouro rounded-md px-7 py-3 text-center text-[14px] font-semibold">
        Gerar nova peça
      </a>
      <a href="analisador-de-contratos.php" class="btn-contorno rounded-md px-7 py-3 text-center text-[14px] font-medium">
        Auditar um contrato
      </a>
    </div>
    <span class="mt-10 block h-px w-14 bg-gold/30"></span>
  </div>

  <div data-preenchido="clientes" class="hidden">
    <!-- Tabela (desktop) -->
    <div class="hidden overflow-x-auto lg:block">
      <table class="tabela-luxo w-full min-w-[1020px] text-left">
        <thead>
          <tr>
            <th class="px-7 py-4">Cliente</th>
            <th class="px-4 py-4">Processo</th>
            <th class="px-4 py-4">Área</th>
            <th class="px-4 py-4">Situação</th>
            <th class="px-4 py-4 text-right">Peças</th>
            <th class="px-7 py-4 text-right">Última movimentação</th>
          </tr>
        </thead>
        <tbody data-lista="clientes-tabela"></tbody>
      </table>
    </div>

    <!-- Cards (mobile e tablet) -->
    <div data-lista="clientes-cartoes" class="lg:hidden"></div>

    <div class="border-t border-gold/[0.1] px-6 py-5 sm:px-7">
      <div class="flex justify-end">
        <a href="gerador-de-pecas.php" class="link-seta text-[12.5px] uppercase tracking-[0.16em] text-gold">
          Gerar peça para um cliente <span class="seta ml-1">&rarr;</span>
        </a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer-painel.php'; ?>