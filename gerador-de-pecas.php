<?php
/** Painel — Gerador de Peças (protótipo visual: nenhuma peça é realmente gerada). */
require_once __DIR__ . '/includes/config.php';

$pagina_titulo    = 'Gerador de Peças — ' . APP_NAME;
$painel_titulo    = 'Gerador de Peças';
$painel_subtitulo = 'Minutas processuais alinhadas ao CPC/2015 e à jurisprudência dominante';

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

<div class="grid gap-5 min-[1180px]:grid-cols-[minmax(0,360px)_1fr] xl:grid-cols-[minmax(0,400px)_1fr]">

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
        Protótipo de interface: a minuta exibida ao lado é um texto demonstrativo fixo.
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
      <span class="mt-8 block h-px w-16 bg-gold/30"></span>
    </div>

    <!-- Composição -->
    <div id="peca-carregando" class="cartao hidden min-h-[520px] flex-col items-center justify-center rounded-lg px-8 py-16 text-center">
      <p class="rotulo-secao text-[10px] text-gold/75">Compondo a minuta</p>
      <p class="mt-4 text-[28px] leading-none text-gold">
        <span class="ponto-carga inline-block">.</span><span class="ponto-carga inline-block">.</span><span class="ponto-carga inline-block">.</span>
      </p>
      <p class="mt-6 max-w-[380px] text-[12.5px] leading-relaxed text-silver">
        Estruturando endereçamento, qualificação, fundamentos e rol de pedidos.
      </p>
    </div>

    <!-- Folha virtual -->
    <div id="peca-folha" class="hidden">
      <div class="folha-virtual mx-auto max-w-[820px] rounded-sm px-7 py-10 text-[14.5px] sm:px-14 sm:py-16">

        <p class="sem-recuo text-center text-[15px] font-semibold uppercase leading-snug tracking-[0.04em]">
          Excelentíssimo Senhor Doutor Juiz de Direito da ___ Vara Cível da Comarca de São Paulo — SP
        </p>

        <p class="sem-recuo mt-12 text-right text-[13px] italic opacity-70" data-eco="tipo">Petição Inicial</p>

        <p class="mt-10">
          <strong data-eco="autor">MARCELO ANDRADE RIBEIRO</strong>, brasileiro, casado, engenheiro civil,
          portador da cédula de identidade RG n.º 00.000.000-0 e inscrito no CPF sob o n.º 000.000.000-00,
          residente e domiciliado nesta Capital, vem, respeitosamente, à presença de Vossa Excelência, por
          intermédio de sua advogada que esta subscreve, com fundamento nos artigos 319 e seguintes do
          Código de Processo Civil, propor a presente
        </p>

        <p class="sem-recuo mt-8 text-center font-semibold uppercase tracking-[0.06em]">
          Ação de Obrigação de Fazer c/c Indenização por Danos Morais<br>
          <span class="text-[13px] font-normal normal-case tracking-normal">com pedido de tutela provisória de urgência</span>
        </p>

        <p class="mt-8">
          em face de <strong data-eco="reu">NEXUS PARTICIPAÇÕES S.A.</strong>, pessoa jurídica de direito
          privado, inscrita no CNPJ sob o n.º 00.000.000/0001-00, com sede nesta Capital, pelos fundamentos
          de fato e de direito a seguir expostos.
        </p>

        <p class="sem-recuo mt-10 font-semibold uppercase tracking-[0.08em]">I — Dos Fatos</p>

        <p class="mt-4" data-eco="fatos">
          O autor celebrou com a ré contrato de prestação de serviços continuados, adimplindo pontualmente
          todas as contraprestações pactuadas. Não obstante a regularidade dos pagamentos, a requerida
          interrompeu unilateralmente a execução do objeto contratual, sem prévia notificação e sem
          apresentar justificativa idônea, frustrando a legítima expectativa depositada no negócio jurídico
          e acarretando prejuízos de ordem material e extrapatrimonial ao requerente.
        </p>

        <p class="sem-recuo mt-8 font-semibold uppercase tracking-[0.08em]">II — Do Direito</p>

        <p class="mt-4">
          A conduta da requerida contraria frontalmente o princípio da boa-fé objetiva, insculpido no artigo
          422 do Código Civil, do qual decorrem os deveres anexos de informação, cooperação e lealdade. A
          interrupção imotivada da prestação configura inadimplemento contratual apto a ensejar a tutela
          específica da obrigação, nos termos do artigo 497 do Código de Processo Civil.
        </p>

        <p class="mt-4">
          Quanto ao dano extrapatrimonial, a jurisprudência consolidada reconhece que a frustração
          injustificada de expectativa legítima, somada à desídia no atendimento das reclamações
          administrativas, ultrapassa o mero dissabor cotidiano e atinge a esfera dos direitos da
          personalidade, impondo o dever de indenizar na forma dos artigos 186 e 927 do Código Civil.
        </p>

        <p class="sem-recuo mt-8 font-semibold uppercase tracking-[0.08em]">III — Da Tutela de Urgência</p>

        <p class="mt-4">
          Presentes os requisitos do artigo 300 do Código de Processo Civil — a probabilidade do direito,
          demonstrada pela prova documental que instrui a exordial, e o perigo de dano, evidenciado pela
          continuidade do inadimplemento —, impõe-se a concessão da tutela provisória de urgência.
        </p>

        <p class="sem-recuo mt-8 font-semibold uppercase tracking-[0.08em]">IV — Dos Pedidos</p>

        <p class="mt-4" data-eco="pedidos">
          Ante o exposto, requer: (a) a concessão da tutela provisória de urgência para determinar o
          imediato restabelecimento dos serviços, sob pena de multa diária; (b) a citação da ré para,
          querendo, apresentar resposta; (c) a procedência dos pedidos, com a condenação da requerida ao
          cumprimento da obrigação de fazer e ao pagamento de indenização por danos morais; e (d) a
          condenação da ré ao pagamento das custas processuais e dos honorários advocatícios sucumbenciais.
        </p>

        <p class="mt-6">
          Protesta provar o alegado por todos os meios de prova em direito admitidos, especialmente pela
          prova documental, testemunhal e pelo depoimento pessoal do representante legal da requerida.
        </p>

        <p class="mt-6">
          Dá-se à causa o valor de R$ 80.000,00 (oitenta mil reais).
        </p>

        <p class="sem-recuo mt-10 text-center">Termos em que,<br>pede deferimento.</p>

        <p class="sem-recuo mt-10 text-center">São Paulo, <?= date('d') ?> de agosto de <?= date('Y') ?>.</p>

        <p class="sem-recuo mt-12 text-center">
          <span class="mx-auto mb-2 block h-px w-56 bg-black/25"></span>
          <strong>Helena Vasconcelos</strong><br>
          <span class="text-[13px]">OAB/SP 214.907</span>
        </p>
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
            <button type="button" data-exportar
                    class="btn-ouro grow whitespace-nowrap rounded-md px-5 py-3 text-[13.5px] font-semibold sm:grow-0">
              Baixar em Word (.docx)
            </button>
            <button type="button" data-exportar
                    class="btn-contorno grow whitespace-nowrap rounded-md px-5 py-3 text-[13.5px] font-medium sm:grow-0">
              Baixar em PDF
            </button>
            <button type="button" data-exportar
                    class="btn-contorno grow whitespace-nowrap rounded-md px-5 py-3 text-[13.5px] font-medium sm:grow-0">
              Resumo para WhatsApp
            </button>
          </div>
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

<?php require __DIR__ . '/includes/footer-painel.php'; ?>
