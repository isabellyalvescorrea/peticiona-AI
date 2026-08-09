/* ==========================================================================
   Peticiona AI — Exportação em PDF com formatação forense

   gerarPDFJuridico(conteudoTexto, nomeCliente, tipoPeca):
     a) converte o Markdown devolvido pela IA em HTML semântico
     b) injeta o HTML no template oculto #pdf-template
     c) compila com html2pdf.js em A4, margens 30/20/20/30 mm
     d) dispara o download e registra a exportação no localStorage
   ========================================================================== */

(function (global) {
  'use strict';

  /* ---------------------------------------------------------------------
     Markdown → HTML semântico

     Conversor deliberadamente restrito à convenção que o prompt impõe ao
     modelo (#, ##, ###, **, *, >, listas e ::assinatura::). Uma biblioteca
     completa traria sintaxe que a folha jurídica não sabe formatar.
     --------------------------------------------------------------------- */

  function escapar(t) {
    return String(t === undefined || t === null ? '' : t)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  /** Ênfases dentro de uma linha já escapada. */
  function inline(t) {
    return t
      .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
      .replace(/(^|[^*])\*([^*\n]+)\*(?!\*)/g, '$1<em>$2</em>')
      .replace(/__([^_]+)__/g, '<strong>$1</strong>');
  }

  function markdownParaHTML(markdown) {
    var linhas = String(markdown || '').replace(/\r\n/g, '\n').split('\n');
    var html = [];
    var buffer = [];      // parágrafo em construção
    var citacao = [];     // blockquote em construção
    var lista = null;     // 'ul' | 'ol' | null
    var naAssinatura = false;
    var assinatura = [];

    function fecharParagrafo() {
      if (!buffer.length) return;
      html.push('<p>' + inline(buffer.join(' ')) + '</p>');
      buffer = [];
    }

    function fecharCitacao() {
      if (!citacao.length) return;
      html.push('<blockquote><p>' + inline(citacao.join(' ')) + '</p></blockquote>');
      citacao = [];
    }

    function fecharLista() {
      if (!lista) return;
      html.push('</' + lista + '>');
      lista = null;
    }

    function fecharTudo() {
      fecharParagrafo();
      fecharCitacao();
      fecharLista();
    }

    linhas.forEach(function (bruta) {
      var linha = bruta.trim();

      // Marcador de assinatura: tudo daqui para baixo é bloco centralizado.
      if (/^::assinatura::$/i.test(linha)) {
        fecharTudo();
        naAssinatura = true;
        return;
      }

      if (naAssinatura) {
        if (linha) assinatura.push(escapar(linha));
        return;
      }

      if (linha === '') { fecharTudo(); return; }

      var escapada = escapar(linha);

      var titulo = escapada.match(/^(#{1,3})\s+(.*)$/);
      if (titulo) {
        fecharTudo();
        var nivel = titulo[1].length;
        html.push('<h' + nivel + '>' + inline(titulo[2]) + '</h' + nivel + '>');
        return;
      }

      var cit = escapada.match(/^&gt;\s?(.*)$/);
      if (cit) {
        fecharParagrafo();
        fecharLista();
        citacao.push(cit[1]);
        return;
      }
      fecharCitacao();

      var ordenada = escapada.match(/^\d+[.)]\s+(.*)$/);
      if (ordenada) {
        fecharParagrafo();
        if (lista !== 'ol') { fecharLista(); html.push('<ol>'); lista = 'ol'; }
        html.push('<li>' + inline(ordenada[1]) + '</li>');
        return;
      }

      var naoOrdenada = escapada.match(/^[-*•]\s+(.*)$/);
      if (naoOrdenada) {
        fecharParagrafo();
        if (lista !== 'ul') { fecharLista(); html.push('<ul>'); lista = 'ul'; }
        html.push('<li>' + inline(naoOrdenada[1]) + '</li>');
        return;
      }
      fecharLista();

      buffer.push(escapada);
    });

    fecharTudo();

    if (assinatura.length) {
      html.push(
        '<div class="bloco-assinatura">' +
          '<span class="linha-assinatura"></span>' +
          assinatura.map(function (l) { return '<p>' + inline(l) + '</p>'; }).join('') +
        '</div>'
      );
    }

    return '<div class="folha-juridica">' + html.join('\n') + '</div>';
  }

  /* ---------------------------------------------------------------------
     Compilação do PDF
     --------------------------------------------------------------------- */

  function nomeArquivo(tipoPeca, nomeCliente) {
    function limpar(s) {
      return String(s || '')
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')  // remove acentos
        .replace(/[^\w\s-]/g, '')
        .trim()
        .replace(/\s+/g, '_') || 'documento';
    }
    return limpar(tipoPeca) + '_' + limpar(nomeCliente) + '.pdf';
  }

  function gerarPDFJuridico(conteudoTexto, nomeCliente, tipoPeca) {
    if (typeof global.html2pdf !== 'function') {
      return Promise.reject(new Error('html2pdf.js não foi carregado nesta página.'));
    }

    var template = document.getElementById('pdf-template');
    if (!template) {
      return Promise.reject(new Error('Container #pdf-template ausente no DOM.'));
    }

    // (a) e (b): converte e injeta.
    template.innerHTML = markdownParaHTML(conteudoTexto);

    var arquivo = nomeArquivo(tipoPeca, nomeCliente);

    var opt = {
      margin:      [30, 20, 20, 30], // [topo, direita, base, esquerda] em mm
      filename:    arquivo,
      image:       { type: 'jpeg', quality: 0.98 },
      html2canvas: { scale: 2, useCORS: true, letterRendering: true },
      jsPDF:       { unit: 'mm', format: 'a4', orientation: 'portrait' },
      pagebreak:   { mode: ['avoid-all', 'css', 'legacy'] }
    };

    // (c) e (d): compila, baixa e registra.
    return global.html2pdf().set(opt).from(template.firstElementChild).save()
      .then(function () {
        if (global.PeticionaDados) {
          global.PeticionaDados.registrarExportacao({
            formato: 'PDF',
            arquivo: arquivo,
            cliente: nomeCliente
          });
        }
        return arquivo;
      });
  }

  /* ---------------------------------------------------------------------
     Ligação dos botões de exportação
     --------------------------------------------------------------------- */

  function ligarBotoes() {
    Array.prototype.slice.call(document.querySelectorAll('[data-exportar-pdf]')).forEach(function (botao) {
      botao.addEventListener('click', function () {
        var origemSel = botao.getAttribute('data-origem') || '#peca-folha';
        var origem = document.querySelector(origemSel);
        if (!origem) return;

        var registro = localizarRegistro(origem);
        if (!registro || !registro.conteudo) {
          console.warn('[Peticiona] Nada a exportar: gere o documento primeiro.');
          return;
        }

        var rotulo = botao.getAttribute('data-rotulo') || botao.textContent.trim();
        botao.setAttribute('data-rotulo', rotulo);
        botao.textContent = 'Compilando PDF…';
        botao.disabled = true;

        gerarPDFJuridico(registro.conteudo, registro.cliente, registro.tipo)
          .then(function (arquivo) {
            console.log('[Peticiona] PDF exportado:', arquivo);
          })
          .catch(function (erro) {
            console.error('[Peticiona] Falha na exportação:', erro);
          })
          .then(function () {
            botao.textContent = rotulo;
            botao.disabled = false;
          });
      });
    });
  }

  /** Recupera do localStorage o texto original, e não o já renderizado. */
  function localizarRegistro(origem) {
    var Dados = global.PeticionaDados;
    if (!Dados) return null;

    var idPeca = origem.getAttribute('data-peca-id');
    if (idPeca) {
      var peca = Dados.listarPecas().find(function (p) { return p.id === idPeca; });
      if (peca) return { conteudo: peca.conteudoGerado, cliente: peca.cliente, tipo: peca.tipoPeca };
    }

    var idAudit = origem.getAttribute('data-auditoria-id');
    if (idAudit) {
      var a = Dados.listarAuditorias().find(function (x) { return x.id === idAudit; });
      if (a) return { conteudo: a.relatorio, cliente: a.cliente, tipo: 'Parecer' };
    }

    return null;
  }

  /* ---------------------------------------------------------------------
     Ajuste da pré-visualização

     A folha tem 210 mm — 794 px — de largura fixa, que é o que garante a
     fidelidade ao PDF. Quando a coluna é mais estreita, ela é reduzida por
     transform em vez de recortada: as proporções internas permanecem, e o
     documento exportado continua saindo em A4 pleno.
     --------------------------------------------------------------------- */

  var LARGURA_A4 = 794;

  function ajustarPrevia() {
    Array.prototype.slice.call(document.querySelectorAll('.previa-folha')).forEach(function (caixa) {
      var folha = caixa.querySelector('.folha-juridica');
      if (!folha || !folha.firstChild) return;

      folha.style.transform = '';
      caixa.style.height = '';

      var disponivel = caixa.clientWidth;
      if (!disponivel) return;

      var escala = Math.min(1, disponivel / LARGURA_A4);
      if (escala >= 1) return;

      folha.style.transformOrigin = 'top left';
      folha.style.transform = 'scale(' + escala + ')';
      // O transform não encolhe a caixa no fluxo; sem isto sobraria um vão.
      caixa.style.height = Math.ceil(folha.offsetHeight * escala) + 'px';
    });
  }

  var reajuste;
  global.addEventListener('resize', function () {
    global.clearTimeout(reajuste);
    reajuste = global.setTimeout(ajustarPrevia, 120);
  });

  function iniciar() {
    ligarBotoes();
    ajustarPrevia();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciar);
  } else {
    iniciar();
  }

  global.PeticionaPDF = {
    markdownParaHTML: markdownParaHTML,
    gerarPDFJuridico: gerarPDFJuridico,
    nomeArquivo: nomeArquivo,
    ajustarPrevia: ajustarPrevia
  };
})(window);
