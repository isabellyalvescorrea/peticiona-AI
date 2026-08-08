<?php
/** Painel — Analisador de Contratos (protótipo visual: resultado fixo e demonstrativo). */
require_once __DIR__ . '/includes/config.php';

$pagina_titulo    = 'Analisador de Contratos — ' . APP_NAME;
$painel_titulo    = 'Analisador de Contratos';
$painel_subtitulo = 'Auditoria de cláusulas, riscos de litígio e recomendações de redação';

$riscos = [
    [
        'clausula' => 'Cláusula 7.ª — Rescisão',
        'grau'     => 'Crítico',
        'texto'    => 'Prevê rescisão unilateral imotivada por apenas uma das partes, sem aviso prévio e sem contrapartida indenizatória, o que caracteriza potestatividade vedada pelo art. 122 do Código Civil.',
    ],
    [
        'clausula' => 'Cláusula 12.ª — Foro de eleição',
        'grau'     => 'Alto',
        'texto'    => 'Elege comarca distante do domicílio da parte aderente em contrato de adesão, hipótese em que a jurisprudência reconhece a abusividade e admite a declinação de ofício.',
    ],
    [
        'clausula' => 'Cláusula 15.ª — Multa moratória',
        'grau'     => 'Médio',
        'texto'    => 'Estipula multa de 20% sobre o valor total do contrato, percentual superior ao limite consolidado para relações de consumo e passível de redução equitativa pelo juízo.',
    ],
];

$ausencias = [
    ['titulo' => 'Cláusula de confidencialidade',   'texto' => 'Não há disciplina do tratamento de informações sensíveis trocadas durante a execução, expondo ambas as partes a vazamentos sem consequência contratual.'],
    ['titulo' => 'Conformidade com a LGPD',         'texto' => 'Ausente a definição de papéis de controlador e operador, bem como das bases legais de tratamento exigidas pela Lei n.º 13.709/2018.'],
    ['titulo' => 'Critério de reajuste',            'texto' => 'O contrato é silente quanto ao índice e à periodicidade de correção do preço, o que tende a gerar litígio em contratações de trato sucessivo.'],
    ['titulo' => 'Método de solução de conflitos',  'texto' => 'Não há previsão de mediação prévia ou arbitragem, alternativas que reduzem custo e tempo de resolução em contratos empresariais.'],
];

$recomendacoes = [
    'Condicionar a rescisão imotivada a aviso prévio mínimo de 30 (trinta) dias e a multa compensatória proporcional ao prazo remanescente.',
    'Substituir o foro de eleição pelo domicílio do aderente, prevenindo declaração de nulidade e deslocamento posterior do feito.',
    'Reduzir a multa moratória a 2% sobre a parcela inadimplida, com juros de mora de 1% ao mês, em harmonia com a prática consolidada.',
    'Inserir capítulo próprio de proteção de dados, com definição de papéis, finalidade, prazo de retenção e obrigações em caso de incidente.',
    'Incluir cláusula escalonada de solução de controvérsias, com negociação direta, mediação e, subsidiariamente, arbitragem.',
];

require __DIR__ . '/includes/header-painel.php';
?>

<div class="grid gap-5 min-[1180px]:grid-cols-[minmax(0,380px)_1fr] xl:grid-cols-[minmax(0,430px)_1fr]">

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
      <span class="mt-8 block h-px w-16 bg-gold/30"></span>
    </div>

    <!-- Processando -->
    <div id="analise-carregando" class="cartao hidden min-h-[560px] flex-col items-center justify-center rounded-lg px-8 py-16 text-center">
      <p class="rotulo-secao text-[10px] text-gold/75">Percorrendo as cláusulas</p>
      <p class="mt-4 text-[28px] leading-none text-gold">
        <span class="ponto-carga inline-block">.</span><span class="ponto-carga inline-block">.</span><span class="ponto-carga inline-block">.</span>
      </p>
      <p class="mt-6 max-w-[380px] text-[12.5px] leading-relaxed text-silver">
        Cotejando o texto com a legislação vigente e a jurisprudência consolidada.
      </p>
    </div>

    <!-- Parecer -->
    <div id="analise-resultado" class="hidden space-y-5">

      <!-- Síntese -->
      <div class="cartao revelar overflow-hidden rounded-lg">
        <div class="h-px w-full bg-gradient-to-r from-transparent via-gold to-transparent"></div>
        <div class="grid gap-6 px-6 py-6 sm:grid-cols-3 sm:px-7">
          <?php
          $sintese = [
              ['rotulo' => 'Grau de risco global', 'valor' => 'Elevado', 'nota' => '3 pontos críticos identificados'],
              ['rotulo' => 'Cláusulas analisadas', 'valor' => '18',      'nota' => '4 omissões relevantes'],
              ['rotulo' => 'Equilíbrio contratual','valor' => '42%',     'nota' => 'desfavorável ao contratante'],
          ];
          foreach ($sintese as $i => $s): ?>
            <div class="<?= $i > 0 ? 'sm:divisa-vertical sm:pl-6' : '' ?>">
              <p class="text-[10.5px] uppercase tracking-[0.18em] text-silver"><?= e($s['rotulo']) ?></p>
              <p class="mt-3 text-[30px] font-light leading-none text-gold"><?= e($s['valor']) ?></p>
              <p class="mt-2.5 text-[12px] text-silver"><?= e($s['nota']) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Riscos críticos -->
      <div class="cartao revelar overflow-hidden rounded-lg">
        <div class="flex items-center justify-between gap-4 border-b border-gold/[0.1] px-6 py-5 sm:px-7">
          <h2 class="text-[16px] font-medium text-silk">Riscos Críticos</h2>
          <span class="rounded-sm border border-gold/40 px-2.5 py-1 text-[10px] uppercase tracking-[0.18em] text-gold">
            <?= count($riscos) ?> ocorrências
          </span>
        </div>

        <ul>
          <?php foreach ($riscos as $i => $risco): ?>
            <li class="<?= $i > 0 ? 'border-t border-gold/[0.07]' : '' ?> px-6 py-5 sm:px-7">
              <div class="flex flex-wrap items-baseline justify-between gap-3">
                <p class="text-[14.5px] font-medium text-silk"><?= e($risco['clausula']) ?></p>
                <span class="rounded-sm border border-sapphire/30 px-2.5 py-1 text-[10px] uppercase tracking-[0.18em] text-sapphire">
                  <?= e($risco['grau']) ?>
                </span>
              </div>
              <p class="mt-3 text-[13.5px] leading-[1.75] text-silver"><?= e($risco['texto']) ?></p>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Cláusulas ausentes -->
      <div class="cartao revelar overflow-hidden rounded-lg">
        <div class="border-b border-gold/[0.1] px-6 py-5 sm:px-7">
          <h2 class="text-[16px] font-medium text-silk">Cláusulas Ausentes</h2>
          <p class="mt-1 text-[12px] text-silver">Disposições esperadas para esta modalidade contratual</p>
        </div>

        <div class="grid sm:grid-cols-2">
          <?php foreach ($ausencias as $i => $item): ?>
            <div class="px-6 py-5 sm:px-7 <?= $i > 0 ? 'border-t border-gold/[0.07]' : '' ?> <?= $i === 1 ? 'sm:border-t-0' : '' ?> <?= $i % 2 === 1 ? 'sm:divisa-vertical' : '' ?>">
              <div class="flex items-baseline gap-3">
                <span class="ordinal text-[11px] text-gold/[0.55]"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                <p class="text-[14px] font-medium text-silk"><?= e($item['titulo']) ?></p>
              </div>
              <p class="mt-2.5 pl-[30px] text-[13px] leading-[1.72] text-silver"><?= e($item['texto']) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Recomendações -->
      <div class="cartao revelar overflow-hidden rounded-lg">
        <div class="border-b border-gold/[0.1] px-6 py-5 sm:px-7">
          <h2 class="text-[16px] font-medium text-silk">Recomendações</h2>
          <p class="mt-1 text-[12px] text-silver">Redações alternativas sugeridas para reequilibrar o instrumento</p>
        </div>

        <ol class="px-6 py-6 sm:px-7">
          <?php foreach ($recomendacoes as $i => $rec): ?>
            <li class="flex items-baseline gap-4 <?= $i > 0 ? 'mt-4 border-t border-gold/[0.07] pt-4' : '' ?>">
              <span class="ordinal shrink-0 text-[12px] text-gold"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
              <p class="text-[13.5px] leading-[1.75] text-silver"><?= e($rec) ?></p>
            </li>
          <?php endforeach; ?>
        </ol>

        <div class="border-t border-gold/[0.1] px-6 py-5 sm:px-7">
          <p class="text-[11.5px] leading-relaxed text-silver/70">
            Parecer demonstrativo gerado por protótipo de interface. A revisão definitiva compete
            ao advogado responsável pelo instrumento.
          </p>
        </div>
      </div>
    </div>
  </section>
</div>

<?php require __DIR__ . '/includes/footer-painel.php'; ?>
