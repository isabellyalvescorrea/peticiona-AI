/* ==========================================================================
   Peticiona AI — Handlers da API do Gemini

   Fluxo, do formulário ao painel:
     1. captura dos campos e montagem do prompt técnico-jurídico
     2. POST para /api/gemini.php (a chave fica no servidor, nunca aqui)
     3. renderização do texto retornado na área de leitura
     4. gravação em JSON no localStorage e recálculo do Dashboard

   A chave vive apenas no servidor. Falha na chamada é exibida como falha:
   não há resposta de reserva que pudesse passar por produção da IA.
   ========================================================================== */

(function (global) {
  'use strict';

  var ENDPOINT = '/api/gemini.php';
  var Dados = global.PeticionaDados;

  function $(s, ctx) { return (ctx || document).querySelector(s); }
  function $$(s, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(s)); }
  function valor(sel) { var el = $(sel); return el ? el.value.trim() : ''; }

  /* ---------------------------------------------------------------------
     Montagem dos prompts
     --------------------------------------------------------------------- */

  var DIRETRIZ = [
    'Você é um advogado brasileiro sênior redigindo para protocolo em juízo.',
    'Escreva em português do Brasil, registro culto e técnica processual rigorosa.',
    'Fundamente em legislação vigente (CPC/2015, CC, CLT, CDC, CF/88) e em',
    'jurisprudência consolidada dos Tribunais Superiores, citando os dispositivos.',
    '',
    'Formate a resposta em Markdown, obedecendo a esta convenção:',
    '  # para o endereçamento ou título principal',
    '  ## para as seções (I — DOS FATOS, II — DO DIREITO, III — DOS PEDIDOS)',
    '  **negrito** para qualificações e destaques',
    '  > para citações de doutrina ou jurisprudência',
    '  ::assinatura:: na linha que antecede o bloco de assinatura',
    '',
    'Não inclua comentários seus, avisos, nem texto fora da peça.'
  ].join('\n');

  function montarPromptPeca(d) {
    return DIRETRIZ + '\n\n' + [
      'TAREFA: redigir a peça processual abaixo, integralmente.',
      '',
      'TIPO DE PEÇA: ' + (d.tipoPeca || 'Petição Inicial'),
      '',
      'QUALIFICAÇÃO DO AUTOR:',
      d.autor || '(não informada)',
      '',
      'QUALIFICAÇÃO DO RÉU:',
      d.reu || '(não informada)',
      '',
      'FATOS DO CASO:',
      d.fatos || '(não informados)',
      '',
      'PEDIDOS:',
      d.pedidos || '(não informados)'
    ].join('\n');
  }

  function montarPromptAuditoria(d) {
    return DIRETRIZ + '\n\n' + [
      'TAREFA: auditar o contrato abaixo e emitir parecer técnico.',
      '',
      'Estruture o parecer em quatro seções:',
      '  I — DELIMITAÇÃO DO OBJETO',
      '  II — RISCOS CRÍTICOS IDENTIFICADOS (uma cláusula por parágrafo, com o',
      '       dispositivo legal violado e o grau de risco)',
      '  III — CLÁUSULAS AUSENTES',
      '  IV — RECOMENDAÇÕES (redações alternativas seguras)',
      '',
      'CONTRATO SUBMETIDO:',
      d.texto || '(nenhum texto submetido)'
    ].join('\n');
  }

  /* ---------------------------------------------------------------------
     Chamada HTTP
     --------------------------------------------------------------------- */

  function requisicao(payload) {
    return fetch(ENDPOINT, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    }).then(function (resposta) {
      return resposta.json().then(function (corpo) {
        if (!resposta.ok) {
          var e = new Error(corpo.erro || ('HTTP ' + resposta.status));
          e.reptivel = !!corpo.reptivel;
          throw e;
        }
        return corpo;
      });
    });
  }

  /**
   * Peça processual é texto muito padronizado, e o Gemini às vezes barra a
   * própria saída como RECITATION por reconhecê-la como reprodução de material
   * conhecido. O bloqueio é intermitente: subir a temperatura afasta a geração
   * do trecho memorizado.
   *
   * A insistência acontece aqui, e não no servidor, porque uma peça leva de 25
   * a 35 s — duas tentativas estourariam o teto de 60 s da função.
   */
  var TEMPERATURAS = [0.35, 0.8];

  function chamarGemini(payload, aoTentarNovamente) {
    function tentativa(i) {
      var corpo = Object.assign({}, payload, { temperatura: TEMPERATURAS[i] });
      return requisicao(corpo).catch(function (erro) {
        if (!erro.reptivel || i >= TEMPERATURAS.length - 1) throw erro;
        console.warn('[Peticiona] Bloqueio por recitação; refazendo com mais variação.');
        if (aoTentarNovamente) aoTentarNovamente();
        return tentativa(i + 1);
      });
    }
    return tentativa(0);
  }

  /* ---------------------------------------------------------------------
     Estado das telas
     --------------------------------------------------------------------- */

  function mostrar(el) { if (el) el.classList.remove('hidden'); }
  function ocultar(el) { if (el) el.classList.add('hidden'); }

  /**
   * Botão em trabalho: rótulo próprio, anel girando e clique bloqueado.
   * O rótulo original fica guardado no próprio elemento para que a restauração
   * não dependa de quem chamou.
   */
  function ocupar(botao, rotulo) {
    if (!botao) return;
    if (!botao.hasAttribute('data-rotulo-original')) {
      botao.setAttribute('data-rotulo-original', botao.innerHTML);
    }
    botao.innerHTML = '<span class="anel-carga" aria-hidden="true"></span>' + rotulo;
    botao.disabled = true;
    botao.setAttribute('aria-busy', 'true');
  }

  function liberar(botao) {
    if (!botao) return;
    var original = botao.getAttribute('data-rotulo-original');
    if (original !== null) botao.innerHTML = original;
    botao.disabled = false;
    botao.removeAttribute('aria-busy');
  }

  /** Aviso flutuante que se apaga sozinho. */
  function avisar(mensagem, duracao) {
    var pilha = document.getElementById('pilha-avisos');
    if (!pilha) return;

    var caixa = document.createElement('div');
    caixa.className = 'aviso';
    caixa.textContent = mensagem;
    pilha.appendChild(caixa);

    // Um quadro de intervalo para a transição partir do estado inicial.
    requestAnimationFrame(function () { caixa.classList.add('visivel'); });

    window.setTimeout(function () {
      caixa.classList.remove('visivel');
      // Só remove do DOM depois da transição de saída.
      window.setTimeout(function () { caixa.remove(); }, 450);
    }, duracao || 3000);
  }

  /** Registra na tela qual modelo produziu o texto e o custo em tokens. */
  function informarModelo(container, corpo) {
    if (!container) return;
    var alvo = container.querySelector('[data-info-modelo]');
    if (!alvo || !corpo) return;

    var partes = ['Composto por ' + (corpo.modelo || 'Gemini')];
    var tokens = corpo.uso && corpo.uso.totalTokenCount;
    if (tokens) partes.push(tokens.toLocaleString('pt-BR') + ' tokens');

    alvo.textContent = partes.join(' · ');
    mostrar(alvo);
  }

  /** Explica a demora extra quando a primeira composição é bloqueada. */
  function avisarNovaTentativa(carga) {
    if (!carga) return;
    var nota = carga.querySelector('[data-nota-carga]');
    if (nota) nota.textContent = 'A primeira composição foi bloqueada por recitação. Refazendo com mais variação…';
  }

  /** Mostra a falha onde o usuário está olhando, além do console. */
  function relatarErro(vazio, mensagem) {
    if (!vazio) return;
    var m = vazio.querySelector('[data-erro]');
    if (!m) return;
    m.textContent = mensagem;
    mostrar(m);
  }

  /* ---------------------------------------------------------------------
     Gerador de Peças
     --------------------------------------------------------------------- */

  function ligarGerador() {
    var botao = $('#gerar-peca');
    if (!botao) return;

    var vazio  = $('#peca-vazia');
    var carga  = $('#peca-carregando');
    var folha  = $('#peca-folha');
    var acoes  = $('#peca-acoes');
    var corpo  = $('#peca-corpo');

    botao.addEventListener('click', function () {
      var entrada = {
        tipoPeca: valor('#campo-tipo'),
        autor:    valor('#campo-autor'),
        reu:      valor('#campo-reu'),
        fatos:    valor('#campo-fatos'),
        pedidos:  valor('#campo-pedidos')
      };

      var payload = {
        tarefa: 'peca',
        prompt: montarPromptPeca(entrada),
        tipoPeca: entrada.tipoPeca,
        autor: entrada.autor,
        reu: entrada.reu,
        fatos: entrada.fatos,
        pedidos: entrada.pedidos
      };

      // Deixa o payload inspecionável no console durante os testes.
      console.groupCollapsed('[Peticiona] Payload → ' + ENDPOINT);
      console.log('tarefa:', payload.tarefa);
      console.log('campos capturados:', entrada);
      console.log('prompt (' + payload.prompt.length + ' caracteres):\n' + payload.prompt);
      console.groupEnd();

      ocultar(vazio); ocultar(folha); ocultar(acoes);
      mostrar(carga);
      ocupar(botao, 'Gerando Peça…');

      chamarGemini(payload, function () { avisarNovaTentativa(carga); })
        .then(function (resposta) {
          ocultar(carga);

          if (corpo) {
            corpo.innerHTML = global.PeticionaPDF.markdownParaHTML(resposta.texto);
          }
          mostrar(folha); mostrar(acoes);
          global.PeticionaPDF.ajustarPrevia();
          informarModelo(acoes, resposta);

          var peca = Dados.salvarPeca({
            titulo:         resumirTitulo(entrada.fatos) || entrada.tipoPeca,
            tipoPeca:       entrada.tipoPeca,
            cliente:        primeiroNome(entrada.autor),
            status:         'Gerada',
            conteudoGerado: resposta.texto
          });

          folha.setAttribute('data-peca-id', peca.id);
          folha.setAttribute('data-peca-cliente', peca.cliente);
          folha.setAttribute('data-peca-tipo', peca.tipoPeca);

          console.groupCollapsed('[Peticiona] Gravado em localStorage.peticiona_pecas');
          console.log(peca);
          console.groupEnd();

          avisar('Documento gerado com sucesso! Pronto para leitura e download.');
          folha.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        })
        .catch(function (erro) {
          ocultar(carga);
          mostrar(vazio);
          relatarErro(vazio, 'Não foi possível gerar a peça: ' + erro.message);
          console.error('[Peticiona] Falha na geração:', erro);
        })
        .then(function () { liberar(botao); });
    });
  }

  /* ---------------------------------------------------------------------
     Analisador de Contratos
     --------------------------------------------------------------------- */

  function ligarAnalisador() {
    var botao = $('#analisar-contrato');
    if (!botao) return;

    var vazio = $('#analise-vazia');
    var carga = $('#analise-carregando');
    var saida = $('#analise-resultado');
    var corpo = $('#analise-corpo');

    botao.addEventListener('click', function () {
      var texto = valor('#campo-contrato');
      var entrada = {
        contrato: primeiraLinha(texto) || 'Contrato submetido',
        texto: texto
      };

      var payload = {
        tarefa: 'auditoria',
        prompt: montarPromptAuditoria(entrada),
        contrato: entrada.contrato,
        texto: entrada.texto
      };

      console.groupCollapsed('[Peticiona] Payload → ' + ENDPOINT);
      console.log('tarefa:', payload.tarefa);
      console.log('campos capturados:', { contrato: entrada.contrato, caracteres: texto.length });
      console.log('prompt (' + payload.prompt.length + ' caracteres):\n' + payload.prompt);
      console.groupEnd();

      ocultar(vazio); ocultar(saida);
      mostrar(carga);
      ocupar(botao, 'Analisando com IA…');

      chamarGemini(payload, function () { avisarNovaTentativa(carga); })
        .then(function (resposta) {
          ocultar(carga);
          if (corpo) corpo.innerHTML = global.PeticionaPDF.markdownParaHTML(resposta.texto);
          mostrar(saida);
          global.PeticionaPDF.ajustarPrevia();
          informarModelo(saida, resposta);

          var auditoria = Dados.salvarAuditoria({
            contrato:          entrada.contrato,
            cliente:           primeiroNome(entrada.contrato),
            riscosEncontrados: contarRiscos(resposta.texto),
            relatorio:         resposta.texto
          });

          saida.setAttribute('data-auditoria-id', auditoria.id);
          saida.setAttribute('data-auditoria-cliente', auditoria.cliente);

          console.groupCollapsed('[Peticiona] Gravado em localStorage.peticiona_auditorias');
          console.log(auditoria);
          console.groupEnd();

          avisar('Documento gerado com sucesso! Pronto para leitura e download.');
        })
        .catch(function (erro) {
          ocultar(carga);
          mostrar(vazio);
          relatarErro(vazio, 'Não foi possível auditar o contrato: ' + erro.message);
          console.error('[Peticiona] Falha na auditoria:', erro);
        })
        .then(function () { liberar(botao); });
    });
  }

  /* ---------------------------------------------------------------------
     Resumo para o cliente

     Segunda chamada ao Gemini, sobre o documento já produzido: traduz a peça
     ou o parecer para uma linguagem que o cliente entenda.
     --------------------------------------------------------------------- */

  var PROMPT_RESUMO = 'Você é um assistente jurídico humanizado. Leia este documento/parecer ' +
    'técnico e crie um resumo curto, claro, sem "juridiquês" e em linguagem acessível para ser ' +
    'enviado diretamente para o cliente final via WhatsApp. Use tópicos de forma profissional.';

  function montarPromptResumo(documento) {
    return PROMPT_RESUMO + '\n\nDOCUMENTO:\n' + documento;
  }

  /** Recupera do localStorage o texto original do documento em tela. */
  function documentoAtual(origemSel) {
    var origem = $(origemSel);
    if (!origem || !Dados) return null;

    var idPeca = origem.getAttribute('data-peca-id');
    if (idPeca) {
      var peca = Dados.listarPecas().find(function (p) { return p.id === idPeca; });
      if (peca) return { texto: peca.conteudoGerado, cliente: peca.cliente };
    }

    var idAudit = origem.getAttribute('data-auditoria-id');
    if (idAudit) {
      var a = Dados.listarAuditorias().find(function (x) { return x.id === idAudit; });
      if (a) return { texto: a.relatorio, cliente: a.cliente };
    }
    return null;
  }

  function ligarResumo() {
    var modal = $('#modal-resumo');
    if (!modal) return;

    var carga     = $('#resumo-carregando');
    var conteudo  = $('#resumo-conteudo');
    var falha     = $('#resumo-erro');
    var alvoTexto = modal.querySelector('[data-resumo-texto]');
    var alvoErro  = modal.querySelector('[data-resumo-erro]');
    var copiar    = $('#copiar-resumo');

    function abrir() {
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      document.body.style.overflow = 'hidden';
    }

    function fechar() {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      document.body.style.overflow = '';
    }

    $$('[data-fechar-resumo]', modal).forEach(function (el) {
      el.addEventListener('click', fechar);
    });
    document.addEventListener('keydown', function (ev) {
      if (ev.key === 'Escape' && !modal.classList.contains('hidden')) fechar();
    });

    $$('[data-gerar-resumo]').forEach(function (botao) {
      botao.addEventListener('click', function () {
        var doc = documentoAtual(botao.getAttribute('data-origem') || '#peca-folha');
        if (!doc) {
          avisar('Gere o documento antes de pedir o resumo.');
          return;
        }

        abrir();
        mostrar(carga); ocultar(conteudo); ocultar(falha);
        if (copiar) copiar.disabled = true;
        ocupar(botao, 'Resumindo…');

        requisicao({
          tarefa: 'resumo',
          prompt: montarPromptResumo(doc.texto),
          temperatura: 0.5
        })
          .then(function (resposta) {
            ocultar(carga);
            if (alvoTexto) alvoTexto.textContent = resposta.texto;
            mostrar(conteudo);
            if (copiar) copiar.disabled = false;
            avisar('Resumo pronto para envio ao cliente.');
          })
          .catch(function (erro) {
            ocultar(carga);
            if (alvoErro) alvoErro.textContent = 'Não foi possível gerar o resumo: ' + erro.message;
            mostrar(falha);
            console.error('[Peticiona] Falha no resumo:', erro);
          })
          .then(function () { liberar(botao); });
      });
    });

    if (copiar) {
      copiar.addEventListener('click', function () {
        var texto = alvoTexto ? alvoTexto.textContent : '';
        if (!texto) return;

        copiarTexto(texto)
          .then(function () { avisar('Resumo copiado para a área de transferência.'); })
          .catch(function () { avisar('Não foi possível copiar. Selecione o texto manualmente.'); });
      });
    }
  }

  /**
   * A Clipboard API exige contexto seguro e permissão; quando indisponível,
   * o caminho antigo com textarea + execCommand ainda funciona.
   */
  function copiarTexto(texto) {
    if (navigator.clipboard && window.isSecureContext) {
      return navigator.clipboard.writeText(texto);
    }
    return new Promise(function (resolver, rejeitar) {
      var area = document.createElement('textarea');
      area.value = texto;
      area.setAttribute('readonly', '');
      area.style.position = 'fixed';
      area.style.left = '-9999px';
      document.body.appendChild(area);
      area.select();
      var ok = false;
      try { ok = document.execCommand('copy'); } catch (e) { ok = false; }
      area.remove();
      ok ? resolver() : rejeitar(new Error('execCommand falhou'));
    });
  }

  /* ---------------------------------------------------------------------
     Auxiliares de extração
     --------------------------------------------------------------------- */

  function primeiraLinha(t) {
    return (t || '').split('\n').map(function (l) { return l.trim(); })
                    .filter(Boolean)[0] || '';
  }

  /** Primeira parte de uma qualificação costuma ser o nome da parte. */
  function primeiroNome(qualificacao) {
    var t = (qualificacao || '').split(/[,\n]/)[0].trim();
    return t || 'Cliente não identificado';
  }

  function resumirTitulo(fatos) {
    var t = (fatos || '').replace(/\s+/g, ' ').trim();
    if (!t) return '';
    return t.length > 60 ? t.slice(0, 57) + '…' : t;
  }

  /** Conta os parágrafos de risco do parecer — cada cláusula apontada. */
  function contarRiscos(texto) {
    var m = (texto || '').match(/\*\*Cláusula[^*]*\*\*/g);
    return m ? m.length : 0;
  }

  /* ---------------------------------------------------------------------
     Inicialização
     --------------------------------------------------------------------- */

  function iniciar() {
    Dados = global.PeticionaDados;
    if (!Dados) { console.error('[Peticiona] dados.js precisa carregar antes de gemini.js'); return; }
    ligarGerador();
    ligarAnalisador();
    ligarResumo();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciar);
  } else {
    iniciar();
  }

  global.PeticionaGemini = {
    ENDPOINT: ENDPOINT,
    montarPromptPeca: montarPromptPeca,
    montarPromptAuditoria: montarPromptAuditoria,
    chamarGemini: chamarGemini,
    contarRiscos: contarRiscos,
    montarPromptResumo: montarPromptResumo,
    avisar: avisar
  };
})(window);
