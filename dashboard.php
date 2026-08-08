<?php
/** Painel — Visão Geral do escritório (protótipo visual, dados fictícios). */
require_once __DIR__ . '/includes/config.php';

$pagina_titulo    = 'Visão Geral — ' . APP_NAME;
$painel_titulo    = 'Visão Geral';
$painel_subtitulo = 'Panorama do escritório — agosto de ' . date('Y');

// Estado de primeiro acesso: nada foi produzido ainda.
$indicadores = [
    ['rotulo' => 'Peças Geradas no Mês',  'valor' => '0', 'variacao' => 'Nenhuma peça gerada ainda',    'barra' => 0],
    ['rotulo' => 'Contratos Analisados',  'valor' => '0', 'variacao' => 'Aguardando primeira análise',  'barra' => 0],
    ['rotulo' => 'Horas Economizadas',    'valor' => '0', 'variacao' => 'Inicie o uso para contabilizar', 'barra' => 0],
    ['rotulo' => 'Clientes Ativos',       'valor' => '0', 'variacao' => 'Nenhum cliente cadastrado',    'barra' => 0],
];

$atalhos = [
    ['titulo' => 'Gerar nova peça',        'texto' => 'Petição inicial, contestação, recurso ou agravo em minutos.', 'file' => 'gerador-de-pecas.php'],
    ['titulo' => 'Auditar um contrato',    'texto' => 'Cole a minuta e receba riscos, omissões e recomendações.',    'file' => 'analisador-de-contratos.php'],
    ['titulo' => 'Abrir minha carteira',   'texto' => 'Clientes, números de processo e peças arquivadas.',           'file' => 'meus-clientes.php'],
];

$atividades = [
    ['hora' => 'Hoje, 09:42', 'titulo' => 'Petição inicial — Ação de obrigação de fazer',      'cliente' => 'Marcelo Andrade Ribeiro',   'estado' => 'Exportada em .docx'],
    ['hora' => 'Hoje, 08:15', 'titulo' => 'Contestação trabalhista — horas extras',            'cliente' => 'Metalúrgica Vertentes Ltda.', 'estado' => 'Revisão pendente'],
    ['hora' => 'Ontem, 18:07','titulo' => 'Auditoria — contrato de prestação de serviços',     'cliente' => 'Nexus Participações S.A.',   'estado' => '3 riscos críticos'],
    ['hora' => 'Ontem, 16:30','titulo' => 'Resumo executivo enviado ao cliente',               'cliente' => 'Beatriz Camargo Nogueira',   'estado' => 'WhatsApp'],
    ['hora' => 'Ontem, 11:52','titulo' => 'Agravo de instrumento — tutela indeferida',         'cliente' => 'Condomínio Alto das Palmas',  'estado' => 'Protocolada'],
];

$distribuicao = [
    ['area' => 'Cível & Processo Civil',   'percentual' => 38],
    ['area' => 'Trabalhista',              'percentual' => 24],
    ['area' => 'Consumidor',               'percentual' => 17],
    ['area' => 'Família & Sucessões',      'percentual' => 13],
    ['area' => 'Penal',                    'percentual' => 8],
];

require __DIR__ . '/includes/header-painel.php';
?>

<!-- Orientação ao avaliador -->
<section class="revelar mb-5 xl:mb-6" aria-label="Boas-vindas">
  <div class="overflow-hidden rounded-lg border border-gold/[0.35] bg-gold/[0.07]">
    <div class="h-px w-full bg-gradient-to-r from-transparent via-gold to-transparent"></div>
    <p class="px-6 py-5 text-[14px] leading-[1.7] text-silk sm:px-7 sm:text-[14.5px]">
      <span class="mr-1" aria-hidden="true">👋</span>
      Bem-vindo ao Peticiona AI! Este é o seu ambiente de testes. Para começar a experimentar
      a inteligência do sistema, utilize os atalhos abaixo ou o menu lateral para
      <a href="gerador-de-pecas.php" class="text-gold underline decoration-gold/40 underline-offset-4 transition-colors duration-300 hover:decoration-gold">Gerar nova peça</a>
      ou
      <a href="analisador-de-contratos.php" class="text-gold underline decoration-gold/40 underline-offset-4 transition-colors duration-300 hover:decoration-gold">Auditar um contrato</a>.
    </p>
  </div>
</section>

<!-- Indicadores -->
<section aria-label="Indicadores do escritório">
  <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 xl:gap-5">
    <?php foreach ($indicadores as $i => $ind): ?>
      <article class="cartao cartao-fio revelar overflow-hidden rounded-lg p-6" data-atraso="<?= $i * 70 ?>">
        <p class="text-[11px] uppercase tracking-[0.18em] text-silver"><?= e($ind['rotulo']) ?></p>
        <p class="mt-4 text-[40px] font-light leading-none text-silk"><?= e($ind['valor']) ?></p>
        <div class="filete-progresso mt-5 rounded-full">
          <span style="width:<?= (int) $ind['barra'] ?>%"></span>
        </div>
        <p class="mt-3 text-[12px] text-gold/80"><?= e($ind['variacao']) ?></p>
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

    <ul>
      <?php foreach ($atividades as $i => $ato): ?>
        <li class="<?= $i > 0 ? 'border-t border-gold/[0.07]' : '' ?> px-6 py-5 transition-colors duration-500 hover:bg-gold/[0.025] sm:px-7">
          <div class="flex flex-col gap-1.5 sm:flex-row sm:items-baseline sm:justify-between sm:gap-6">
            <div class="min-w-0">
              <p class="text-[14.5px] leading-snug text-silk"><?= e($ato['titulo']) ?></p>
              <p class="mt-1.5 text-[12.5px] text-silver"><?= e($ato['cliente']) ?></p>
            </div>
            <div class="shrink-0 sm:text-right">
              <p class="text-[11.5px] text-silver/70"><?= e($ato['hora']) ?></p>
              <p class="mt-1.5 text-[11.5px] text-gold/80"><?= e($ato['estado']) ?></p>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>

    <div class="border-t border-gold/[0.1] px-6 py-4 sm:px-7">
      <a href="meus-clientes.php" class="link-seta text-[12.5px] uppercase tracking-[0.16em] text-gold">
        Ver histórico completo <span class="seta ml-1">&rarr;</span>
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
        <div>
          <div class="flex items-baseline justify-between gap-4">
            <p class="text-[13.5px] text-silk"><?= e($linha['area']) ?></p>
            <p class="ordinal text-[13px] text-gold"><?= (int) $linha['percentual'] ?>%</p>
          </div>
          <div class="filete-progresso mt-2.5 rounded-full">
            <span style="width:<?= (int) $linha['percentual'] ?>%"></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="border-t border-gold/[0.1] px-6 py-5 sm:px-7">
      <p class="text-[12px] leading-relaxed text-silver/70">
        Os percentuais são recalculados a cada nova peça produzida.
      </p>
    </div>
  </article>
</section>

<?php require __DIR__ . '/includes/footer-painel.php'; ?>
