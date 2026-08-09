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
     Exportação em Word

     O Word lê HTML com CSS embutido e monta o documento a partir dele, então
     a mesma folha que vira PDF vira .docx sem uma segunda diagramação. As
     medidas são declaradas em pt e cm, que é o que o Word entende de papel —
     px seriam reinterpretados na abertura.
     --------------------------------------------------------------------- */

  /** Folha de estilo que acompanha o arquivo, em unidades de impressão. */
  var ESTILO_WORD = [
    '@page WordSection1 {',
    '  size: 21cm 29.7cm;',
    '  margin: 3cm 2cm 2cm 3cm;',   // superior, direita, inferior, esquerda
    '}',
    'div.WordSection1 { page: WordSection1; }',
    'body {',
    '  font-family: "Times New Roman", Times, serif;',
    '  font-size: 12pt;',
    '  color: #000000;',
    '  text-align: justify;',
    '}',
    'p {',
    '  text-indent: 1.5cm;',
    '  margin: 0 0 10pt 0;',
    '  line-height: 150%;',
    '  text-align: justify;',
    '}',
    'p.sem-recuo { text-indent: 0; }',
    'h1, h2, h3 {',
    '  font-family: "Times New Roman", Times, serif;',
    '  font-weight: bold;',
    '  text-transform: uppercase;',
    '  text-indent: 0;',
    '  margin: 18pt 0 8pt 0;',
    '  page-break-after: avoid;',
    '}',
    'h1 { font-size: 14pt; text-align: center; }',
    'h2, h3 { font-size: 12pt; text-align: left; }',
    'blockquote {',
    '  font-size: 10pt;',
    '  line-height: 100%;',
    '  margin: 10pt 0 10pt 4cm;',
    '  text-indent: 0;',
    '  text-align: justify;',
    '}',
    'blockquote p { font-size: 10pt; line-height: 100%; text-indent: 0; margin: 0; }',
    'ul, ol { margin: 0 0 10pt 0; }',
    'li { margin-bottom: 4pt; text-indent: 0; }',
    '.bloco-assinatura { text-align: center; margin-top: 30pt; text-indent: 0; }',
    '.bloco-assinatura p { text-align: center; text-indent: 0; margin: 0; }',
    '.linha-assinatura {',
    '  display: block;',
    '  width: 8cm;',
    '  margin: 0 auto 6pt auto;',
    '  border-top: 1pt solid #000000;',
    '}'
  ].join('\n');

  function documentoWord(corpoHtml) {
    // Os namespaces da Microsoft são o que faz o Word tratar o arquivo como
    // documento próprio, e não como página da web importada.
    return '<!DOCTYPE html>' +
      '<html xmlns:o="urn:schemas-microsoft-com:office:office" ' +
            'xmlns:w="urn:schemas-microsoft-com:office:word" ' +
            'xmlns="http://www.w3.org/TR/REC-html40">' +
      '<head><meta charset="utf-8">' +
      '<title>Peça processual</title>' +
      '<style>' + ESTILO_WORD + '</style>' +
      '</head><body><div class="WordSection1">' + corpoHtml + '</div></body></html>';
  }

  function baixarBlob(blob, arquivo) {
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = arquivo;
    document.body.appendChild(a);
    a.click();
    a.remove();
    // Revoga só depois de o navegador iniciar o download.
    window.setTimeout(function () { URL.revokeObjectURL(url); }, 1500);
  }

  // A4 em twips (1 mm = 56,7): 210 mm x 297 mm.
  var A4_LARGURA = 11906;
  var A4_ALTURA  = 16838;

  /**
   * O html-docx-js fixa o papel em Carta (12240 x 15840 twips) e não oferece
   * opção de tamanho. Para peça brasileira isso desloca margens e muda a
   * paginação, então o pacote é reaberto e a definição de página corrigida
   * para A4. Sem JSZip disponível, devolve o pacote como veio.
   */
  function corrigirParaA4(blob) {
    if (!global.JSZip) return Promise.resolve(blob);

    return global.JSZip.loadAsync(blob)
      .then(function (zip) {
        return zip.file('word/document.xml').async('string').then(function (xml) {
          var corrigido = xml
            .replace(/w:w="\d+"/, 'w:w="' + A4_LARGURA + '"')
            .replace(/w:h="\d+"/, 'w:h="' + A4_ALTURA + '"');
          zip.file('word/document.xml', corrigido);
          return zip.generateAsync({
            type: 'blob',
            mimeType: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
          });
        });
      })
      .catch(function (erro) {
        console.warn('[Peticiona] Não foi possível ajustar o papel para A4:', erro);
        return blob;
      });
  }

  function gerarWordJuridico(conteudoTexto, nomeCliente, tipoPeca) {
    var html = markdownParaHTML(conteudoTexto);
    var completo = documentoWord(html);
    var base = nomeArquivo(tipoPeca, nomeCliente).replace(/\.pdf$/, '');

    // Sem html-docx-js, o HTML com os namespaces do Office ainda abre
    // corretamente como .doc — Word e Google Docs leem ambos, e esse caminho
    // não depende de CDN nenhuma.
    if (!global.htmlDocx || typeof global.htmlDocx.asBlob !== 'function') {
      var arquivoDoc = base + '.doc';
      baixarBlob(new Blob(['﻿' + completo], { type: 'application/msword;charset=utf-8' }), arquivoDoc);
      registrarSaida('DOC', arquivoDoc, nomeCliente);
      return Promise.resolve(arquivoDoc);
    }

    var arquivo = base + '.docx';
    var pacote = global.htmlDocx.asBlob(completo, {
      orientation: 'portrait',
      // Margens em twips, na ordem forense: 30/20/20/30 mm.
      margins: { top: 1701, right: 1134, bottom: 1134, left: 1701 }
    });

    return corrigirParaA4(pacote).then(function (finalizado) {
      baixarBlob(finalizado, arquivo);
      registrarSaida('DOCX', arquivo, nomeCliente);
      return arquivo;
    });
  }

  function registrarSaida(formato, arquivo, cliente) {
    if (!global.PeticionaDados) return;
    global.PeticionaDados.registrarExportacao({
      formato: formato, arquivo: arquivo, cliente: cliente
    });
  }

  /* ---------------------------------------------------------------------
     Ligação dos botões de exportação
     --------------------------------------------------------------------- */

  function ligarBotoes() {
    var formatos = [
      { seletor: '[data-exportar-pdf]',  rotuloCarga: 'Compilando PDF…',  gerar: gerarPDFJuridico },
      { seletor: '[data-exportar-word]', rotuloCarga: 'Montando Word…',   gerar: gerarWordJuridico }
    ];

    formatos.forEach(function (formato) {
      Array.prototype.slice.call(document.querySelectorAll(formato.seletor)).forEach(function (botao) {
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
          botao.textContent = formato.rotuloCarga;
          botao.disabled = true;

          formato.gerar(registro.conteudo, registro.cliente, registro.tipo)
            .then(function (arquivo) {
              console.log('[Peticiona] Documento exportado:', arquivo);
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

  /**
   * A prévia em tela é dimensionada só por CSS, com largura fluida. A versão
   * anterior mantinha os 794 px e reduzia por transform, o que espremia o
   * corpo abaixo de 10 pt no notebook e ainda cortava linhas na borda. Segue
   * existindo como função vazia porque gemini.js a chama após renderizar.
   */
  function ajustarPrevia() {}

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', ligarBotoes);
  } else {
    ligarBotoes();
  }

  global.PeticionaPDF = {
    markdownParaHTML: markdownParaHTML,
    gerarPDFJuridico: gerarPDFJuridico,
    gerarWordJuridico: gerarWordJuridico,
    nomeArquivo: nomeArquivo,
    ajustarPrevia: ajustarPrevia
  };
})(window);
