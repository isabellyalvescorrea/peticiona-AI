<?php
/** Painel — Analisador de Contratos (parecer composto pela IA). */
require_once __DIR__ . '/includes/config.php';

$pagina_titulo    = 'Analisador de Contratos — ' . APP_NAME;
$painel_titulo    = 'Analisador de Contratos';
$painel_subtitulo = 'Auditoria de cláusulas, riscos de litígio e recomendações de redação';

$estilos_extra = [asset('assets/css/pdf-juridico.css')];
$scripts_extra = [
    'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js',
    asset('assets/js/dados.js'),
    asset('assets/js/pdf-juridico.js'),
    asset('assets/js/gemini.js'),
];

require __DIR__ . '/includes/header-painel.php';
?>

<div class="grid grid-cols-[minmax(0,1fr)] gap-5 min-[1180px]:grid-cols-[minmax(0,380px)_minmax(0,1fr)] xl:grid-cols-[minmax(0,430px)_minmax(0,1fr)]">

  <!-- ====================== Entrada ====================== -->
  <section class="cartao revelar overflow-hidden rounded-lg" aria-label="Minuta contratual">
    <div class="border-b border-gold/[0.1] px-6 py-5 sm:px-7">
      <p class="rotulo-secao text-[9px] text-gold/70">Etapa 01</p>
      <h2 class="mt-2 text-[17px] font-medium text-silk">Minuta Contratual</h2>
      <p class="mt-1.5 text-[12.5px] leading-relaxed text-silver">
        Cole o texto integral do contrato. A leitura técnica percorre cláusula por cláusula.
      </p>
    </div>

    <div class="px-6 py-6 sm:px-7">
      <label for="campo-contrato" class="mb-2 block text-[11px] uppercase tracking-[0.2em] text-silver">
        Texto do contrato
      </label>
      <textarea id="campo-contrato" rows="16"
                class="campo w-full resize-none rounded-md px-4 py-3 text-[13px] leading-relaxed"
                placeholder="CONTRATO DE PRESTAÇÃO DE SERVIÇOS&#10;&#10;Pelo presente instrumento particular, de um lado…"></textarea>

      <div class="mt-5 flex flex-wrap gap-3">
        <button type="button" id="analisar-contrato"
                class="btn-ouro grow whitespace-nowrap rounded-md px-6 py-3.5 text-[14.5px] font-semibold">
          Auditar Contrato <span class="seta ml-1.5">&rarr;</span>
        </button>
        <button type="button" id="limpar-contrato"
                class="btn-contorno whitespace-nowrap rounded-md px-8 py-3.5 text-[14.5px] font-medium">
          Limpar
        </button>
      </div>

      <div class="mt-6 border-t border-gold/[0.1] pt-5">
        <p class="rotulo-secao text-[9px] text-gold/70">O que é verificado</p>
        <ul class="mt-3 space-y-2.5">
          <?php foreach (['Cláusulas abusivas e potestativas', 'Omissões contratuais relevantes', 'Desequilíbrio entre as prestações', 'Conformidade com a LGPD', 'Riscos concretos de litígio'] as $item): ?>
            <li class="flex items-baseline gap-3 text-[13px] text-silver">
              <span class="h-px w-4 shrink-0 translate-y-[-3px] bg-gold/50"></span>
              <?= e($item) ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </section>

  <!-- ====================== Resultado ====================== -->
  <section aria-label="Resultado da auditoria">

    <!-- Estado vazio -->
    <div id="analise-vazia" class="cartao revelar flex min-h-[560px] flex-col items-center justify-center rounded-lg px-8 py-16 text-center" data-atraso="120">
      <p class="titulo-editorial text-[28px] text-silk/[0.8] sm:text-[34px]">Nenhuma auditoria em curso</p>
      <p class="mt-4 max-w-[460px] text-[13.5px] leading-relaxed text-silver">
        Cole a minuta ao lado e acione <span class="text-gold">Auditar Contrato</span>.
        O parecer será organizado em riscos críticos, cláusulas ausentes e recomendações.
      </p>
      <p data-erro class="mt-6 hidden max-w-[460px] text-[13px] leading-relaxed text-sapphire"></p>
      <span class="mt-8 block h-px w-16 bg-gold/30"></span>
    </div>

    <!-- Processando -->
    <div id="analise-carregando" class="cartao hidden min-h-[560px] flex-col items-center justify-center rounded-lg px-8 py-16 text-center">
      <p class="rotulo-secao text-[10px] text-gold/75">Percorrendo as cláusulas</p>
      <p class="mt-4 text-[28px] leading-none text-gold">
        <span class="ponto-carga inline-block">.</span><span class="ponto-carga inline-block">.</span><span class="ponto-carga inline-block">.</span>
      </p>
      <p class="mt-6 max-w-[380px] text-[12.5px] leading-relaxed text-silver">
        <span data-nota-carga>Cotejando o texto com a legislação vigente e a jurisprudência consolidada.</span>
      </p>
    </div>

    <!-- Parecer: a marcação e as medidas são as mesmas que saem no PDF. -->
    <div id="analise-resultado" class="hidden space-y-5">
      <div class="previa-folha">
        <div id="analise-corpo" class="folha-juridica"></div>
      </div>

      <div class="cartao rounded-lg p-6 sm:p-7">
        <div class="flex flex-col gap-5">
          <div>
            <h3 class="text-[16px] font-medium text-silk">Exportar parecer</h3>
            <p class="mt-1.5 text-[12.5px] text-silver">
              Documento com formatação forense, pronto para anexar ao dossiê do cliente.
            </p>
          </div>
          <div class="flex flex-wrap gap-3">
            <button type="button" data-exportar-pdf data-origem="#analise-resultado"
                    class="btn-ouro grow whitespace-nowrap rounded-md px-5 py-3 text-[13.5px] font-semibold sm:grow-0">
              Baixar parecer em PDF
            </button>
            <button type="button" data-exportar-pendente
                    class="btn-contorno grow whitespace-nowrap rounded-md px-5 py-3 text-[13.5px] font-medium sm:grow-0">
              Baixar em Word (.docx)
            </button>
          </div>

          <p data-info-modelo
             class="hidden text-[11.5px] leading-relaxed text-silver/70"></p>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Template de compilação do PDF: fora da tela, mas com layout real. -->
<div id="pdf-template" aria-hidden="true"></div>

<?php require __DIR__ . '/includes/footer-painel.php'; ?>