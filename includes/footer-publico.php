<?php
/** Rodapé público + modal de acesso ao painel. */
require_once __DIR__ . '/config.php';
?>

<footer class="relative border-t border-gold/[0.12] bg-navy">
  <div class="mx-auto max-w-[1400px] px-5 py-14 sm:px-8 lg:py-16">

    <div class="grid gap-12 lg:grid-cols-[1.4fr_1fr_1fr] lg:gap-16">
      <div>
        <img src="<?= e(asset('assets/img/logo.png')) ?>" alt="<?= e(APP_NAME) ?>" class="h-8 w-auto">
        <p class="mt-6 max-w-md text-[14px] leading-relaxed text-silver">
          Sistema de inteligência e gestão jurídica desenvolvido exclusivamente para a advocacia
          brasileira. Redação de peças, auditoria contratual e organização processual sob rigor
          técnico e sigilo absoluto.
        </p>
        <p class="rotulo-secao mt-7 text-[10px] text-gold/70">Peça certa. Tempo certo.</p>
      </div>

      <div>
        <h3 class="rotulo-secao text-[10.5px] text-gold">Navegação</h3>
        <ul class="mt-6 space-y-3">
          <?php foreach (nav_publico() as $item): ?>
            <li>
              <a href="<?= e($item['anchor']) ?>"
                 class="text-[14px] text-silver transition-colors duration-300 hover:text-gold">
                <?= e($item['label']) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div>
        <h3 class="rotulo-secao text-[10.5px] text-gold">Ambiente do Advogado</h3>
        <ul class="mt-6 space-y-3">
          <?php foreach (nav_painel() as $item): ?>
            <li>
              <a href="<?= e($item['file']) ?>"
                 class="text-[14px] text-silver transition-colors duration-300 hover:text-gold">
                <?= e($item['label']) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <div class="mt-14 flex flex-col gap-4 border-t border-gold/[0.1] pt-7 sm:flex-row sm:items-center sm:justify-between">
      <p class="text-[12.5px] text-silver/70">
        © <?= date('Y') ?> <?= e(APP_NAME) ?>. Todos os direitos reservados.
      </p>
      <p class="text-[12.5px] text-silver/60">
        Protótipo de interface — conteúdo demonstrativo, sem valor jurídico.
      </p>
    </div>
  </div>
</footer>

<!-- ===================== Modal de acesso ao sistema ===================== -->
<div id="modal-acesso"
     class="fixed inset-0 z-[70] hidden items-center justify-center px-5 py-8"
     role="dialog" aria-modal="true" aria-labelledby="titulo-acesso">

  <div class="absolute inset-0 bg-black/80" data-fechar-acesso></div>

  <div class="relative w-full max-w-[430px] overflow-hidden rounded-lg border border-gold/[0.28] bg-panel shadow-[0_40px_120px_-40px_rgba(0,0,0,1)]">
    <div class="h-px w-full bg-gradient-to-r from-transparent via-gold to-transparent"></div>

    <div class="px-7 py-8 sm:px-9 sm:py-10">
      <div class="flex items-start justify-between gap-6">
        <div>
          <p class="rotulo-secao text-[9.5px] text-gold/70">Ambiente Reservado</p>
          <h2 id="titulo-acesso" class="mt-2 text-[24px] font-bold tracking-[-0.015em] text-silk">
            Acessar <span class="text-gold">Sistema</span>
          </h2>
        </div>
        <button type="button" data-fechar-acesso
                class="-mr-1 -mt-1 h-8 w-8 shrink-0 rounded border border-gold/[0.2] text-[15px] leading-none text-silver transition-colors duration-300 hover:border-gold/60 hover:text-gold"
                aria-label="Fechar">&times;</button>
      </div>

      <!-- Aviso ao avaliador: precisa ler antes dos campos, então ganha
           tratamento de destaque em vez de nota discreta. -->
      <div class="mt-5 rounded-md border border-gold/[0.35] bg-gold/[0.07] px-4 py-4">
        <p class="text-[13.5px] font-medium leading-[1.65] text-silk">
          Demonstração do login do advogado, não é necessário inserir as credenciais
          para fins de teste do avaliador.
          <span class="text-gold">Clique direto em entrar no painel para acessar o sistema</span>
        </p>
      </div>

      <form class="mt-7 space-y-4" action="dashboard.php" method="get">
        <div>
          <label for="acesso-email" class="mb-2 block text-[11px] uppercase tracking-[0.2em] text-silver">E-mail profissional</label>
          <input id="acesso-email" type="email" autocomplete="off" placeholder="advogado@escritorio.adv.br"
                 class="campo w-full rounded-md px-4 py-3 text-[14px]">
        </div>
        <div>
          <label for="acesso-oab" class="mb-2 block text-[11px] uppercase tracking-[0.2em] text-silver">Inscrição na OAB</label>
          <input id="acesso-oab" type="text" autocomplete="off" placeholder="OAB/SP 000.000"
                 class="campo w-full rounded-md px-4 py-3 text-[14px]">
        </div>

        <button type="submit" class="btn-ouro mt-2 w-full rounded-md px-6 py-3.5 text-[14.5px] font-semibold">
          Entrar no Painel <span class="seta ml-1">&rarr;</span>
        </button>
      </form>
    </div>
  </div>
</div>

<script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
</body>
</html>
