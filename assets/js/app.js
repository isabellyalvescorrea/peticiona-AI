/* ==========================================================================
   Peticiona AI — Comportamento de interface (navegação, revelações, filtros).

   A geração de peças, a auditoria de contratos e a exportação em PDF vivem em
   gemini.js, dados.js e pdf-juridico.js, carregados apenas nas telas que os
   usam. Este arquivo cuida do que é comum a todas.
   ========================================================================== */

(function () {
  'use strict';

  var doc = document;
  var $  = function (sel, ctx) { return (ctx || doc).querySelector(sel); };
  var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || doc).querySelectorAll(sel)); };

  /* ---------------------------------------------------------------------
     1. Menu mobile
     --------------------------------------------------------------------- */
  (function menuMobile() {
    var botao = $('#botao-menu');
    var painel = $('#menu-mobile');
    if (!botao || !painel) return;

    function fechar() {
      painel.classList.add('hidden');
      botao.classList.remove('menu-aberto');
      botao.setAttribute('aria-expanded', 'false');
      botao.setAttribute('aria-label', 'Abrir menu de navegação');
    }

    botao.addEventListener('click', function () {
      var aberto = !painel.classList.contains('hidden');
      if (aberto) {
        fechar();
      } else {
        painel.classList.remove('hidden');
        botao.classList.add('menu-aberto');
        botao.setAttribute('aria-expanded', 'true');
        botao.setAttribute('aria-label', 'Fechar menu de navegação');
      }
    });

    $$('[data-fecha-menu]').forEach(function (el) {
      el.addEventListener('click', fechar);
    });

    // Ao voltar para desktop, garante estado limpo.
    window.addEventListener('resize', function () {
      if (window.innerWidth >= 1024) fechar();
    });
  })();

  /* ---------------------------------------------------------------------
     2. Modal "Acessar Sistema"
     --------------------------------------------------------------------- */
  (function modalAcesso() {
    var modal = $('#modal-acesso');
    if (!modal) return;

    function abrir() {
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      doc.body.style.overflow = 'hidden';
      var campo = $('#acesso-email', modal);
      if (campo) window.setTimeout(function () { campo.focus(); }, 60);
    }

    function fechar() {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      doc.body.style.overflow = '';
    }

    $$('[data-abrir-acesso]').forEach(function (el) {
      el.addEventListener('click', abrir);
    });
    $$('[data-fechar-acesso]', modal).forEach(function (el) {
      el.addEventListener('click', fechar);
    });
    doc.addEventListener('keydown', function (ev) {
      if (ev.key === 'Escape' && !modal.classList.contains('hidden')) fechar();
    });
  })();

  /* ---------------------------------------------------------------------
     3. Revelação das seções ao rolar
     --------------------------------------------------------------------- */
  (function revelar() {
    var alvos = $$('.revelar');
    if (!alvos.length) return;

    function mostrar(el) { el.classList.add('visivel'); }
    function mostrarTodos() { alvos.forEach(mostrar); }

    if (!('IntersectionObserver' in window)) {
      mostrarTodos();
      return;
    }

    var observadorAtuou = false;

    var obs = new IntersectionObserver(function (entradas) {
      observadorAtuou = true;
      entradas.forEach(function (entrada) {
        if (!entrada.isIntersecting) return;
        var atraso = parseInt(entrada.target.getAttribute('data-atraso') || '0', 10);
        window.setTimeout(function () { mostrar(entrada.target); }, atraso);
        obs.unobserve(entrada.target);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

    alvos.forEach(function (el) { obs.observe(el); });

    // Redes de segurança. A animação de entrada é progressive enhancement e nunca
    // pode deixar conteúdo permanentemente invisível.
    //
    // A primeira cobre o caso comum: o observador avalia as posições antes de o
    // layout assentar (a folha do Tailwind chega por CDN e desloca tudo), de modo
    // que um elemento já dentro da janela pode ser medido como fora e nunca mais
    // receber um evento, porque a página não rola. Passado o tempo de assentar,
    // qualquer alvo visível é revelado.
    window.setTimeout(function () {
      alvos.forEach(function (el) {
        if (el.classList.contains('visivel')) return;
        var r = el.getBoundingClientRect();
        if (r.top < window.innerHeight && r.bottom > 0) mostrar(el);
      });
    }, 1200);

    // A segunda cobre o observador que nunca funciona — janelas sem composição de
    // frames, painéis embutidos, pré-renderizadores e capturas automatizadas.
    window.setTimeout(function () {
      if (observadorAtuou) return;
      obs.disconnect();
      mostrarTodos();
    }, 2500);
  })();

  /* ---------------------------------------------------------------------
     4. Destaque do item de menu conforme a seção visível (scroll spy)

     Baseado em geometria, e não em IntersectionObserver: o observer só avisa
     quando um limiar é *cruzado*, de modo que seções altas podiam nunca atingir
     o threshold e o destaque ficava preso na última que disparou. Aqui a seção
     ativa é recalculada do zero a cada quadro, o que garante um — e apenas um —
     link destacado.
     --------------------------------------------------------------------- */
  (function menuAtivo() {
    var DESLOCAMENTO = 84; // igual ao scroll-padding-top do html

    var links = $$('header .link-nav');
    if (!links.length) return;

    // Só entram no rodízio os links cuja seção existe de fato na página.
    var pares = [];
    links.forEach(function (link) {
      var id = (link.getAttribute('href') || '').replace(/^.*#/, '');
      var secao = id && doc.getElementById(id);
      if (secao) pares.push({ link: link, secao: secao });
    });
    if (!pares.length) return;

    function topoDe(el) {
      return el.getBoundingClientRect().top + window.pageYOffset;
    }

    function atualizar() {
      // A tolerância cobre o arredondamento subpixel: ao clicar num link, a
      // rolagem para exatamente no topo da seção menos o deslocamento, e uma
      // fração de pixel a menos destacaria a seção anterior.
      var linha = window.pageYOffset + DESLOCAMENTO + 4;
      var ativo = pares[0];
      var melhorTopo = -Infinity;

      // Vence a seção mais baixa cujo topo já cruzou a linha de leitura.
      pares.forEach(function (par) {
        var topo = topoDe(par.secao);
        if (topo <= linha && topo >= melhorTopo) {
          melhorTopo = topo;
          ativo = par;
        }
      });

      // No fim da página a última seção é a que está sendo lida, ainda que curta.
      var fimDaPagina = window.pageYOffset + window.innerHeight >=
                        doc.documentElement.scrollHeight - 2;
      if (fimDaPagina) ativo = pares[pares.length - 1];

      // O destaque sai de TODOS os links antes de entrar em um único.
      pares.forEach(function (par) {
        par.link.classList.remove('link-ativo');
        par.link.removeAttribute('aria-current');
      });
      ativo.link.classList.add('link-ativo');
      ativo.link.setAttribute('aria-current', 'true');
    }

    var agendado = false;
    function agendar() {
      if (agendado) return;
      agendado = true;
      window.requestAnimationFrame(function () {
        agendado = false;
        atualizar();
      });
    }

    window.addEventListener('scroll', agendar, { passive: true });
    window.addEventListener('resize', agendar);
    window.addEventListener('load', agendar);
    atualizar();
  })();

  /* ---------------------------------------------------------------------
     5. Painel: menu lateral em telas pequenas
     --------------------------------------------------------------------- */
  (function menuPainel() {
    var botao = $('#botao-painel');
    var aside = $('#lateral-painel');
    var veu   = $('#veu-painel');
    if (!botao || !aside) return;

    function alternar(abrir) {
      aside.classList.toggle('-translate-x-full', !abrir);
      if (veu) veu.classList.toggle('hidden', !abrir);
      botao.classList.toggle('menu-aberto', abrir);
      botao.setAttribute('aria-expanded', abrir ? 'true' : 'false');
    }

    botao.addEventListener('click', function () {
      alternar(aside.classList.contains('-translate-x-full'));
    });
    if (veu) veu.addEventListener('click', function () { alternar(false); });
  })();

  /* ---------------------------------------------------------------------
     6. Exportações ainda não implementadas (.docx e WhatsApp)

     O PDF é real e vive em pdf-juridico.js. Estes dois formatos ainda não
     existem, e o botão diz isso em vez de fingir que baixou algo.
     --------------------------------------------------------------------- */
  (function exportacoesPendentes() {
    $$('[data-exportar-pendente]').forEach(function (b) {
      b.addEventListener('click', function () {
        var rotulo = b.getAttribute('data-rotulo') || b.textContent.trim();
        b.setAttribute('data-rotulo', rotulo);
        b.textContent = 'Formato ainda não disponível';
        b.disabled = true;
        window.setTimeout(function () {
          b.textContent = rotulo;
          b.disabled = false;
        }, 1800);
      });
    });
  })();

  /* ---------------------------------------------------------------------
     7. Meus Clientes — filtro local da listagem

     As linhas são desenhadas por dados.js a partir do localStorage, então a
     consulta ao DOM acontece a cada digitação: guardar a lista numa variável
     deixaria o filtro cego para tudo que fosse cadastrado depois da carga.
     --------------------------------------------------------------------- */
  (function filtroClientes() {
    var campo = $('#filtro-clientes');
    if (!campo) return;

    var contador = $('#contador-clientes');

    function filtrar() {
      var termo = campo.value.trim().toLowerCase();
      var visiveis = 0;

      $$('[data-cliente]').forEach(function (linha) {
        var texto = (linha.getAttribute('data-cliente') || '').toLowerCase();
        var casa = !termo || texto.indexOf(termo) !== -1;
        linha.classList.toggle('hidden', !casa);
        if (casa) visiveis++;
      });

      // Com a busca vazia o total volta a ser dito por dados.js, que conhece
      // a carteira inteira; aqui só se informa o resultado da filtragem.
      if (contador && termo) {
        contador.textContent = visiveis + (visiveis === 1 ? ' registro' : ' registros');
      }
    }

    campo.addEventListener('input', filtrar);
    // Redesenhos da carteira precisam reaplicar o filtro em vigor.
    doc.addEventListener('peticiona:dados', function () {
      if (campo.value.trim()) window.setTimeout(filtrar, 0);
    });
  })();
})();