<?php
/**
 * Respostas de simulação usadas enquanto GEMINI_API_KEY não estiver configurada.
 *
 * Devolvem Markdown com a mesma forma que se espera do modelo real — títulos,
 * parágrafos, citação recuada e bloco de assinatura — para que o conversor de
 * Markdown e o gerador de PDF sejam exercitados de verdade no teste local.
 */

declare(strict_types=1);

function resposta_simulada(string $tarefa, array $entrada): string
{
    return $tarefa === 'auditoria'
        ? simulacao_auditoria($entrada)
        : simulacao_peca($entrada);
}

function simulacao_peca(array $e): string
{
    $tipo    = trim((string) ($e['tipoPeca'] ?? 'Petição Inicial'));
    $autor   = trim((string) ($e['autor'] ?? '')) ?: 'AUTOR NÃO QUALIFICADO';
    $reu     = trim((string) ($e['reu'] ?? '')) ?: 'RÉU NÃO QUALIFICADO';
    $fatos   = trim((string) ($e['fatos'] ?? '')) ?: 'Os fatos não foram detalhados no formulário.';
    $pedidos = trim((string) ($e['pedidos'] ?? '')) ?: 'Os pedidos não foram detalhados no formulário.';
    $data    = strftime_pt();

    return <<<TEXTO
# EXCELENTÍSSIMO SENHOR DOUTOR JUIZ DE DIREITO DA ___ VARA CÍVEL DA COMARCA DE SÃO PAULO — SP

{$autor}, já qualificado nos autos, vem, respeitosamente, à presença de Vossa Excelência, por intermédio de sua advogada que esta subscreve, com fundamento nos artigos 319 e seguintes do Código de Processo Civil, propor a presente **{$tipo}** em face de {$reu}, pelos fundamentos de fato e de direito a seguir expostos.

## I — DOS FATOS

{$fatos}

Os elementos documentais que instruem a presente demonstram a regularidade da conduta do requerente e a integral inobservância, pela parte contrária, dos deveres assumidos.

## II — DO DIREITO

A conduta narrada contraria o princípio da boa-fé objetiva, insculpido no artigo 422 do Código Civil, do qual decorrem os deveres anexos de informação, cooperação e lealdade. O inadimplemento apurado autoriza a tutela específica da obrigação, nos termos do artigo 497 do Código de Processo Civil.

> A boa-fé objetiva impõe às partes um padrão de conduta pautado pela lealdade e pela cooperação, cuja violação enseja responsabilidade independentemente de previsão contratual expressa.

Quanto ao dano extrapatrimonial, a jurisprudência consolidada reconhece que a frustração injustificada de expectativa legítima ultrapassa o mero dissabor cotidiano e atinge a esfera dos direitos da personalidade, impondo o dever de indenizar na forma dos artigos 186 e 927 do Código Civil.

## III — DOS PEDIDOS

{$pedidos}

Protesta provar o alegado por todos os meios de prova em direito admitidos, especialmente pela prova documental, testemunhal e pelo depoimento pessoal do representante legal da parte requerida.

Termos em que,
pede deferimento.

São Paulo, {$data}.

::assinatura::
Helena Vasconcelos
OAB/SP 214.907
TEXTO;
}

function simulacao_auditoria(array $e): string
{
    $contrato = trim((string) ($e['contrato'] ?? '')) ?: 'Instrumento não identificado';
    $trecho   = trim((string) ($e['texto'] ?? ''));
    $amostra  = $trecho !== ''
        ? mb_substr($trecho, 0, 220) . (mb_strlen($trecho) > 220 ? '…' : '')
        : 'Nenhum texto contratual foi submetido.';

    return <<<TEXTO
# PARECER DE AUDITORIA CONTRATUAL

**Instrumento analisado:** {$contrato}

## I — DELIMITAÇÃO DO OBJETO

A análise percorreu o instrumento cláusula por cláusula, cotejando a redação com a legislação vigente e a jurisprudência consolidada. Trecho de referência submetido:

> {$amostra}

## II — RISCOS CRÍTICOS IDENTIFICADOS

**Cláusula 7.ª — Rescisão.** Prevê rescisão unilateral imotivada por apenas uma das partes, sem aviso prévio e sem contrapartida indenizatória, o que caracteriza potestatividade vedada pelo artigo 122 do Código Civil.

**Cláusula 12.ª — Foro de eleição.** Elege comarca distante do domicílio da parte aderente em contrato de adesão, hipótese em que a jurisprudência reconhece a abusividade e admite a declinação de ofício.

**Cláusula 15.ª — Multa moratória.** Estipula multa de 20% sobre o valor total do contrato, percentual superior ao limite consolidado para relações de consumo e passível de redução equitativa pelo juízo.

## III — CLÁUSULAS AUSENTES

Não há disciplina de confidencialidade, tampouco definição dos papéis de controlador e operador exigida pela Lei n.º 13.709/2018. O instrumento é silente quanto ao índice e à periodicidade de reajuste, e não prevê método escalonado de solução de controvérsias.

## IV — RECOMENDAÇÕES

Condicionar a rescisão imotivada a aviso prévio mínimo de trinta dias e a multa compensatória proporcional ao prazo remanescente. Substituir o foro de eleição pelo domicílio do aderente. Reduzir a multa moratória a 2% sobre a parcela inadimplida, com juros de mora de 1% ao mês. Inserir capítulo próprio de proteção de dados e cláusula escalonada de solução de controvérsias.

::assinatura::
Helena Vasconcelos
OAB/SP 214.907
TEXTO;
}

/** Data por extenso em português, sem depender de locale do sistema. */
function strftime_pt(): string
{
    $meses = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho',
              'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
    return date('d') . ' de ' . $meses[(int) date('n') - 1] . ' de ' . date('Y');
}
