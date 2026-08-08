<?php
/** Painel — Meus Clientes & Processos (protótipo visual, carteira fictícia). */
require_once __DIR__ . '/includes/config.php';

$pagina_titulo    = 'Meus Clientes — ' . APP_NAME;
$painel_titulo    = 'Meus Clientes & Processos';
$painel_subtitulo = 'Carteira, números de processo e peças arquivadas';

$clientes = [
    [
        'nome'      => 'Marcelo Andrade Ribeiro',
        'documento' => 'CPF 312.884.770-19',
        'processo'  => '1029384-55.2026.8.26.0100',
        'area'      => 'Cível',
        'vara'      => '7.ª Vara Cível — Foro Central/SP',
        'situacao'  => 'Em andamento',
        'pecas'     => 6,
        'ultima'    => 'Petição inicial · hoje',
    ],
    [
        'nome'      => 'Metalúrgica Vertentes Ltda.',
        'documento' => 'CNPJ 08.442.190/0001-63',
        'processo'  => '0011257-84.2026.5.02.0043',
        'area'      => 'Trabalhista',
        'vara'      => '43.ª Vara do Trabalho de São Paulo',
        'situacao'  => 'Aguardando audiência',
        'pecas'     => 9,
        'ultima'    => 'Contestação · hoje',
    ],
    [
        'nome'      => 'Beatriz Camargo Nogueira',
        'documento' => 'CPF 447.201.338-06',
        'processo'  => '1004471-12.2026.8.26.0011',
        'area'      => 'Família',
        'vara'      => '2.ª Vara de Família — Pinheiros/SP',
        'situacao'  => 'Sentença publicada',
        'pecas'     => 12,
        'ultima'    => 'Resumo ao cliente · ontem',
    ],
    [
        'nome'      => 'Nexus Participações S.A.',
        'documento' => 'CNPJ 21.760.884/0001-05',
        'processo'  => 'Consultivo — sem distribuição',
        'area'      => 'Empresarial',
        'vara'      => 'Assessoria contratual',
        'situacao'  => 'Auditoria concluída',
        'pecas'     => 4,
        'ultima'    => 'Parecer contratual · ontem',
    ],
    [
        'nome'      => 'Condomínio Alto das Palmas',
        'documento' => 'CNPJ 33.918.472/0001-88',
        'processo'  => '2087654-31.2026.8.26.0000',
        'area'      => 'Cível',
        'vara'      => '12.ª Câmara de Direito Privado — TJSP',
        'situacao'  => 'Agravo em análise',
        'pecas'     => 7,
        'ultima'    => 'Agravo de instrumento · ontem',
    ],
    [
        'nome'      => 'Rodrigo Peixoto Vilela',
        'documento' => 'CPF 190.554.802-77',
        'processo'  => '1503112-09.2026.8.26.0602',
        'area'      => 'Consumidor',
        'vara'      => '3.ª Vara Cível — Sorocaba/SP',
        'situacao'  => 'Réplica pendente',
        'pecas'     => 3,
        'ultima'    => 'Tutela deferida · há 3 dias',
    ],
    [
        'nome'      => 'Ana Lúcia Ferraz Monteiro',
        'documento' => 'CPF 605.339.114-20',
        'processo'  => '0004488-70.2026.8.26.0050',
        'area'      => 'Penal',
        'vara'      => '9.ª Vara Criminal — Barra Funda/SP',
        'situacao'  => 'Resposta apresentada',
        'pecas'     => 5,
        'ultima'    => 'Resposta à acusação · há 5 dias',
    ],
    [
        'nome'      => 'Construtora Íris Empreendimentos',
        'documento' => 'CNPJ 45.221.907/0001-44',
        'processo'  => '1077321-46.2026.8.26.0100',
        'area'      => 'Cível',
        'vara'      => '21.ª Vara Cível — Foro Central/SP',
        'situacao'  => 'Cumprimento de sentença',
        'pecas'     => 11,
        'ultima'    => 'Cálculo homologado · há 6 dias',
    ],
];

$resumo = [
    ['rotulo' => 'Clientes ativos',      'valor' => (string) count($clientes)],
    ['rotulo' => 'Processos em curso',   'valor' => '7'],
    ['rotulo' => 'Peças arquivadas',     'valor' => (string) array_sum(array_column($clientes, 'pecas'))],
    ['rotulo' => 'Prazos nos próximos 7 dias', 'valor' => '4'],
];

require __DIR__ . '/includes/header-painel.php';
?>

<!-- Resumo da carteira -->
<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 xl:gap-5" aria-label="Resumo da carteira">
  <?php foreach ($resumo as $i => $r): ?>
    <article class="cartao revelar rounded-lg px-6 py-5" data-atraso="<?= $i * 70 ?>">
      <p class="text-[10.5px] uppercase tracking-[0.18em] text-silver"><?= e($r['rotulo']) ?></p>
      <p class="mt-3 text-[32px] font-light leading-none text-silk"><?= e($r['valor']) ?></p>
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
      <tbody>
        <?php foreach ($clientes as $c): ?>
          <?php $chave = $c['nome'] . ' ' . $c['documento'] . ' ' . $c['processo'] . ' ' . $c['area'] . ' ' . $c['situacao']; ?>
          <tr data-cliente="<?= e($chave) ?>">
            <td class="px-7 py-5">
              <p class="whitespace-nowrap text-[14px] text-silk"><?= e($c['nome']) ?></p>
              <p class="mt-1 text-[11.5px] text-silver"><?= e($c['documento']) ?></p>
            </td>
            <td class="px-4 py-5">
              <p class="whitespace-nowrap text-[13px] tabular-nums text-silver"><?= e($c['processo']) ?></p>
              <p class="mt-1 whitespace-nowrap text-[11.5px] text-silver/[0.65]"><?= e($c['vara']) ?></p>
            </td>
            <td class="px-4 py-5">
              <span class="whitespace-nowrap rounded-sm border border-sapphire/30 px-2.5 py-1 text-[10px] uppercase tracking-[0.16em] text-sapphire">
                <?= e($c['area']) ?>
              </span>
            </td>
            <td class="px-4 py-5">
              <p class="whitespace-nowrap text-[13px] text-gold/[0.85]"><?= e($c['situacao']) ?></p>
            </td>
            <td class="px-4 py-5 text-right">
              <p class="ordinal text-[15px] text-silk"><?= (int) $c['pecas'] ?></p>
            </td>
            <td class="px-7 py-5 text-right">
              <p class="whitespace-nowrap text-[12.5px] text-silver"><?= e($c['ultima']) ?></p>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Cards (mobile e tablet) -->
  <div class="lg:hidden">
    <?php foreach ($clientes as $i => $c): ?>
      <?php $chave = $c['nome'] . ' ' . $c['documento'] . ' ' . $c['processo'] . ' ' . $c['area'] . ' ' . $c['situacao']; ?>
      <article data-cliente="<?= e($chave) ?>"
               class="px-6 py-5 sm:px-7 <?= $i > 0 ? 'border-t border-gold/[0.07]' : '' ?>">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div class="min-w-0">
            <p class="text-[15px] text-silk"><?= e($c['nome']) ?></p>
            <p class="mt-1 text-[11.5px] text-silver"><?= e($c['documento']) ?></p>
          </div>
          <span class="shrink-0 rounded-sm border border-sapphire/30 px-2.5 py-1 text-[10px] uppercase tracking-[0.16em] text-sapphire">
            <?= e($c['area']) ?>
          </span>
        </div>

        <dl class="mt-4 space-y-2.5">
          <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
            <dt class="text-[11px] uppercase tracking-[0.16em] text-silver/70">Processo</dt>
            <dd class="text-[12.5px] tabular-nums text-silver"><?= e($c['processo']) ?></dd>
          </div>
          <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
            <dt class="text-[11px] uppercase tracking-[0.16em] text-silver/70">Juízo</dt>
            <dd class="text-[12.5px] text-silver"><?= e($c['vara']) ?></dd>
          </div>
          <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
            <dt class="text-[11px] uppercase tracking-[0.16em] text-silver/70">Situação</dt>
            <dd class="text-[12.5px] text-gold/[0.85]"><?= e($c['situacao']) ?></dd>
          </div>
          <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
            <dt class="text-[11px] uppercase tracking-[0.16em] text-silver/70">Peças salvas</dt>
            <dd class="ordinal text-[13px] text-silk"><?= (int) $c['pecas'] ?></dd>
          </div>
        </dl>

        <p class="mt-4 border-t border-gold/[0.07] pt-3 text-[12px] text-silver/70">
          Última movimentação: <?= e($c['ultima']) ?>
        </p>
      </article>
    <?php endforeach; ?>
  </div>

  <div class="border-t border-gold/[0.1] px-6 py-5 sm:px-7">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <p class="text-[12px] text-silver/70">
        Registros fictícios exibidos para demonstração da interface.
      </p>
      <a href="gerador-de-pecas.php" class="link-seta text-[12.5px] uppercase tracking-[0.16em] text-gold">
        Gerar peça para um cliente <span class="seta ml-1">&rarr;</span>
      </a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer-painel.php'; ?>
