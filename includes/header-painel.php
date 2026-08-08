<?php
/**
 * Layout do painel logado — barra lateral fixa + barra superior.
 * Espera $pagina_titulo, $painel_titulo e (opcional) $painel_subtitulo.
 */
require_once __DIR__ . '/config.php';

$painel_titulo    = $painel_titulo    ?? 'Painel';
$painel_subtitulo = $painel_subtitulo ?? '';
require __DIR__ . '/head.php';
?>

<!-- Véu do menu lateral no mobile -->
<div id="veu-painel" class="fixed inset-0 z-40 hidden bg-black/70 lg:hidden"></div>

<!-- ============================= LATERAL ============================= -->
<aside id="lateral-painel"
       class="fixed inset-y-0 left-0 z-50 flex w-[268px] -translate-x-full flex-col border-r border-gold/[0.12] bg-navy transition-transform duration-500 lg:translate-x-0">

  <div class="flex h-[72px] shrink-0 items-center border-b border-gold/[0.12] px-6">
    <a href="index.php" aria-label="<?= e(APP_NAME) ?> — página inicial">
      <img src="<?= e(asset('assets/img/logo.png')) ?>" alt="<?= e(APP_NAME) ?>" class="h-7 w-auto">
    </a>
  </div>

  <nav class="flex-1 overflow-y-auto px-4 py-7" aria-label="Navegação do painel">
    <p class="rotulo-secao px-3 text-[9px] text-gold/70">Ambiente do Advogado</p>

    <ul class="mt-4 space-y-1">
      <?php foreach (nav_painel() as $i => $item): ?>
        <?php $ativo = is_current($item['file']); ?>
        <li>
          <a href="<?= e($item['file']) ?>"
             class="group relative block rounded-md px-3 py-3 transition-colors duration-500
                    <?= $ativo ? 'bg-gold/[0.07]' : 'hover:bg-gold/[0.035]' ?>"
             <?= $ativo ? 'aria-current="page"' : '' ?>>
            <?php if ($ativo): ?>
              <span class="absolute inset-y-2 left-0 w-px bg-gold"></span>
            <?php endif; ?>
            <span class="flex items-baseline gap-3">
              <span class="ordinal text-[10.5px] <?= $ativo ? 'text-gold' : 'text-gold/40' ?>">
                <?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?>
              </span>
              <span class="text-[14px] <?= $ativo ? 'text-silk' : 'text-silver group-hover:text-silk' ?>">
                <?= e($item['label']) ?>
              </span>
            </span>
            <span class="mt-1 block pl-[30px] text-[11px] text-silver/[0.55]"><?= e($item['hint']) ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <p class="rotulo-secao mt-10 px-3 text-[9px] text-gold/70">Institucional</p>
    <ul class="mt-4 space-y-1">
      <li>
        <a href="index.php#portal-juris"
           class="block rounded-md px-3 py-2.5 text-[13.5px] text-silver transition-colors duration-500 hover:bg-gold/[0.035] hover:text-silk">
          Voltar ao site
        </a>
      </li>
      <li>
        <a href="index.php#solucoes-legais"
           class="block rounded-md px-3 py-2.5 text-[13.5px] text-silver transition-colors duration-500 hover:bg-gold/[0.035] hover:text-silk">
          Soluções Legais
        </a>
      </li>
    </ul>
  </nav>

  <!-- Assinatura do usuário -->
  <div class="shrink-0 border-t border-gold/[0.12] px-5 py-5">
    <p class="text-[13.5px] font-medium text-silk">Dra. Helena Vasconcelos</p>
    <p class="mt-1 text-[11.5px] text-silver">OAB/SP 214.907 &middot; Plano Banca</p>
    <div class="filete-progresso mt-4 rounded-full">
      <span style="width:68%"></span>
    </div>
    <p class="mt-2 text-[10.5px] text-silver/70">68% das peças do mês utilizadas</p>
  </div>
</aside>

<!-- ============================== CORPO ============================== -->
<div class="lg:pl-[268px]">

  <header class="sticky top-0 z-30 border-b border-gold/[0.12] bg-navy">
    <div class="flex min-h-[72px] items-center gap-4 px-5 py-4 sm:px-8">

      <button type="button" id="botao-painel"
              class="flex h-10 w-10 shrink-0 flex-col items-center justify-center gap-[5px] rounded-md border border-gold/[0.25] lg:hidden"
              aria-expanded="false" aria-controls="lateral-painel" aria-label="Abrir menu do painel">
        <span class="filete-menu"></span>
        <span class="filete-menu"></span>
        <span class="filete-menu"></span>
      </button>

      <div class="min-w-0 flex-1">
        <h1 class="truncate text-[17px] font-medium text-silk sm:text-[19px]"><?= e($painel_titulo) ?></h1>
        <?php if ($painel_subtitulo !== ''): ?>
          <p class="mt-0.5 truncate text-[12px] text-silver"><?= e($painel_subtitulo) ?></p>
        <?php endif; ?>
      </div>

      <span class="hidden shrink-0 rounded-sm border border-sapphire/30 px-3 py-1.5 text-[10px] uppercase tracking-[0.18em] text-sapphire sm:inline-block">
        Protótipo
      </span>
    </div>
  </header>

  <main class="textura-navy min-h-[calc(100vh-72px)] px-5 py-8 sm:px-8 lg:py-10">
