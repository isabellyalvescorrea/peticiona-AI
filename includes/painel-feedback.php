<?php
/**
 * Retorno visual comum às ferramentas do painel: a pilha de avisos flutuantes
 * e o modal do resumo ao cliente.
 *
 * Fica num include próprio porque Gerador de Peças e Analisador de Contratos
 * usam exatamente a mesma marcação — duplicá-la faria as duas telas divergirem
 * na primeira alteração.
 */
require_once __DIR__ . '/config.php';
?>

<!-- Avisos flutuantes. O conteúdo é criado por gemini.js. -->
<div class="pilha-avisos" id="pilha-avisos" role="status" aria-live="polite"></div>

<!-- ===================== Resumo para o cliente ===================== -->
<div id="modal-resumo"
     class="fixed inset-0 z-[70] hidden items-center justify-center px-5 py-8"
     role="dialog" aria-modal="true" aria-labelledby="titulo-resumo">

  <div class="absolute inset-0 bg-black/80" data-fechar-resumo></div>

  <div class="relative flex max-h-full w-full max-w-[560px] flex-col overflow-hidden rounded-lg border border-gold/[0.28] bg-panel shadow-[0_40px_120px_-40px_rgba(0,0,0,1)]">
    <div class="h-px w-full bg-gradient-to-r from-transparent via-gold to-transparent"></div>

    <div class="flex items-start justify-between gap-6 px-7 pt-7 sm:px-8">
      <div>
        <p class="rotulo-secao text-[9.5px] text-gold/70">Comunicação ao Cliente</p>
        <h2 id="titulo-resumo" class="mt-2 text-[22px] font-bold tracking-[-0.015em] text-silk">
          Resumo em <span class="text-gold">linguagem acessível</span>
        </h2>
      </div>
      <button type="button" data-fechar-resumo
              class="-mr-1 h-8 w-8 shrink-0 rounded border border-gold/[0.2] text-[15px] leading-none text-silver transition-colors duration-300 hover:border-gold/60 hover:text-gold"
              aria-label="Fechar">&times;</button>
    </div>

    <!-- Composição -->
    <div id="resumo-carregando" class="flex min-h-[220px] flex-col items-center justify-center px-8 py-12 text-center">
      <p class="rotulo-secao text-[10px] text-gold/75">Traduzindo o documento</p>
      <p class="mt-4 text-[26px] leading-none text-gold">
        <span class="ponto-carga inline-block">.</span><span class="ponto-carga inline-block">.</span><span class="ponto-carga inline-block">.</span>
      </p>
      <p class="mt-5 max-w-[340px] text-[12.5px] leading-relaxed text-silver">
        Reescrevendo o conteúdo técnico em termos que o seu cliente entenda.
      </p>
    </div>

    <!-- Resultado -->
    <div id="resumo-conteudo" class="hidden min-h-0 flex-1 overflow-y-auto px-7 py-6 sm:px-8">
      <p class="resumo-corpo text-[14px] leading-[1.75] text-slate-200" data-resumo-texto></p>
    </div>

    <!-- Falha -->
    <div id="resumo-erro" class="hidden px-7 py-10 text-center sm:px-8">
      <p class="text-[13.5px] leading-relaxed text-sapphire" data-resumo-erro></p>
    </div>

    <div class="shrink-0 border-t border-gold/[0.1] px-7 py-5 sm:px-8">
      <div class="flex flex-col gap-3 sm:flex-row">
        <button type="button" id="copiar-resumo"
                class="btn-ouro grow rounded-md px-6 py-3 text-[14px] font-semibold">
          Copiar Resumo
        </button>
        <button type="button" data-fechar-resumo
                class="btn-contorno rounded-md px-6 py-3 text-[14px] font-medium">
          Fechar
        </button>
      </div>
      <p class="mt-4 text-[11.5px] leading-relaxed text-silver/70">
        Confira o texto antes de enviar: a tradução é automática e a comunicação
        ao cliente permanece sob responsabilidade do advogado.
      </p>
    </div>
  </div>
</div>
