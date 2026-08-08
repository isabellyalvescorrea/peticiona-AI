<?php
/**
 * Camada ambiente do Hero.
 *
 * Uma família de arcos concêntricos, todos abaulados para o mesmo lado e
 * atravessando a largura inteira da tela. Por não se cruzarem, funcionam como
 * fundo e não competem com a leitura — o que a versão anterior, de dois feixes
 * em sentidos opostos, não conseguia.
 *
 * O SVG usa preserveAspectRatio="none" para acompanhar qualquer proporção de
 * tela, e cada traço declara vector-effect="non-scaling-stroke" para que a
 * espessura não se deforme junto: sem isso, o esticamento horizontal deixaria
 * as linhas grossas e irregulares no mobile.
 */

function arte_ondas(): void
{
    // [y nas bordas, y do vértice central, opacidade, espessura]
    $arcos = [
        [150,  22, 0.9,  1.4],
        [188,  66, 0.7,  1.2],
        [228, 112, 0.55, 1.05],
        [270, 160, 0.44, 1.0],
        [314, 210, 0.34, 0.95],
        [360, 262, 0.26, 0.9],
    ];
    ?>
    <svg class="camada-ondas" viewBox="0 0 1440 360" preserveAspectRatio="none"
         fill="none" aria-hidden="true" focusable="false">
      <defs>
        <linearGradient id="onda-champanhe" gradientUnits="userSpaceOnUse" x1="0" y1="0" x2="1440" y2="0">
          <stop offset="0%"   stop-color="#E2D4A8" stop-opacity="0"/>
          <stop offset="20%"  stop-color="#E2D4A8" stop-opacity="0.72"/>
          <stop offset="50%"  stop-color="#F4EACB" stop-opacity="0.95"/>
          <stop offset="80%"  stop-color="#E2D4A8" stop-opacity="0.72"/>
          <stop offset="100%" stop-color="#E2D4A8" stop-opacity="0"/>
        </linearGradient>
      </defs>

      <g class="ondas-ambiente" stroke="url(#onda-champanhe)">
        <?php foreach ($arcos as [$borda, $vertice, $opacidade, $espessura]): ?>
          <path class="onda"
                d="M-40 <?= $borda ?> Q 720 <?= $vertice ?> 1480 <?= $borda ?>"
                opacity="<?= $opacidade ?>"
                stroke-width="<?= $espessura ?>"
                vector-effect="non-scaling-stroke"/>
        <?php endforeach; ?>
      </g>
    </svg>
    <?php
}
