<?php
/**
 * Peticiona AI — Configuração global do protótipo visual.
 *
 * Protótipo de interface: nenhuma chamada de backend, banco de dados ou API.
 * Todo o conteúdo abaixo é estático e serve apenas para alimentar as views.
 */

declare(strict_types=1);

const APP_NAME    = 'Peticiona AI';
const APP_TAGLINE = 'Inteligência e Gestão Jurídica';

/** Caminho base para assets (ajuste caso o projeto rode em subdiretório). */
const BASE_URL = '';

/** Escapa texto para saída em HTML. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Monta uma URL de asset a partir da raiz do projeto. */
function asset(string $path): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/** Marca o item ativo do menu do painel. */
function is_current(string $file): bool
{
    return basename($_SERVER['SCRIPT_NAME'] ?? '') === $file;
}

/** Navegação pública — âncoras da landing page (single-page scroll). */
function nav_publico(): array
{
    return [
        ['label' => 'Portal Juris',     'anchor' => '#portal-juris'],
        ['label' => 'Soluções Legais',  'anchor' => '#solucoes-legais'],
        ['label' => 'Áreas do Direito', 'anchor' => '#areas-direito'],
        ['label' => 'Acervo & Modelos', 'anchor' => '#acervo-modelos'],
        ['label' => 'Ecossistema',      'anchor' => '#ecossistema'],
    ];
}

/** Navegação do painel logado. */
function nav_painel(): array
{
    return [
        ['label' => 'Visão Geral',            'file' => 'dashboard.php',                'hint' => 'Indicadores do escritório'],
        ['label' => 'Gerador de Peças',       'file' => 'gerador-de-pecas.php',         'hint' => 'Minutas processuais'],
        ['label' => 'Analisador de Contratos','file' => 'analisador-de-contratos.php',  'hint' => 'Auditoria de cláusulas'],
        ['label' => 'Meus Clientes',          'file' => 'meus-clientes.php',            'hint' => 'Carteira e processos'],
    ];
}

/** Seção 1 — barra de 5 destaques do Hero. */
function hero_destaques(): array
{
    return [
        ['titulo' => 'Redação em Segundos',    'texto' => 'Petições impecáveis geradas em segundos com base em IA treinada na legislação brasileira.'],
        ['titulo' => 'Análise de Contratos',   'texto' => 'Identifique cláusulas de risco, sugira melhorias e garanta segurança jurídica.'],
        ['titulo' => 'Exportação Inteligente', 'texto' => 'Documentos em .docx prontos para protocolo, com formatação profissional e automática.'],
        ['titulo' => 'Resumo para WhatsApp',   'texto' => 'Gere resumos automáticos e envie para o WhatsApp do seu cliente com um clique.'],
        ['titulo' => 'Segurança & Privacidade','texto' => 'Seus dados protegidos com criptografia de ponta a ponta e total confidencialidade.'],
    ];
}

/** Seção 2 — Soluções Legais. */
function solucoes_legais(): array
{
    return [
        [
            'titulo' => 'Minuta Inteligente de Peças',
            'texto'  => 'Geração automatizada de petições iniciais, contestações, recursos e agravos rigorosamente alinhados às exigências formais do CPC/2015 e jurisprudência atual.',
        ],
        [
            'titulo' => 'Auditoria e Revisão Contratual',
            'texto'  => 'Leitura técnica instantânea que identifica cláusulas abusivas, riscos de litígio, omissões críticas e sugere redações alternativas seguras.',
        ],
        [
            'titulo' => 'Comunicação Executiva para Clientes',
            'texto'  => 'Tradução direta do juridiquês técnico para sínteses claras e objetivas, prontas para envio aos seus clientes via WhatsApp ou e-mail.',
        ],
        [
            'titulo' => 'Gestão Centralizada de Casos',
            'texto'  => 'Organização estruturada de clientes, qualificações, peças e históricos processuais em ambiente de alto desempenho.',
        ],
    ];
}

/** Seção 3 — Áreas do Direito. */
function areas_direito(): array
{
    return [
        [
            'titulo' => 'Direito Civil & Processo Civil',
            'texto'  => 'Ações de indenização, contratos, obrigações, posse, usucapião e cumprimento de sentença fundamentados no CPC/2015.',
            'badge'  => 'CPC/2015',
        ],
        [
            'titulo' => 'Direito Trabalhista',
            'texto'  => 'Reclamatórias trabalhistas, contestações empresariais e recursos fundamentados na CLT e na jurisprudência consolidada do TST.',
            'badge'  => 'CLT · TST',
        ],
        [
            'titulo' => 'Direito de Família & Sucessões',
            'texto'  => 'Ações alimentares, divórcios, inventários, partilhas e guardas tratados com precisão técnica e rigor processual.',
            'badge'  => 'CC · ECA',
        ],
        [
            'titulo' => 'Direito do Consumidor',
            'texto'  => 'Ações de consumo, cobranças indevidas, falhas na prestação de serviço com aplicação direta do CDC e teses fixadas pelos TJs.',
            'badge'  => 'CDC',
        ],
        [
            'titulo' => 'Direito Penal & Processo Penal',
            'texto'  => 'Liberdade provisória, relaxamento de prisão, resposta à acusação e recursos estruturados na doutrina e precedentes do STJ e STF.',
            'badge'  => 'CP · CPP',
        ],
    ];
}

/** Seção 4 — Acervo & Modelos. */
function acervo_modelos(): array
{
    return [
        [
            'titulo' => 'Modelos de Alta Taxa de Deferimento',
            'texto'  => 'Estruturas de peças validadas e organizadas por complexidade, prontas para personalização com os fatos do caso.',
        ],
        [
            'titulo' => 'Atualização Jurisprudencial Contínua',
            'texto'  => 'Súmulas vinculantes, temas repetitivos e precedentes do STF e STJ integrados na argumentação.',
        ],
        [
            'titulo' => 'Teses Jurídicas Relevantes',
            'texto'  => 'Doutrinas e fundamentos de apoio para fortalecer preliminares, pedidos de tutela de urgência e recursos.',
        ],
    ];
}

/** Seção 5 — Ecossistema. */
function ecossistema(): array
{
    return [
        [
            'titulo' => 'Conformidade Rígida com a LGPD',
            'texto'  => 'Criptografia de ponta a ponta para proteger dados de processos, qualificações e dados sob segredo de justiça.',
        ],
        [
            'titulo' => 'Sigilo Profissional Garantido',
            'texto'  => 'Suas peças e informações de clientes não são utilizadas para treinamento público de modelos de IA.',
        ],
        [
            'titulo' => 'Disponibilidade e Alta Performance',
            'texto'  => 'Arquitetura em nuvem com alta redundância, garantindo acesso instantâneo em qualquer dispositivo.',
        ],
        [
            'titulo' => 'Inteligência & Privacidade Nativa',
            'texto'  => 'Processamento ultra-rápido de minutas e peças processuais com total sigilo dos dados, sem dependência de bancos de dados externos expostos.',
        ],
    ];
}
