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

  function chamarGemini(payload) {
    return fetch(ENDPOINT, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    }).then(function (resposta) {
      return resposta.json().then(function (corpo) {
        if (!resposta.ok) {
          throw new Error(corpo.erro || ('HTTP ' + resposta.status));
        }
        return corpo;
      });
    });
  }

  /* ---------------------------------------------------------------------
     Estado das telas
     --------------------------------------------------------------------- */

  function mostrar(el) { if (el) el.classList.remove('hidden'); }
  function ocultar(el) { if (el) el.classList.add('hidden'); }

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
      botao.disabled = true;

      chamarGemini(payload)
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

          folha.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        })
        .catch(function (erro) {
          ocultar(carga);
          mostrar(vazio);
          relatarErro(vazio, 'Não foi possível gerar a peça: ' + erro.message);
          console.error('[Peticiona] Falha na geração:', erro);
        })
        .then(function () { botao.disabled = false; });
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
      botao.disabled = true;

      chamarGemini(payload)
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
        })
        .catch(function (erro) {
          ocultar(carga);
          mostrar(vazio);
          relatarErro(vazio, 'Não foi possível auditar o contrato: ' + erro.message);
          console.error('[Peticiona] Falha na auditoria:', erro);
        })
        .then(function () { botao.disabled = false; });
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
    contarRiscos: contarRiscos
  };
})(window);
