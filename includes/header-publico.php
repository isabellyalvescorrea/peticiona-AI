<?php
/**
 * Header público — fundo 100% sólido (#070A12), sem transparência e sem blur.
 * Navegação por âncoras com rolagem suave (single-page scroll).
 */
require_once __DIR__ . '/config.php';
$itens = nav_publico();
?>
<header id="cabecalho" class="fixed inset-x-0 top-0 z-50 bg-navy border-b border-gold/[0.15]">
  <div class="mx-auto flex h-[72px] max-w-[1400px] items-center justify-between gap-6 px-5 sm:px-8">

    <!-- Logo oficial -->
    <a href="index.php#portal-juris" class="group flex shrink-0 items-center" aria-label="<?= e(APP_NAME) ?> — início">
      <img src="<?= e(asset('assets/img/logo.png')) ?>"
           alt="<?= e(APP_NAME) ?>"
           width="843" height="128"
           class="h-[26px] w-auto transition-opacity duration-500 group-hover:opacity-80 sm:h-[30px]">
    </a>

    <!-- Navegação central (desktop) -->
    <nav class="hidden items-center gap-8 lg:flex xl:gap-10" aria-label="Navegação principal">
      <?php foreach ($itens as $item): ?>
        <a href="<?= e($item['anchor']) ?>"
           class="link-nav text-[13.5px] font-normal text-silver">
          <?= e($item['label']) ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="flex items-center gap-3">
      <!-- Acesso ao painel -->
      <button type="button"
              data-abrir-acesso
              class="btn-contorno hidden rounded-md px-5 py-2.5 text-[13.5px] font-medium tracking-wide sm:inline-flex">
        Acessar Sistema
      </button>

      <!-- Menu mobile: filetes, não ícone -->
      <button type="button"
              id="botao-menu"
              class="flex h-10 w-10 flex-col items-center justify-center gap-[5px] rounded-md border border-gold/[0.25] lg:hidden"
              aria-expanded="false"
              aria-controls="menu-mobile"
              aria-label="Abrir menu de navegação">
        <span class="filete-menu"></span>
        <span class="filete-menu"></span>
        <span class="filete-menu"></span>
      </button>
    </div>
  </div>

  <!-- Painel de navegação mobile -->
  <div id="menu-mobile"
       class="hidden border-t border-gold/[0.12] bg-navy lg:hidden">
    <nav class="mx-auto max-w-[1400px] px-5 py-4 sm:px-8" aria-label="Navegação principal (mobile)">
      <?php foreach ($itens as $i => $item): ?>
        <a href="<?= e($item['anchor']) ?>"
           data-fecha-menu
           class="flex items-baseline gap-4 border-b border-gold/[0.08] py-3.5 text-[15px] text-silver transition-colors duration-300 hover:text-gold">
          <span class="ordinal text-[12px] text-gold/[0.45]"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
          <?= e($item['label']) ?>
        </a>
      <?php endforeach; ?>
      <button type="button"
              data-abrir-acesso
              data-fecha-menu
              class="btn-contorno mt-5 w-full rounded-md px-5 py-3 text-[14px] font-medium tracking-wide">
        Acessar Sistema
      </button>
    </nav>
  </div>
</header>
