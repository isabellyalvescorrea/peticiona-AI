<?php
/** Painel — Gerador de Peças (protótipo visual: nenhuma peça é realmente gerada). */
require_once __DIR__ . '/includes/config.php';

$pagina_titulo    = 'Gerador de Peças — ' . APP_NAME;
$painel_titulo    = 'Gerador de Peças';
$painel_subtitulo = 'Minutas processuais alinhadas ao CPC/2015 e à jurisprudência dominante';

$estilos_extra = [asset('assets/css/pdf-juridico.css')];
$scripts_extra = [
    'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js',
    asset('assets/js/dados.js'),
    asset('assets/js/pdf-juridico.js'),
    asset('assets/js/gemini.js'),
];

$tipos_peca = [
    'Petição Inicial',
    'Contestação',
    'Réplica',
    'Recurso de Apelação',
    'Agravo de Instrumento',
    'Embargos de Declaração',
    'Reclamatória Trabalhista',
    'Resposta à Acusação',
    'Contrarrazões Recursais',
    'Cumprimento de Sentença',
];

require __DIR__ . '/includes/header-painel.php';
?>

<div class="grid grid-cols-[minmax(0,1fr)] gap-5 min-[1180px]:grid-cols-[minmax(0,360px)_minmax(0,1fr)] xl:grid-cols-[minmax(0,400px)_minmax(0,1fr)]">

  <!-- ====================== Formulário ====================== -->
  <section class="cartao revelar overflow-hidden rounded-lg" aria-label="Dados da peça">
    <div class="border-b border-gold/[0.1] px-6 py-5 sm:px-7">
      <p class="rotulo-secao text-[9px] text-gold/70">Etapa 01</p>
      <h2 class="mt-2 text-[17px] font-medium text-silk">Dados da Peça</h2>
      <p class="mt-1.5 text-[12.5px] leading-relaxed text-silver">
        Preencha os elementos essenciais. A minuta é composta com base na técnica processual aplicável.
      </p>
    </div>

    <form class="space-y-5 px-6 py-6 sm:px-7" onsubmit="return false;">

      <div>
        <label for="campo-tipo" class="mb-2 block text-[11px] uppercase tracking-[0.2em] text-silver">
          Tipo de peça
        </label>
        <select id="campo-tipo" class="campo w-full rounded-md px-4 py-3 text-[14px]">
          <?php foreach ($tipos_peca as $tipo): ?>
            <option value="<?= e($tipo) ?>"><?= e($tipo) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="campo-autor" class="mb-2 block text-[11px] uppercase tracking-[0.2em] text-silver">
          Qualificação do autor
        </label>
        <textarea id="campo-autor" rows="3"
                  class="campo w-full resize-none rounded-md px-4 py-3 text-[13.5px] leading-relaxed"
                  placeholder="Nome, nacionalidade, estado civil, profissão, CPF, RG e endereço completo."></textarea>
      </div>

      <div>
        <label for="campo-reu" class="mb-2 block text-[11px] uppercase tracking-[0.2em] text-silver">
          Qualificação do réu
        </label>
        <textarea id="campo-reu" rows="3"
                  class="campo w-full resize-none rounded-md px-4 py-3 text-[13.5px] leading-relaxed"
                  placeholder="Razão social ou nome, CNPJ/CPF e endereço para citação."></textarea>
      </div>

      <div>
        <label for="campo-fatos" class="mb-2 block text-[11px] uppercase tracking-[0.2em] text-silver">
          Fatos do caso
        </label>
        <textarea id="campo-fatos" rows="6"
                  class="campo w-full resize-none rounded-md px-4 py-3 text-[13.5px] leading-relaxed"
                  placeholder="Narre a cronologia dos acontecimentos, os documentos que a instruem e o dano suportado."></textarea>
      </div>

      <div>
        <label for="campo-pedidos" class="mb-2 block text-[11px] uppercase tracking-[0.2em] text-silver">
          Pedidos
        </label>
        <textarea id="campo-pedidos" rows="4"
                  class="campo w-full resize-none rounded-md px-4 py-3 text-[13.5px] leading-relaxed"
                  placeholder="Relacione os pedidos principais, subsidiários e os requerimentos processuais."></textarea>
      </div>

      <button type="button" id="gerar-peca"
              class="btn-ouro w-full rounded-md px-6 py-3.5 text-[14.5px] font-semibold">
        Gerar Peça Processual <span class="seta ml-1.5">&rarr;</span>
      </button>

      <p class="border-t border-gold/[0.1] pt-4 text-[11.5px] leading-relaxed text-silver/70">
        A minuta é composta pela IA, gravada na sua carteira local e exportável
        em PDF com formatação forense.
      </p>
    </form>
  </section>

  <!-- ====================== Resultado ====================== -->
  <section class="revelar" aria-label="Peça gerada" data-atraso="120">

    <!-- Estado vazio -->
    <div id="peca-vazia" class="cartao flex min-h-[520px] flex-col items-center justify-center rounded-lg px-8 py-16 text-center">
      <p class="titulo-editorial text-[28px] text-silk/[0.8] sm:text-[34px]">Folha em branco</p>
      <p class="mt-4 max-w-[440px] text-[13.5px] leading-relaxed text-silver">
        Preencha os dados ao lado e acione <span class="text-gold">Gerar Peça Processual</span>.
        A minuta aparecerá aqui, formatada como sairia para protocolo.
      </p>
      <p data-erro class="mt-6 hidden max-w-[440px] text-[13px] leading-relaxed text-sapphire"></p>
      <span class="mt-8 block h-px w-16 bg-gold/30"></span>
    </div>

    <!-- Composição -->
    <div id="peca-carregando" class="cartao hidden min-h-[520px] flex-col items-center justify-center rounded-lg px-8 py-16 text-center">
      <p class="rotulo-secao text-[10px] text-gold/75">Compondo a minuta</p>
      <p class="mt-4 text-[28px] leading-none text-gold">
        <span class="ponto-carga inline-block">.</span><span class="ponto-carga inline-block">.</span><span class="ponto-carga inline-block">.</span>
      </p>
      <p class="mt-6 max-w-[380px] text-[12.5px] leading-relaxed text-silver">
        <span data-nota-carga>Estruturando endereçamento, qualificação, fundamentos e rol de pedidos.</span>
      </p>
    </div>

    <!-- Folha jurídica: mesma marcação e mesmas medidas que saem no PDF. O
         conteúdo é injetado por gemini.js a partir do Markdown da IA. -->
    <div id="peca-folha" class="hidden">
      <div class="previa-folha">
        <div id="peca-corpo" class="folha-juridica"></div>
      </div>
    </div>

    <!-- Ações de exportação -->
    <div id="peca-acoes" class="hidden">
      <div class="cartao mt-5 rounded-lg p-6 sm:p-7">
        <div class="flex flex-col gap-5">
          <div>
            <h3 class="text-[16px] font-medium text-silk">Exportar e comunicar</h3>
            <p class="mt-1.5 text-[12.5px] text-silver">
              Documento formatado para protocolo e resumo pronto para o cliente.
            </p>
          </div>
          <!-- flex-wrap em vez de grid: os rótulos são longos e não devem
               ser espremidos em colunas de largura fixa. -->
          <div class="flex flex-wrap gap-3">
            <button type="button" data-exportar-pdf data-origem="#peca-folha"
                    class="btn-ouro grow whitespace-nowrap rounded-md px-5 py-3 text-[13.5px] font-semibold sm:grow-0">
              Baixar em PDF
            </button>
            <button type="button" data-exportar-pendente
                    class="btn-contorno grow whitespace-nowrap rounded-md px-5 py-3 text-[13.5px] font-medium sm:grow-0">
              Baixar em Word (.docx)
            </button>
            <button type="button" data-exportar-pendente
                    class="btn-contorno grow whitespace-nowrap rounded-md px-5 py-3 text-[13.5px] font-medium sm:grow-0">
              Resumo para WhatsApp
            </button>
          </div>

          <p data-info-modelo
             class="hidden text-[11.5px] leading-relaxed text-silver/70"></p>
        </div>

        <div class="mt-6 border-t border-gold/[0.1] pt-5">
          <p class="rotulo-secao text-[9px] text-gold/70">Prévia do resumo ao cliente</p>
          <p class="mt-3 text-[13.5px] leading-[1.75] text-silver">
            &ldquo;Prezado(a) cliente, protocolamos hoje a ação que exige o restabelecimento imediato dos
            serviços contratados, acompanhada de pedido urgente para que a decisão saia antes do julgamento
            final. Também pleiteamos indenização pelos transtornos. Assim que houver decisão judicial,
            comunicaremos você de imediato.&rdquo;
          </p>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Template de compilação do PDF: fica fora da tela, mas com layout real —
     display:none impediria o html2canvas de medir o conteúdo. -->
<div id="pdf-template" aria-hidden="true"></div>

<?php require __DIR__ . '/includes/footer-painel.php'; ?>