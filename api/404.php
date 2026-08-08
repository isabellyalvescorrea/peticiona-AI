<?php
/**
 * 404 do ambiente de produção. Vive em /api porque só é alcançada pelo
 * controlador de entrada, quando a rota pedida não está na lista de páginas
 * públicas (o que inclui qualquer tentativa de acessar includes/).
 */
require_once dirname(__DIR__) . '/includes/config.php';

$pagina_titulo    = 'Página não encontrada — ' . APP_NAME;
$pagina_descricao = 'O endereço solicitado não existe neste sistema.';
require dirname(__DIR__) . '/includes/head.php';
?>

<main class="textura-navy flex min-h-screen flex-col items-center justify-center px-5 text-center">
  <img src="<?= e(asset('assets/img/logo.png')) ?>"
       alt="<?= e(APP_NAME) ?>"
       width="843" height="128"
       class="h-8 w-auto sm:h-10">

  <p class="rotulo-secao mt-12 text-[10px] text-gold/80">Erro 404</p>

  <h1 class="titulo-secao mt-5 max-w-[620px] text-silk">
    O endereço solicitado <span class="text-gold">não existe</span> neste sistema.
  </h1>

  <p class="mt-6 max-w-[460px] text-[14.5px] leading-[1.75] text-slate-200">
    A página pode ter sido movida ou o link estar incorreto.
    Retome pelo portal principal ou pelo ambiente reservado do advogado.
  </p>

  <div class="mt-10 flex flex-col items-stretch gap-4 sm:flex-row sm:items-center">
    <a href="/" class="btn-ouro rounded-md px-8 py-[17px] text-center text-[15px] font-semibold">
      Voltar ao início <span class="seta ml-2">&rarr;</span>
    </a>
    <a href="/dashboard.php" class="btn-contorno rounded-md px-8 py-[17px] text-center text-[15px] font-medium">
      Ir para o painel
    </a>
  </div>

  <span class="mt-14 block h-px w-16 bg-gold/30"></span>
</main>

<script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
</body>
</html>
