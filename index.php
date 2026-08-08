<?php
/**
 * Peticiona AI — Landing page pública (single-page scroll, 5 seções).
 * Protótipo visual: sem backend, sem API, sem persistência.
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/arte-hero.php';

$pagina_titulo = APP_NAME . ' — Peças processuais impecáveis em segundos';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/header-publico.php';
?>

<main>

  <!-- ==================================================================
       SEÇÃO 1 — PORTAL JURIS (Hero + barra de 5 destaques)
       ================================================================== -->
  <section id="portal-juris" class="relative overflow-hidden textura-navy pt-[72px]">
    <div class="textura-malha pointer-events-none absolute inset-0 opacity-70"></div>

    <!-- Curvas ambientes, uma família em cada lateral. A escala acompanha o
         espaço livre ao lado do bloco de texto centralizado. -->
    <div class="ondas-laterais pointer-events-none absolute inset-0 select-none" aria-hidden="true">
      <div class="arte-lateral absolute left-0 top-[52%] -translate-y-1/2">
        <?php arte_ondas_esquerda(); ?>
      </div>
      <div class="arte-lateral absolute right-0 top-[52%] -translate-y-1/2">
        <?php arte_ondas_direita(); ?>
      </div>
    </div>

    <div class="relative mx-auto flex min-h-[calc(100vh-72px)] max-w-[1400px] flex-col px-5 pb-9 pt-6 sm:px-8 lg:pb-10 lg:pt-10">

      <div class="mx-auto w-full max-w-[860px] text-center">
        <!-- A marca abre o título como imagem; o restante segue as quebras definidas. -->
        <h1 class="hero-titulo revelar font-bold text-silk">
          <img src="<?= e(asset('assets/img/logo.png')) ?>"
               alt="<?= e(APP_NAME) ?>"
               width="843" height="128"
               class="inline-block h-10 w-auto align-[-0.12em] sm:h-12 lg:h-14"><br>
          o sistema que transforma a<br>
          rotina de <span class="text-gold">escritórios jurídicos</span> ao<br>
          redigir <span class="text-gold">peças processuais</span> impecáveis<br>
          em segundos.
        </h1>

        <p class="revelar mx-auto mt-6 max-w-[660px] text-[15px] leading-[1.72] text-slate-200" data-atraso="120">
          Desenvolvido exclusivamente para advogados brasileiros e treinado rigorosamente<br class="hidden lg:inline">
          na legislação e jurisprudência do país (CPC, CLT e Tribunais Superiores).<br class="hidden lg:inline">
          O Peticiona AI automatiza a redação de petições, analisa contratos e exporta documentos<br class="hidden lg:inline">
          em Word (.docx) prontos para protocolo — além de gerar resumos automáticos<br class="hidden lg:inline">
          para o WhatsApp do seu cliente.
        </p>

        <div class="revelar mt-9 flex flex-col items-stretch justify-center gap-4 sm:flex-row sm:items-center sm:gap-6" data-atraso="240">
          <button type="button" data-abrir-acesso
                  class="btn-ouro rounded-md px-8 py-[17px] text-[15px] font-semibold tracking-[0.01em] sm:min-w-[292px]">
            Experimentar Peticiona AI <span class="seta ml-2">&rarr;</span>
          </button>
          <a href="#ecossistema"
             class="btn-contorno rounded-md px-8 py-[17px] text-center text-[15px] font-medium tracking-[0.01em] sm:min-w-[266px]">
            Ver Ecossistema
          </a>
        </div>
      </div>

      <!-- Barra de 5 destaques -->
      <div class="revelar mt-12 lg:mt-auto lg:pt-14" data-atraso="360">
        <div class="overflow-hidden rounded-lg border border-gold/[0.18] bg-panel/[0.45]">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5">
            <?php foreach (hero_destaques() as $i => $d): ?>
              <?php
                $borda  = 'border-gold/[0.1] ';
                $borda .= $i > 0 ? 'border-t lg:border-t-0 lg:border-l ' : 'lg:border-l-0 ';
                $borda .= $i === 1 ? 'sm:border-t-0 ' : '';
                $borda .= $i % 2 === 1 ? 'sm:border-l ' : '';
              ?>
              <div class="group relative px-6 py-6 transition-colors duration-500 hover:bg-gold/[0.03] <?= $borda ?>">
                <h3 class="text-[15px] font-medium leading-snug text-gold">
                  <?= e($d['titulo']) ?>
                </h3>
                <p class="mt-3.5 text-[12px] leading-[1.62] text-silver">
                  <?= e($d['texto']) ?>
                </p>
                <span class="absolute inset-x-6 bottom-0 h-px origin-left scale-x-0 bg-gradient-to-r from-gold/60 to-transparent transition-transform duration-700 group-hover:scale-x-100"></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </section>


  <!-- ==================================================================
       SEÇÃO 2 — SOLUÇÕES LEGAIS
       ================================================================== -->
  <section id="solucoes-legais" class="relative border-t border-gold/[0.08] py-20 lg:py-28">
    <div class="mx-auto max-w-[1400px] px-5 sm:px-8">

      <header class="revelar mx-auto max-w-[860px] text-center">
        <p class="filete-rotulo rotulo-secao flex items-center justify-center gap-4 text-[10px] text-gold/80">
          Soluções Legais
        </p>
        <h2 class="titulo-secao mt-6 text-silk">
          Engenharia de Inteligência para Cada Etapa da <span class="text-gold">Prática Jurídica</span>
        </h2>
        <p class="mx-auto mt-6 max-w-[680px] text-[14.5px] leading-[1.75] text-slate-200">
          Ferramentas desenhadas sob medida para eliminar a burocracia, acelerar pesquisas
          e entregar minutas de alta técnica processual.
        </p>
      </header>

      <div class="mt-14 grid gap-5 md:grid-cols-2 lg:mt-20 lg:gap-6">
        <?php foreach (solucoes_legais() as $i => $item): ?>
          <article class="cartao cartao-fio revelar overflow-hidden rounded-lg p-7 sm:p-9"
                   data-atraso="<?= $i * 90 ?>">
            <div class="flex items-baseline gap-4">
              <span class="ordinal text-[13px] text-gold/[0.55]"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
              <span class="h-px flex-1 bg-gradient-to-r from-gold/25 to-transparent"></span>
            </div>
            <h3 class="mt-5 text-[19px] font-medium leading-snug text-gold sm:text-[21px]">
              <?= e($item['titulo']) ?>
            </h3>
            <p class="mt-4 text-[14px] leading-[1.78] text-silver">
              <?= e($item['texto']) ?>
            </p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>


  <!-- ==================================================================
       SEÇÃO 3 — ÁREAS DO DIREITO
       ================================================================== -->
  <section id="areas-direito" class="relative border-t border-gold/[0.08] bg-panel/25 py-20 lg:py-28">
    <div class="textura-malha pointer-events-none absolute inset-0 opacity-50"></div>

    <div class="relative mx-auto max-w-[1400px] px-5 sm:px-8">

      <header class="revelar mx-auto max-w-[880px] text-center">
        <p class="filete-rotulo rotulo-secao flex items-center justify-center gap-4 text-[10px] text-gold/80">
          Áreas do Direito
        </p>
        <h2 class="titulo-secao mt-6 text-silk">
          Adaptação Rigorosa aos Principais Ramos do <span class="text-gold">Direito Brasileiro</span>
        </h2>
        <p class="mx-auto mt-6 max-w-[700px] text-[14.5px] leading-[1.75] text-slate-200">
          A inteligência do Peticiona AI compreende a linguagem, a doutrina e as
          peculiaridades rituais de cada ramo jurídico.
        </p>
      </header>

      <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:mt-20 lg:grid-cols-3 lg:gap-6">
        <?php foreach (areas_direito() as $i => $area): ?>
          <article class="cartao cartao-fio revelar flex flex-col overflow-hidden rounded-lg p-7 sm:p-8
                          <?= $i === 3 ? 'lg:col-span-2' : '' ?>"
                   data-atraso="<?= $i * 80 ?>">
            <div class="flex items-center justify-between gap-4">
              <span class="ordinal text-[13px] text-gold/[0.55]"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
              <span class="rounded-sm border border-sapphire/30 px-2.5 py-1 text-[10px] uppercase tracking-[0.18em] text-sapphire">
                <?= e($area['badge']) ?>
              </span>
            </div>
            <h3 class="mt-6 text-[18px] font-medium leading-snug text-gold sm:text-[19.5px]">
              <?= e($area['titulo']) ?>
            </h3>
            <p class="mt-4 text-[13.5px] leading-[1.78] text-silver">
              <?= e($area['texto']) ?>
            </p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>


  <!-- ==================================================================
       SEÇÃO 4 — ACERVO & MODELOS
       ================================================================== -->
  <section id="acervo-modelos" class="relative border-t border-gold/[0.08] py-20 lg:py-28">
    <div class="mx-auto max-w-[1400px] px-5 sm:px-8">

      <header class="revelar mx-auto max-w-[860px] text-center">
        <p class="filete-rotulo rotulo-secao flex items-center justify-center gap-4 text-[10px] text-gold/80">
          Acervo &amp; Modelos
        </p>
        <h2 class="titulo-secao mt-6 text-silk">
          Conhecimento Estruturado e <span class="text-gold">Fundamentação Atualizada</span>
        </h2>
        <p class="mx-auto mt-6 max-w-[720px] text-[14.5px] leading-[1.75] text-slate-200">
          Acesso a teses consolidadas, jurisprudências recentes dos Tribunais Superiores
          e estruturas de minutas refinadas por especialistas.
        </p>
      </header>

      <div class="mt-14 grid gap-5 md:grid-cols-3 lg:mt-20 lg:gap-6">
        <?php foreach (acervo_modelos() as $i => $item): ?>
          <article class="cartao cartao-fio revelar flex flex-col overflow-hidden rounded-lg p-7 sm:p-9"
                   data-atraso="<?= $i * 110 ?>">
            <span class="ordinal text-[34px] leading-none text-gold/25 sm:text-[40px]">
              <?= ['I', 'II', 'III'][$i] ?>
            </span>
            <h3 class="mt-7 text-[18.5px] font-medium leading-snug text-gold sm:text-[20px]">
              <?= e($item['titulo']) ?>
            </h3>
            <span class="mt-5 block h-px w-12 bg-gold/[0.35]"></span>
            <p class="mt-5 text-[14px] leading-[1.78] text-silver">
              <?= e($item['texto']) ?>
            </p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>


  <!-- ==================================================================
       SEÇÃO 5 — ECOSSISTEMA
       ================================================================== -->
  <section id="ecossistema" class="relative border-t border-gold/[0.08] py-20 lg:py-28">
    <div class="mx-auto max-w-[1400px] px-5 sm:px-8">

      <header class="revelar mx-auto max-w-[900px] text-center">
        <p class="filete-rotulo rotulo-secao flex items-center justify-center gap-4 text-[10px] text-gold/80">
          Ecossistema
        </p>
        <h2 class="titulo-secao mt-6 text-silk">
          Infraestrutura de Classe Empresarial com <span class="text-gold">Confidencialidade Absoluta</span>
        </h2>
        <p class="mx-auto mt-6 max-w-[720px] text-[14.5px] leading-[1.75] text-slate-200">
          Segurança de dados sensíveis, conformidade com a LGPD e metodologias rígidas
          de proteção à informação dos seus clientes.
        </p>
      </header>

      <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:mt-20 lg:grid-cols-4 lg:gap-6">
        <?php foreach (ecossistema() as $i => $item): ?>
          <article class="cartao cartao-fio revelar flex flex-col overflow-hidden rounded-lg p-7 sm:p-8"
                   data-atraso="<?= $i * 85 ?>">
            <span class="ordinal text-[13px] text-gold/[0.55]"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
            <h3 class="mt-5 text-[17px] font-medium leading-snug text-gold sm:text-[18px]">
              <?= e($item['titulo']) ?>
            </h3>
            <p class="mt-4 text-[13.5px] leading-[1.78] text-silver">
              <?= e($item['texto']) ?>
            </p>
          </article>
        <?php endforeach; ?>
      </div>

      <!-- Chamada final -->
      <div class="revelar mt-16 overflow-hidden rounded-xl border border-gold/[0.2] bg-panel/50 lg:mt-20" data-atraso="150">
        <div class="h-px w-full bg-gradient-to-r from-transparent via-gold to-transparent"></div>
        <div class="flex flex-col items-center gap-8 px-7 py-12 text-center sm:px-12 lg:flex-row lg:justify-between lg:gap-12 lg:text-left">
          <div class="max-w-[640px]">
            <h3 class="titulo-secao text-silk">
              A peça certa, no tempo certo, com <span class="text-gold">o rigor que a sua banca exige</span>.
            </h3>
            <p class="mt-4 text-[14px] leading-[1.75] text-silver">
              Conheça o ambiente reservado do advogado: gerador de peças, auditoria contratual
              e gestão de carteira em uma única superfície de trabalho.
            </p>
          </div>
          <div class="flex w-full flex-col gap-4 sm:w-auto sm:flex-row">
            <button type="button" data-abrir-acesso
                    class="btn-ouro whitespace-nowrap rounded-md px-8 py-4 text-[15px] font-semibold">
              Acessar Sistema <span class="seta ml-2">&rarr;</span>
            </button>
            <a href="dashboard.php"
               class="btn-contorno whitespace-nowrap rounded-md px-8 py-4 text-center text-[15px] font-medium">
              Ver o Painel
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

</main>

<?php require __DIR__ . '/includes/footer-publico.php'; ?>
