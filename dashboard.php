<?php
/** Painel — Visão Geral do escritório (protótipo visual, dados fictícios). */
require_once __DIR__ . '/includes/config.php';

$pagina_titulo    = 'Visão Geral — ' . APP_NAME;
$painel_titulo    = 'Visão Geral';
$painel_subtitulo = 'Panorama do escritório — agosto de ' . date('Y');

$scripts_extra = [asset('assets/js/dados.js')];

// Valores de primeiro acesso renderizados no servidor. Assim que dados.js
// carrega, ele os substitui pelo que houver no localStorage — a chave em
// 'metrica' é o que liga cada cartão ao seu valor.
$indicadores = [
    ['metrica' => 'pecas',     'rotulo' => 'Peças Geradas no Mês', 'valor' => '0', 'variacao' => 'Nenhuma peça gerada ainda'],
    ['metrica' => 'contratos', 'rotulo' => 'Contratos Analisados', 'valor' => '0', 'variacao' => 'Aguardando primeira análise'],
    ['metrica' => 'horas',     'rotulo' => 'Horas Economizadas',   'valor' => '0', 'variacao' => 'Inicie o uso para contabilizar'],
    ['metrica' => 'clientes',  'rotulo' => 'Clientes Ativos',      'valor' => '0', 'variacao' => 'Nenhum cliente cadastrado'],
];

$atalhos = [
    ['titulo' => 'Gerar nova peça',        'texto' => 'Petição inicial, contestação, recurso ou agravo em minutos.', 'file' => 'gerador-de-pecas.php'],
    ['titulo' => 'Auditar um contrato',    'texto' => 'Cole a minuta e receba riscos, omissões e recomendações.',    'file' => 'analisador-de-contratos.php'],
    ['titulo' => 'Abrir minha carteira',   'texto' => 'Clientes, números de processo e peças arquivadas.',           'file' => 'meus-clientes.php'],
];

// Primeiro acesso: nenhum histórico registrado.
$atividades = [];

$distribuicao = [
    ['area' => 'Cível & Processo Civil',   'percentual' => 0],
    ['area' => 'Trabalhista',              'percentual' => 0],
    ['area' => 'Consumidor',               'percentual' => 0],
    ['area' => 'Família & Sucessões',      'percentual' => 0],
    ['area' => 'Penal',                    'percentual' => 0],
];

require __DIR__ . '/includes/header-painel.php';
?>

<!-- Orientação ao avaliador -->
<section class="revelar mb-5 xl:mb-6" aria-label="Ambiente de demonstração">
  <div class="cartao overflow-hidden rounded-lg border-gold/[0.3]">
    <div class="h-px w-full bg-gradient-to-r from-transparent via-gold to-transparent"></div>
    <div class="px-6 py-6 sm:px-8 sm:py-7">
      <p class="rotulo-secao text-[9.5px] text-gold/80">Ambiente de Demonstração</p>
      <p class="mt-3 max-w-[760px] text-[14.5px] leading-[1.75] text-slate-200 sm:text-[15px]">
        <span class="font-medium text-silk">Ambiente de Demonstração Peticiona AI</span>
        &mdash; Utilize os atalhos abaixo ou o menu lateral para iniciar a geração de
        peças processuais ou auditoria de contratos.
      </p>
    </div>
  </div>
</section>

<!-- Indicadores -->
<section aria-label="Indicadores do escritório">
  <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 xl:gap-5">
    <?php foreach ($indicadores as $i => $ind): ?>
      <article class="cartao cartao-fio revelar overflow-hidden rounded-lg p-6" data-atraso="<?= $i * 70 ?>">
        <p class="text-[11px] uppercase tracking-[0.18em] text-silver"><?= e($ind['rotulo']) ?></p>
        <p class="mt-4 text-[40px] font-light leading-none text-silk"
           data-metrica="<?= e($ind['metrica']) ?>"><?= e($ind['valor']) ?></p>
        <div class="filete-progresso mt-5 rounded-full">
          <span data-metrica-barra="<?= e($ind['metrica']) ?>" style="width:0%"></span>
        </div>
        <p class="mt-3 text-[12px] text-gold/80"
           data-metrica-nota="<?= e($ind['metrica']) ?>"><?= e($ind['variacao']) ?></p>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<!-- Atalhos -->
<section class="mt-8 lg:mt-10" aria-label="Atalhos rápidos">
  <div class="flex items-baseline justify-between gap-4">
    <h2 class="text-[22px] font-bold leading-tight tracking-[-0.015em] text-silk sm:text-[26px]">
      Atalhos <span class="text-gold">Rápidos</span>
    </h2>
    <span class="hidden h-px flex-1 bg-gradient-to-r from-gold/25 to-transparent sm:block"></span>
  </div>

  <div class="mt-6 grid gap-4 md:grid-cols-3 xl:gap-5">
    <?php foreach ($atalhos as $i => $atalho): ?>
      <a href="<?= e($atalho['file']) ?>"
         class="cartao cartao-fio link-seta revelar group block overflow-hidden rounded-lg p-6 sm:p-7"
         data-atraso="<?= $i * 80 ?>">
        <span class="ordinal text-[12px] text-gold/[0.55]"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
        <h3 class="mt-4 text-[18px] font-medium text-silk transition-colors duration-500 group-hover:text-gold">
          <?= e($atalho['titulo']) ?>
        </h3>
        <p class="mt-3 text-[13.5px] leading-relaxed text-silver"><?= e($atalho['texto']) ?></p>
        <p class="mt-6 text-[12.5px] uppercase tracking-[0.16em] text-gold">
          Abrir <span class="seta ml-1">&rarr;</span>
        </p>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- Atividades + distribuição -->
<section class="mt-8 grid gap-4 lg:mt-10 lg:grid-cols-[1.65fr_1fr] xl:gap-5" aria-label="Histórico e distribuição">

  <article class="cartao revelar overflow-hidden rounded-lg">
    <div class="flex items-center justify-between gap-4 border-b border-gold/[0.1] px-6 py-5 sm:px-7">
      <h2 class="text-[16px] font-medium text-silk">Atividades Recentes</h2>
      <span class="rotulo-secao text-[9px] text-gold/70">Últimas 48 h</span>
    </div>

    <!-- Estado vazio e lista convivem: dados.js alterna entre os dois conforme
         o acervo, sem recarregar a página. -->
    <div data-vazio="atividades" class="flex min-h-[260px] flex-col items-center justify-center px-8 py-14 text-center">
      <p class="max-w-[380px] text-[14px] leading-[1.75] text-silver">
        Nenhuma atividade recente registrada.
        <span class="block text-silver/70">Suas ações aparecerão aqui em tempo real.</span>
      </p>
      <span class="mt-7 block h-px w-14 bg-gold/30"></span>
    </div>

    <ul data-lista="atividades" class="hidden"></ul>

    <div class="border-t border-gold/[0.1] px-6 py-4 sm:px-7">
      <a href="gerador-de-pecas.php" class="link-seta text-[12.5px] uppercase tracking-[0.16em] text-gold">
        <span data-rodape="atividades">Gerar a primeira peça</span> <span class="seta ml-1">&rarr;</span>
      </a>
    </div>
  </article>

  <article class="cartao revelar overflow-hidden rounded-lg" data-atraso="120">
    <div class="border-b border-gold/[0.1] px-6 py-5 sm:px-7">
      <h2 class="text-[16px] font-medium text-silk">Distribuição por Área</h2>
      <p class="mt-1 text-[12px] text-silver">Peças geradas no mês corrente</p>
    </div>

    <div class="space-y-5 px-6 py-6 sm:px-7">
      <?php foreach ($distribuicao as $linha): ?>
        <div data-area="<?= e($linha['area']) ?>">
          <div class="flex items-baseline justify-between gap-4">
            <p class="text-[13.5px] text-silk"><?= e($linha['area']) ?></p>
            <p class="ordinal text-[13px] text-gold" data-area-percentual>0%</p>
          </div>
          <div class="filete-progresso mt-2.5 rounded-full">
            <span data-area-barra style="width:0%"></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="border-t border-gold/[0.1] px-6 py-5 sm:px-7">
      <p class="text-[12px] leading-relaxed text-silver/70" data-distribuicao-nota>
        Aguardando primeiras produções.
      </p>
    </div>
  </article>
</section>

<?php require __DIR__ . '/includes/footer-painel.php'; ?>
