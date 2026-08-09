/* ==========================================================================
   Peticiona AI — Camada de dados (localStorage + JSON)

   Módulo único, exposto em window.PeticionaDados. Responsável por:
     · ler e escrever os três acervos em JSON
     · derivar as métricas do painel a partir deles
     · redesenhar Dashboard e Meus Clientes sem recarregar a página

   Toda escrita emite o evento 'peticiona:dados' no document, e as telas
   apenas escutam. Assim a origem da mudança (formulário, importação, outra
   aba do navegador) deixa de importar para quem renderiza.
   ========================================================================== */

(function (global) {
  'use strict';

  var CHAVES = {
    pecas:       'peticiona_pecas',
    auditorias:  'peticiona_auditorias',
    clientes:    'peticiona_clientes',
    exportacoes: 'peticiona_exportacoes'
  };

  /* ---------------------------------------------------------------------
     Acesso bruto ao localStorage
     --------------------------------------------------------------------- */

  function disponivel() {
    try {
      var t = '__peticiona_teste__';
      global.localStorage.setItem(t, '1');
      global.localStorage.removeItem(t);
      return true;
    } catch (e) {
      // Modo privado, cota esgotada ou storage bloqueado por política.
      return false;
    }
  }

  var TEM_STORAGE = disponivel();

  // Espelho em memória: sem localStorage o sistema continua funcionando
  // dentro da sessão, apenas sem persistir entre recarregamentos.
  var memoria = {};

  function ler(chave) {
    var bruto;
    if (TEM_STORAGE) {
      bruto = global.localStorage.getItem(chave);
    } else {
      bruto = memoria[chave] || null;
    }
    if (!bruto) return [];
    try {
      var dados = JSON.parse(bruto);
      return Array.isArray(dados) ? dados : [];
    } catch (e) {
      // JSON corrompido não pode derrubar a tela: recomeça o acervo.
      console.warn('[Peticiona] JSON inválido em ' + chave + ', reiniciando acervo.');
      return [];
    }
  }

  function escrever(chave, dados) {
    var serializado = JSON.stringify(dados);
    if (TEM_STORAGE) {
      try {
        global.localStorage.setItem(chave, serializado);
      } catch (e) {
        console.warn('[Peticiona] Falha ao gravar ' + chave + ': ' + e.message);
        memoria[chave] = serializado;
      }
    } else {
      memoria[chave] = serializado;
    }
    notificar(chave);
    return dados;
  }

  function notificar(chave) {
    document.dispatchEvent(new CustomEvent('peticiona:dados', { detail: { chave: chave } }));
  }

  /* ---------------------------------------------------------------------
     Utilidades
     --------------------------------------------------------------------- */

  function novoId(prefixo) {
    return prefixo + '_' + Date.now().toString(36) + '_' +
           Math.random().toString(36).slice(2, 8);
  }

  function agoraISO() {
    return new Date().toISOString();
  }

  function formatarData(iso) {
    var d = new Date(iso);
    if (isNaN(d.getTime())) return '—';
    var hoje = new Date();
    var mesmoDia = d.toDateString() === hoje.toDateString();
    var ontem = new Date(hoje.getTime() - 86400000).toDateString() === d.toDateString();
    var hora = String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
    if (mesmoDia) return 'Hoje, ' + hora;
    if (ontem) return 'Ontem, ' + hora;
    return d.toLocaleDateString('pt-BR') + ', ' + hora;
  }

  function texto(v) {
    return (v === undefined || v === null || String(v).trim() === '') ? '' : String(v).trim();
  }

  /* ---------------------------------------------------------------------
     Esquemas — normalizam a entrada para que o JSON gravado tenha sempre
     o mesmo formato, independentemente de quem chamou.
     --------------------------------------------------------------------- */

  function esquemaPeca(dados) {
    dados = dados || {};
    return {
      id:             texto(dados.id) || novoId('peca'),
      titulo:         texto(dados.titulo) || 'Peça sem título',
      tipoPeca:       texto(dados.tipoPeca) || 'Petição Inicial',
      cliente:        texto(dados.cliente) || 'Cliente não identificado',
      data:           texto(dados.data) || agoraISO(),
      status:         texto(dados.status) || 'Gerada',
      conteudoGerado: texto(dados.conteudoGerado)
    };
  }

  function esquemaAuditoria(dados) {
    dados = dados || {};
    return {
      id:                texto(dados.id) || novoId('audit'),
      contrato:          texto(dados.contrato) || 'Contrato sem identificação',
      cliente:           texto(dados.cliente) || 'Cliente não identificado',
      data:              texto(dados.data) || agoraISO(),
      riscosEncontrados: Number.isFinite(Number(dados.riscosEncontrados)) ? Number(dados.riscosEncontrados) : 0,
      relatorio:         texto(dados.relatorio)
    };
  }

  function esquemaCliente(dados) {
    dados = dados || {};
    return {
      id:        texto(dados.id) || novoId('cli'),
      nome:      texto(dados.nome) || 'Cliente sem nome',
      documento: texto(dados.documento),
      processos: Array.isArray(dados.processos) ? dados.processos.map(texto).filter(Boolean)
                                                : (texto(dados.processos) ? [texto(dados.processos)] : []),
      area:      texto(dados.area) || 'Não classificada'
    };
  }

  /* ---------------------------------------------------------------------
     API pública de escrita e leitura
     --------------------------------------------------------------------- */

  function salvarPeca(dados) {
    var peca = esquemaPeca(dados);
    var acervo = ler(CHAVES.pecas);
    var i = acervo.findIndex(function (p) { return p.id === peca.id; });
    if (i >= 0) { acervo[i] = peca; } else { acervo.unshift(peca); }
    escrever(CHAVES.pecas, acervo);

    // Uma peça sempre implica um cliente na carteira.
    if (peca.cliente && peca.cliente !== 'Cliente não identificado') {
      salvarCliente({ nome: peca.cliente, area: peca.tipoPeca });
    }
    return peca;
  }

  function salvarAuditoria(dados) {
    var auditoria = esquemaAuditoria(dados);
    var acervo = ler(CHAVES.auditorias);
    var i = acervo.findIndex(function (a) { return a.id === auditoria.id; });
    if (i >= 0) { acervo[i] = auditoria; } else { acervo.unshift(auditoria); }
    escrever(CHAVES.auditorias, acervo);

    if (auditoria.cliente && auditoria.cliente !== 'Cliente não identificado') {
      salvarCliente({ nome: auditoria.cliente, area: 'Contratual' });
    }
    return auditoria;
  }

  function salvarCliente(dados) {
    var cliente = esquemaCliente(dados);
    var acervo = ler(CHAVES.clientes);

    // Deduplica por nome normalizado: o mesmo cliente não entra duas vezes
    // só porque foi digitado com acento, caixa ou espaço diferente.
    var chaveNome = cliente.nome.toLowerCase().replace(/\s+/g, ' ').trim();
    var existente = acervo.find(function (c) {
      return c.nome.toLowerCase().replace(/\s+/g, ' ').trim() === chaveNome;
    });

    if (existente) {
      if (cliente.documento) existente.documento = cliente.documento;
      cliente.processos.forEach(function (p) {
        if (existente.processos.indexOf(p) === -1) existente.processos.push(p);
      });
      escrever(CHAVES.clientes, acervo);
      return existente;
    }

    acervo.unshift(cliente);
    escrever(CHAVES.clientes, acervo);
    return cliente;
  }

  function registrarExportacao(dados) {
    var acervo = ler(CHAVES.exportacoes);
    acervo.unshift({
      id:       novoId('exp'),
      formato:  texto((dados || {}).formato) || 'PDF',
      arquivo:  texto((dados || {}).arquivo),
      refId:    texto((dados || {}).refId),
      cliente:  texto((dados || {}).cliente),
      data:     agoraISO()
    });
    escrever(CHAVES.exportacoes, acervo);
    return acervo[0];
  }

  function listarPecas()       { return ler(CHAVES.pecas); }
  function listarAuditorias()  { return ler(CHAVES.auditorias); }
  function listarClientes()    { return ler(CHAVES.clientes); }
  function listarExportacoes() { return ler(CHAVES.exportacoes); }

  function limparTudo() {
    Object.keys(CHAVES).forEach(function (k) { escrever(CHAVES[k], []); });
  }

  /* ---------------------------------------------------------------------
     Métricas derivadas
     --------------------------------------------------------------------- */

  // Economia estimada por artefato, em horas. São os números que sustentam o
  // indicador "Horas Economizadas": redigir uma peça à mão custa ~1h30, e
  // auditar um contrato ~2h.
  var HORAS_POR_PECA = 1.5;
  var HORAS_POR_AUDITORIA = 2;

  function metricas() {
    var pecas = listarPecas();
    var auditorias = listarAuditorias();
    var clientes = listarClientes();

    var agora = new Date();
    var doMes = pecas.filter(function (p) {
      var d = new Date(p.data);
      return !isNaN(d.getTime()) &&
             d.getMonth() === agora.getMonth() &&
             d.getFullYear() === agora.getFullYear();
    });

    var horas = pecas.length * HORAS_POR_PECA + auditorias.length * HORAS_POR_AUDITORIA;

    return {
      pecasNoMes:   doMes.length,
      pecasTotal:   pecas.length,
      auditorias:   auditorias.length,
      horas:        Math.round(horas),
      clientes:     clientes.length,
      processos:    clientes.reduce(function (t, c) { return t + (c.processos ? c.processos.length : 0); }, 0),
      riscos:       auditorias.reduce(function (t, a) { return t + (a.riscosEncontrados || 0); }, 0)
    };
  }

  /** Percentual de peças por área, ordenado como o painel exibe. */
  function distribuicaoPorArea(areasFixas) {
    var pecas = listarPecas();
    var contagem = {};
    pecas.forEach(function (p) {
      var a = classificarArea(p.tipoPeca);
      contagem[a] = (contagem[a] || 0) + 1;
    });
    var total = pecas.length;
    return (areasFixas || []).map(function (area) {
      var n = contagem[area] || 0;
      return { area: area, percentual: total ? Math.round((n / total) * 100) : 0, quantidade: n };
    });
  }

  /** Heurística simples de área a partir do tipo de peça. */
  function classificarArea(tipoPeca) {
    var t = (tipoPeca || '').toLowerCase();
    if (t.indexOf('trabalh') >= 0 || t.indexOf('reclamat') >= 0) return 'Trabalhista';
    if (t.indexOf('acusa') >= 0 || t.indexOf('penal') >= 0 || t.indexOf('habeas') >= 0) return 'Penal';
    if (t.indexOf('consumidor') >= 0) return 'Consumidor';
    if (t.indexOf('família') >= 0 || t.indexOf('familia') >= 0 || t.indexOf('aliment') >= 0 ||
        t.indexOf('divórcio') >= 0 || t.indexOf('divorcio') >= 0 || t.indexOf('inventário') >= 0) return 'Família & Sucessões';
    return 'Cível & Processo Civil';
  }

  /** Linha do tempo unificada de peças, auditorias e exportações. */
  function atividades(limite) {
    var itens = [];

    listarPecas().forEach(function (p) {
      itens.push({
        data: p.data,
        titulo: p.tipoPeca + ' — ' + p.titulo,
        cliente: p.cliente,
        estado: p.status
      });
    });

    listarAuditorias().forEach(function (a) {
      itens.push({
        data: a.data,
        titulo: 'Auditoria — ' + a.contrato,
        cliente: a.cliente,
        estado: a.riscosEncontrados + (a.riscosEncontrados === 1 ? ' risco crítico' : ' riscos críticos')
      });
    });

    listarExportacoes().forEach(function (x) {
      itens.push({
        data: x.data,
        titulo: 'Exportação — ' + (x.arquivo || x.formato),
        cliente: x.cliente || '—',
        estado: x.formato
      });
    });

    itens.sort(function (a, b) { return new Date(b.data) - new Date(a.data); });
    return limite ? itens.slice(0, limite) : itens;
  }

  /* ---------------------------------------------------------------------
     Renderização — Dashboard
     --------------------------------------------------------------------- */

  function definirTexto(seletor, valor, contexto) {
    var el = (contexto || document).querySelector(seletor);
    if (el) el.textContent = valor;
  }

  function atualizarDashboard() {
    if (!document.querySelector('[data-metrica]')) return; // não estamos no painel

    var m = metricas();

    var cartoes = [
      { chave: 'pecas',     valor: m.pecasNoMes, vazio: 'Nenhuma peça gerada ainda',
        nota: m.pecasNoMes + (m.pecasNoMes === 1 ? ' peça neste mês' : ' peças neste mês'), teto: 20 },
      { chave: 'contratos', valor: m.auditorias, vazio: 'Aguardando primeira análise',
        nota: m.riscos + (m.riscos === 1 ? ' risco apontado' : ' riscos apontados'), teto: 15 },
      { chave: 'horas',     valor: m.horas, vazio: 'Inicie o uso para contabilizar',
        nota: 'equivalente a ' + (m.horas / 8).toFixed(1).replace('.', ',') + ' dias de trabalho', teto: 80 },
      { chave: 'clientes',  valor: m.clientes, vazio: 'Nenhum cliente cadastrado',
        nota: m.processos + (m.processos === 1 ? ' processo vinculado' : ' processos vinculados'), teto: 25 }
    ];

    cartoes.forEach(function (c) {
      definirTexto('[data-metrica="' + c.chave + '"]', String(c.valor));
      definirTexto('[data-metrica-nota="' + c.chave + '"]', c.valor === 0 ? c.vazio : c.nota);
      var barra = document.querySelector('[data-metrica-barra="' + c.chave + '"]');
      if (barra) barra.style.width = Math.min(100, Math.round((c.valor / c.teto) * 100)) + '%';
    });

    renderizarAtividades();
    renderizarDistribuicao();
  }

  function renderizarAtividades() {
    var lista  = document.querySelector('[data-lista="atividades"]');
    var vazio  = document.querySelector('[data-vazio="atividades"]');
    var rodape = document.querySelector('[data-rodape="atividades"]');
    if (!lista || !vazio) return;

    var itens = atividades(6);

    if (itens.length === 0) {
      lista.innerHTML = '';
      lista.classList.add('hidden');
      vazio.classList.remove('hidden');
      if (rodape) rodape.textContent = 'Gerar a primeira peça';
      return;
    }

    vazio.classList.add('hidden');
    lista.classList.remove('hidden');
    if (rodape) rodape.textContent = 'Ver histórico completo';

    lista.innerHTML = itens.map(function (a, i) {
      return '<li class="' + (i > 0 ? 'border-t border-gold/[0.07] ' : '') +
             'px-6 py-5 transition-colors duration-500 hover:bg-gold/[0.025] sm:px-7">' +
               '<div class="flex flex-col gap-1.5 sm:flex-row sm:items-baseline sm:justify-between sm:gap-6">' +
                 '<div class="min-w-0">' +
                   '<p class="text-[14.5px] leading-snug text-silk">' + escapar(a.titulo) + '</p>' +
                   '<p class="mt-1.5 text-[12.5px] text-silver">' + escapar(a.cliente) + '</p>' +
                 '</div>' +
                 '<div class="shrink-0 sm:text-right">' +
                   '<p class="text-[11.5px] text-silver/70">' + escapar(formatarData(a.data)) + '</p>' +
                   '<p class="mt-1.5 text-[11.5px] text-gold/80">' + escapar(a.estado) + '</p>' +
                 '</div>' +
               '</div>' +
             '</li>';
    }).join('');
  }

  function renderizarDistribuicao() {
    var linhas = Array.prototype.slice.call(document.querySelectorAll('[data-area]'));
    if (!linhas.length) return;

    var areas = linhas.map(function (l) { return l.getAttribute('data-area'); });
    var dist = distribuicaoPorArea(areas);

    dist.forEach(function (d, i) {
      var linha = linhas[i];
      var pct = linha.querySelector('[data-area-percentual]');
      var barra = linha.querySelector('[data-area-barra]');
      if (pct) pct.textContent = d.percentual + '%';
      if (barra) barra.style.width = d.percentual + '%';
    });

    var nota = document.querySelector('[data-distribuicao-nota]');
    if (nota) {
      var total = listarPecas().length;
      nota.textContent = total === 0
        ? 'Aguardando primeiras produções.'
        : 'Percentuais sobre ' + total + (total === 1 ? ' peça produzida.' : ' peças produzidas.');
    }
  }

  /* ---------------------------------------------------------------------
     Renderização — Meus Clientes
     --------------------------------------------------------------------- */

  function atualizarClientes() {
    var corpo = document.querySelector('[data-lista="clientes-tabela"]');
    if (!corpo) return; // não estamos na tela de clientes

    var m = metricas();
    definirTexto('[data-resumo="clientes"]', String(m.clientes));
    definirTexto('[data-resumo="processos"]', String(m.processos));
    definirTexto('[data-resumo="pecas"]', String(m.pecasTotal));
    definirTexto('[data-resumo="prazos"]', '0');

    var clientes = listarClientes();
    var pecas = listarPecas();
    var cartoes = document.querySelector('[data-lista="clientes-cartoes"]');
    var vazio = document.querySelector('[data-vazio="clientes"]');
    var preenchido = document.querySelector('[data-preenchido="clientes"]');
    var contador = document.getElementById('contador-clientes');

    if (contador) {
      contador.textContent = clientes.length + (clientes.length === 1 ? ' registro' : ' registros');
    }

    if (clientes.length === 0) {
      corpo.innerHTML = '';
      if (cartoes) cartoes.innerHTML = '';
      if (vazio) vazio.classList.remove('hidden');
      if (preenchido) preenchido.classList.add('hidden');
      return;
    }

    if (vazio) vazio.classList.add('hidden');
    if (preenchido) preenchido.classList.remove('hidden');

    var enriquecidos = clientes.map(function (c) {
      var doCliente = pecas.filter(function (p) { return p.cliente === c.nome; });
      var ultima = doCliente[0];
      return {
        nome: c.nome,
        documento: c.documento || 'Documento não informado',
        processo: (c.processos && c.processos[0]) || 'Sem processo vinculado',
        area: c.area,
        situacao: ultima ? ultima.status : 'Cadastrado',
        pecas: doCliente.length,
        ultima: ultima ? (ultima.tipoPeca + ' · ' + formatarData(ultima.data)) : 'Nenhuma peça ainda'
      };
    });

    corpo.innerHTML = enriquecidos.map(function (c) {
      var chave = escapar([c.nome, c.documento, c.processo, c.area, c.situacao].join(' '));
      return '<tr data-cliente="' + chave + '">' +
        '<td class="px-7 py-5">' +
          '<p class="whitespace-nowrap text-[14px] text-silk">' + escapar(c.nome) + '</p>' +
          '<p class="mt-1 text-[11.5px] text-silver">' + escapar(c.documento) + '</p>' +
        '</td>' +
        '<td class="px-4 py-5"><p class="whitespace-nowrap text-[13px] tabular-nums text-silver">' + escapar(c.processo) + '</p></td>' +
        '<td class="px-4 py-5"><span class="whitespace-nowrap rounded-sm border border-sapphire/30 px-2.5 py-1 text-[10px] uppercase tracking-[0.16em] text-sapphire">' + escapar(c.area) + '</span></td>' +
        '<td class="px-4 py-5"><p class="whitespace-nowrap text-[13px] text-gold/[0.85]">' + escapar(c.situacao) + '</p></td>' +
        '<td class="px-4 py-5 text-right"><p class="ordinal text-[15px] text-silk">' + c.pecas + '</p></td>' +
        '<td class="px-7 py-5 text-right"><p class="whitespace-nowrap text-[12.5px] text-silver">' + escapar(c.ultima) + '</p></td>' +
      '</tr>';
    }).join('');

    if (cartoes) {
      cartoes.innerHTML = enriquecidos.map(function (c, i) {
        var chave = escapar([c.nome, c.documento, c.processo, c.area, c.situacao].join(' '));
        return '<article data-cliente="' + chave + '" class="px-6 py-5 sm:px-7 ' + (i > 0 ? 'border-t border-gold/[0.07]' : '') + '">' +
          '<div class="flex flex-wrap items-start justify-between gap-3">' +
            '<div class="min-w-0">' +
              '<p class="text-[15px] text-silk">' + escapar(c.nome) + '</p>' +
              '<p class="mt-1 text-[11.5px] text-silver">' + escapar(c.documento) + '</p>' +
            '</div>' +
            '<span class="shrink-0 rounded-sm border border-sapphire/30 px-2.5 py-1 text-[10px] uppercase tracking-[0.16em] text-sapphire">' + escapar(c.area) + '</span>' +
          '</div>' +
          '<dl class="mt-4 space-y-2.5">' +
            linhaDefinicao('Processo', c.processo) +
            linhaDefinicao('Situação', c.situacao) +
            linhaDefinicao('Peças salvas', String(c.pecas)) +
          '</dl>' +
          '<p class="mt-4 border-t border-gold/[0.07] pt-3 text-[12px] text-silver/70">Última movimentação: ' + escapar(c.ultima) + '</p>' +
        '</article>';
      }).join('');
    }
  }

  function linhaDefinicao(rotulo, valor) {
    return '<div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">' +
             '<dt class="text-[11px] uppercase tracking-[0.16em] text-silver/70">' + escapar(rotulo) + '</dt>' +
             '<dd class="text-[12.5px] text-silver">' + escapar(valor) + '</dd>' +
           '</div>';
  }

  function escapar(v) {
    return String(v === undefined || v === null ? '' : v)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  /* ---------------------------------------------------------------------
     Ligação com a página
     --------------------------------------------------------------------- */

  function sincronizar() {
    atualizarDashboard();
    atualizarClientes();
  }

  document.addEventListener('peticiona:dados', sincronizar);

  // Mudanças feitas em outra aba do navegador refletem aqui.
  global.addEventListener('storage', function (ev) {
    if (ev.key && ev.key.indexOf('peticiona_') === 0) sincronizar();
  });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', sincronizar);
  } else {
    sincronizar();
  }

  global.PeticionaDados = {
    CHAVES: CHAVES,
    temPersistencia: TEM_STORAGE,
    salvarPeca: salvarPeca,
    salvarAuditoria: salvarAuditoria,
    salvarCliente: salvarCliente,
    registrarExportacao: registrarExportacao,
    listarPecas: listarPecas,
    listarAuditorias: listarAuditorias,
    listarClientes: listarClientes,
    listarExportacoes: listarExportacoes,
    metricas: metricas,
    distribuicaoPorArea: distribuicaoPorArea,
    atividades: atividades,
    atualizarDashboard: atualizarDashboard,
    atualizarClientes: atualizarClientes,
    sincronizar: sincronizar,
    limparTudo: limparTudo,
    formatarData: formatarData,
    novoId: novoId
  };
})(window);
