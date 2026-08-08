<?php
/**
 * Acervo vetorial decorativo do Hero.
 *
 * Não são ícones de biblioteca: é ilustração de linha desenhada em SVG,
 * com traço "perolado" (dasharray arredondado), luz quente na base e
 * curvas ambientes em Ouro Champanhe fluindo em direção ao centro da tela.
 *
 * Exibida apenas em telas grandes (o próprio index.php aplica o hidden/lg:block).
 */

/** Balança da Justiça — lateral esquerda do Hero. */
function arte_balanca(): void
{
    ?>
    <svg class="arte-hero h-full w-full" viewBox="0 0 330 300" fill="none" aria-hidden="true" focusable="false">
      <defs>
        <!-- userSpaceOnUse é obrigatório: traços perfeitamente horizontais ou
             verticais têm bounding box de dimensão zero e não seriam pintados
             por um gradiente em objectBoundingBox. -->
        <linearGradient id="bal-ouro" gradientUnits="userSpaceOnUse" x1="0" y1="8" x2="0" y2="200">
          <stop offset="0%"   stop-color="#F6EFDA"/>
          <stop offset="45%"  stop-color="#E2D4A8"/>
          <stop offset="100%" stop-color="#A98B4C"/>
        </linearGradient>
        <radialGradient id="bal-luz" cx="50%" cy="50%" r="50%">
          <stop offset="0%"   stop-color="#FFD37A" stop-opacity="0.95"/>
          <stop offset="35%"  stop-color="#E8A94D" stop-opacity="0.34"/>
          <stop offset="100%" stop-color="#E8A94D" stop-opacity="0"/>
        </radialGradient>
        <!-- Curvas ambientes: acendem em direção ao centro da tela (onde ficam
             os botões) e se apagam na borda externa, para não cortarem seco. -->
        <linearGradient id="bal-onda" gradientUnits="userSpaceOnUse" x1="0" y1="0" x2="330" y2="0">
          <stop offset="0%"   stop-color="#E2D4A8" stop-opacity="0.06"/>
          <stop offset="35%"  stop-color="#F4EACB" stop-opacity="0.95"/>
          <stop offset="72%"  stop-color="#E2D4A8" stop-opacity="0.85"/>
          <stop offset="100%" stop-color="#E2D4A8" stop-opacity="0"/>
        </linearGradient>
      </defs>

      <!-- Curvas ambientes em Ouro Champanhe -->
      <g class="ondas-ambiente" fill="none" stroke="url(#bal-onda)">
        <path class="onda" d="M-16 176 C 44 196 104 214 168 224 C 232 234 288 240 344 242" opacity="0.75" stroke-width="1.25"/>
        <path class="onda" d="M-16 192 C 44 212 108 230 172 240 C 236 250 292 256 344 258" opacity="0.6"  stroke-width="1.1"/>
        <path class="onda" d="M-16 210 C 48 228 112 246 176 254 C 240 262 296 268 344 270" opacity="0.5"  stroke-width="1"/>
        <path class="onda" d="M-16 232 C 52 246 116 258 180 266 C 244 274 300 280 344 282" opacity="0.42" stroke-width="0.95"/>
        <path class="onda" d="M-16 250 C 56 238 122 232 188 240 C 248 248 300 262 344 274" opacity="0.9"  stroke-width="1.4"/>
        <path class="onda" d="M-16 266 C 60 254 128 248 194 258 C 254 266 304 280 344 292" opacity="0.55" stroke-width="1.05"/>
      </g>

      <!-- Luz quente sob a base -->
      <g class="luz-base">
        <ellipse cx="110" cy="190" rx="78" ry="26" fill="url(#bal-luz)"/>
        <ellipse cx="110" cy="190" rx="26" ry="7"  fill="#FFD37A" opacity="0.55"/>
      </g>

      <!-- Balança: traço perolado -->
      <g stroke="url(#bal-ouro)" stroke-width="1.7" stroke-linecap="round" fill="none"
         stroke-dasharray="0.5 3.1">

        <!-- Remate superior e haste -->
        <circle cx="108" cy="13" r="3.6"/>
        <path d="M108 17 V 147"/>
        <ellipse cx="108" cy="30" rx="4.2" ry="5.4"/>
        <ellipse cx="108" cy="48" rx="6.4" ry="10.5"/>
        <circle cx="108" cy="45" r="2.6"/>

        <!-- Travessão -->
        <path d="M40 44 C 60 36 84 41 108 42 C 132 41 156 36 177 44"/>
        <circle cx="40"  cy="44" r="3.6"/>
        <circle cx="177" cy="44" r="3.6"/>

        <!-- Prato esquerdo -->
        <path d="M40 47 L 11 117"/>
        <path d="M40 47 L 75 117"/>
        <path d="M40 47 L 43 117"/>
        <path d="M9 117 H 77"/>
        <path d="M9 117 C 14 134 27 141 43 141 C 59 141 72 134 77 117"/>

        <!-- Prato direito -->
        <path d="M177 47 L 141 117"/>
        <path d="M177 47 L 205 117"/>
        <path d="M177 47 L 173 117"/>
        <path d="M139 117 H 207"/>
        <path d="M139 117 C 144 134 157 141 173 141 C 189 141 202 134 207 117"/>

        <!-- Pé e plinto -->
        <path d="M97 146 H 119"/>
        <path d="M97 146 L 93 154"/>
        <path d="M119 146 L 123 154"/>
        <path d="M93 154 H 123"/>
        <path d="M108 154 V 163"/>
        <path d="M96 163 H 120"/>
        <path d="M96 163 L 91 172"/>
        <path d="M120 163 L 125 172"/>
        <path d="M91 172 H 125"/>
        <path d="M91 172 L 80 179"/>
        <path d="M125 172 L 140 179"/>
        <path d="M82 179 H 138 C 145 179 149 183 149 187.5 C 149 192 145 196 138 196 H 82 C 75 196 71 192 71 187.5 C 71 183 75 179 82 179 Z"/>
      </g>

    </svg>
    <?php
}

/** Frontão do Tribunal — lateral direita do Hero. */
function arte_tribunal(): void
{
    $colunas = [132.5, 169.5, 206.5, 243.5];
    ?>
    <svg class="arte-hero h-full w-full" viewBox="0 0 320 300" fill="none" aria-hidden="true" focusable="false">
      <defs>
        <!-- Ver nota em arte_balanca(): userSpaceOnUse é obrigatório aqui. -->
        <linearGradient id="tri-ouro" gradientUnits="userSpaceOnUse" x1="0" y1="28" x2="0" y2="200">
          <stop offset="0%"   stop-color="#F6EFDA"/>
          <stop offset="45%"  stop-color="#E2D4A8"/>
          <stop offset="100%" stop-color="#A98B4C"/>
        </linearGradient>
        <radialGradient id="tri-luz" cx="50%" cy="50%" r="50%">
          <stop offset="0%"   stop-color="#FFD37A" stop-opacity="0.95"/>
          <stop offset="35%"  stop-color="#E8A94D" stop-opacity="0.34"/>
          <stop offset="100%" stop-color="#E8A94D" stop-opacity="0"/>
        </radialGradient>
        <!-- Espelho da esquerda: aqui o centro da tela fica à esquerda, então o
             brilho cresce nesse sentido e some na borda externa. -->
        <linearGradient id="tri-onda" gradientUnits="userSpaceOnUse" x1="0" y1="0" x2="320" y2="0">
          <stop offset="0%"   stop-color="#E2D4A8" stop-opacity="0"/>
          <stop offset="28%"  stop-color="#E2D4A8" stop-opacity="0.85"/>
          <stop offset="65%"  stop-color="#F4EACB" stop-opacity="0.95"/>
          <stop offset="100%" stop-color="#E2D4A8" stop-opacity="0.06"/>
        </linearGradient>
      </defs>

      <!-- Curvas ambientes em Ouro Champanhe -->
      <g class="ondas-ambiente" fill="none" stroke="url(#tri-onda)">
        <path class="onda" d="M-16 236 C 40 232 96 220 152 206 C 216 190 272 172 336 158" opacity="0.72" stroke-width="1.25"/>
        <path class="onda" d="M-16 252 C 40 248 100 236 156 222 C 220 206 276 188 336 174" opacity="0.58" stroke-width="1.1"/>
        <path class="onda" d="M-16 266 C 44 262 104 250 160 236 C 224 220 280 202 336 190" opacity="0.48" stroke-width="1"/>
        <path class="onda" d="M-16 282 C 48 278 108 266 164 252 C 228 236 284 220 336 208" opacity="0.4"  stroke-width="0.95"/>
        <path class="onda" d="M-16 292 C 52 286 116 268 176 246 C 236 224 288 206 336 196" opacity="0.9"  stroke-width="1.4"/>
        <path class="onda" d="M-16 300 C 56 296 122 280 182 258 C 242 236 292 220 336 212" opacity="0.52" stroke-width="1.05"/>
      </g>

      <!-- Luz quente sob o estilóbata -->
      <g class="luz-base">
        <ellipse cx="176" cy="186" rx="84" ry="26" fill="url(#tri-luz)"/>
        <ellipse cx="176" cy="186" rx="30" ry="7"  fill="#FFD37A" opacity="0.5"/>
      </g>

      <!-- Tribunal: traço perolado -->
      <g stroke="url(#tri-ouro)" stroke-width="1.7" stroke-linecap="round" fill="none"
         stroke-dasharray="0.5 3.1">

        <!-- Frontão -->
        <path d="M105 78 L 189 31 L 273 78"/>
        <path d="M117 73 L 189 41 L 261 73"/>
        <path d="M103 78 H 275"/>

        <!-- Arquitrave -->
        <path d="M103 86 H 275"/>
        <path d="M103 78 V 86"/>
        <path d="M275 78 V 86"/>
        <path d="M114 89 H 264"/>
        <path d="M114 95 H 264"/>
        <path d="M114 89 V 95"/>
        <path d="M264 89 V 95"/>

        <!-- Colunatas -->
        <?php foreach ($colunas as $c): ?>
          <path d="M<?= $c - 11.5 ?> 96 H <?= $c + 11.5 ?>"/>
          <path d="M<?= $c - 11.5 ?> 96 V 104"/>
          <path d="M<?= $c + 11.5 ?> 96 V 104"/>
          <path d="M<?= $c - 11.5 ?> 104 H <?= $c + 11.5 ?>"/>
          <path d="M<?= $c - 8.5 ?> 104 V 162"/>
          <path d="M<?= $c + 8.5 ?> 104 V 162"/>
          <path d="M<?= $c - 3 ?> 107 V 159" opacity="0.55"/>
          <path d="M<?= $c + 3 ?> 107 V 159" opacity="0.55"/>
          <path d="M<?= $c - 11.5 ?> 162 H <?= $c + 11.5 ?>"/>
          <path d="M<?= $c - 11.5 ?> 162 V 170"/>
          <path d="M<?= $c + 11.5 ?> 162 V 170"/>
          <path d="M<?= $c - 13 ?> 170 H <?= $c + 13 ?>"/>
        <?php endforeach; ?>

        <!-- Estilóbata e degraus -->
        <path d="M114 170 H 264"/>
        <path d="M114 177 H 264"/>
        <path d="M114 170 V 177"/>
        <path d="M264 170 V 177"/>
        <path d="M104 179 H 274"/>
        <path d="M104 186 H 274"/>
        <path d="M104 179 V 186"/>
        <path d="M274 179 V 186"/>
        <path d="M96 188 H 282"/>
        <path d="M96 194 H 282"/>
        <path d="M96 188 V 194"/>
        <path d="M282 188 V 194"/>
      </g>

    </svg>
    <?php
}
