<?php
/**
 * Acervo vetorial decorativo do Hero.
 *
 * Não são ícones de biblioteca: é ilustração de linha desenhada em SVG,
 * com traço "perolado" (dasharray arredondado), luz quente na base,
 * traçados de circuito e ondas de fundo — réplica do protótipo.
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
        <linearGradient id="bal-onda" gradientUnits="userSpaceOnUse" x1="0" y1="0" x2="330" y2="0">
          <stop offset="0%"   stop-color="#94A3B8" stop-opacity="0"/>
          <stop offset="45%"  stop-color="#B8C4D6" stop-opacity="0.5"/>
          <stop offset="100%" stop-color="#94A3B8" stop-opacity="0"/>
        </linearGradient>
        <linearGradient id="bal-onda-ouro" gradientUnits="userSpaceOnUse" x1="0" y1="0" x2="330" y2="0">
          <stop offset="0%"   stop-color="#E2D4A8" stop-opacity="0"/>
          <stop offset="50%"  stop-color="#E2D4A8" stop-opacity="0.75"/>
          <stop offset="100%" stop-color="#E2D4A8" stop-opacity="0"/>
        </linearGradient>
      </defs>

      <!-- Ondas de fundo -->
      <g fill="none" stroke-width="0.9">
        <path class="onda" d="M-16 176 C 44 196 104 214 168 224 C 232 234 288 240 344 242" stroke="url(#bal-onda)" opacity="0.35"/>
        <path class="onda" d="M-16 192 C 44 212 108 230 172 240 C 236 250 292 256 344 258" stroke="url(#bal-onda)" opacity="0.28"/>
        <path class="onda" d="M-16 210 C 48 228 112 246 176 254 C 240 262 296 268 344 270" stroke="url(#bal-onda)" opacity="0.22"/>
        <path class="onda" d="M-16 232 C 52 246 116 258 180 266 C 244 274 300 280 344 282" stroke="url(#bal-onda)" opacity="0.16"/>
        <path class="onda" d="M-16 250 C 56 238 122 232 188 240 C 248 248 300 262 344 274" stroke="url(#bal-onda-ouro)" opacity="0.55" stroke-width="1.1"/>
        <path class="onda" d="M-16 266 C 60 254 128 248 194 258 C 254 266 304 280 344 292" stroke="url(#bal-onda-ouro)" opacity="0.3"/>
      </g>

      <!-- Luz quente sob a base -->
      <g class="luz-base">
        <ellipse cx="110" cy="190" rx="78" ry="26" fill="url(#bal-luz)"/>
        <ellipse cx="110" cy="190" rx="26" ry="7"  fill="#FFD37A" opacity="0.55"/>
      </g>

      <!-- Traçados de circuito -->
      <g stroke="#8FA3BE" stroke-width="0.7" fill="none" opacity="0.55">
        <path d="M150 176 H188 L198 167 H222"/>
        <path d="M152 188 H208 L218 179 H252"/>
        <path d="M122 201 H140 L150 210 H198"/>
        <path d="M98 214 H134 L144 223 H182"/>
        <path d="M62 233 H114 L124 242 H168"/>
        <path d="M170 196 H196 L204 204 H236"/>
      </g>
      <g fill="none" stroke="#C7D3E3" stroke-width="0.8">
        <circle class="no-circuito" cx="225" cy="167" r="2.2"/>
        <circle class="no-circuito" cx="255" cy="179" r="1.8"/>
        <circle class="no-circuito" cx="201" cy="210" r="2"/>
        <circle class="no-circuito" cx="185" cy="223" r="1.7"/>
        <circle class="no-circuito" cx="171" cy="242" r="2.1"/>
        <circle class="no-circuito" cx="239" cy="204" r="1.6"/>
        <circle class="no-circuito" cx="119" cy="201" r="1.5"/>
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

      <!-- Poeira luminosa -->
      <g fill="#E2D4A8">
        <circle class="no-circuito" cx="66"  cy="128" r="0.9" opacity="0.6"/>
        <circle class="no-circuito" cx="152" cy="128" r="0.8" opacity="0.5"/>
        <circle class="no-circuito" cx="188" cy="86"  r="0.9" opacity="0.45"/>
        <circle class="no-circuito" cx="24"  cy="72"  r="0.8" opacity="0.4"/>
        <circle class="no-circuito" cx="205" cy="130" r="0.7" opacity="0.45"/>
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
        <linearGradient id="tri-onda" gradientUnits="userSpaceOnUse" x1="0" y1="0" x2="320" y2="0">
          <stop offset="0%"   stop-color="#94A3B8" stop-opacity="0"/>
          <stop offset="55%"  stop-color="#B8C4D6" stop-opacity="0.5"/>
          <stop offset="100%" stop-color="#94A3B8" stop-opacity="0"/>
        </linearGradient>
        <linearGradient id="tri-onda-ouro" gradientUnits="userSpaceOnUse" x1="0" y1="0" x2="320" y2="0">
          <stop offset="0%"   stop-color="#E2D4A8" stop-opacity="0"/>
          <stop offset="50%"  stop-color="#E2D4A8" stop-opacity="0.7"/>
          <stop offset="100%" stop-color="#E2D4A8" stop-opacity="0"/>
        </linearGradient>
      </defs>

      <!-- Ondas de fundo -->
      <g fill="none" stroke-width="0.9">
        <path class="onda" d="M-16 236 C 40 232 96 220 152 206 C 216 190 272 172 336 158" stroke="url(#tri-onda)" opacity="0.32"/>
        <path class="onda" d="M-16 252 C 40 248 100 236 156 222 C 220 206 276 188 336 174" stroke="url(#tri-onda)" opacity="0.26"/>
        <path class="onda" d="M-16 266 C 44 262 104 250 160 236 C 224 220 280 202 336 190" stroke="url(#tri-onda)" opacity="0.2"/>
        <path class="onda" d="M-16 282 C 48 278 108 266 164 252 C 228 236 284 220 336 208" stroke="url(#tri-onda)" opacity="0.15"/>
        <path class="onda" d="M-16 292 C 52 286 116 268 176 246 C 236 224 288 206 336 196" stroke="url(#tri-onda-ouro)" opacity="0.55" stroke-width="1.1"/>
        <path class="onda" d="M-16 300 C 56 296 122 280 182 258 C 242 236 292 220 336 212" stroke="url(#tri-onda-ouro)" opacity="0.28"/>
      </g>

      <!-- Luz quente sob o estilóbata -->
      <g class="luz-base">
        <ellipse cx="176" cy="186" rx="84" ry="26" fill="url(#tri-luz)"/>
        <ellipse cx="176" cy="186" rx="30" ry="7"  fill="#FFD37A" opacity="0.5"/>
      </g>

      <!-- Traçados de circuito -->
      <g stroke="#8FA3BE" stroke-width="0.7" fill="none" opacity="0.55">
        <path d="M104 182 H 68 L 58 191 H 22"/>
        <path d="M112 192 H 74 L 64 201 H 30"/>
        <path d="M120 202 H 96 L 86 211 H 46"/>
        <path d="M148 208 H 118 L 108 217 H 72"/>
        <path d="M172 212 H 140 L 130 221 H 96"/>
        <path d="M100 172 H 78 L 70 180 H 40"/>
      </g>
      <g fill="none" stroke="#C7D3E3" stroke-width="0.8">
        <circle class="no-circuito" cx="19" cy="191" r="2.2"/>
        <circle class="no-circuito" cx="27" cy="201" r="1.7"/>
        <circle class="no-circuito" cx="43" cy="211" r="2"/>
        <circle class="no-circuito" cx="69" cy="217" r="1.8"/>
        <circle class="no-circuito" cx="93" cy="221" r="2.1"/>
        <circle class="no-circuito" cx="37" cy="180" r="1.6"/>
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

      <!-- Poeira luminosa -->
      <g fill="#E2D4A8">
        <circle class="no-circuito" cx="228" cy="62"  r="0.9" opacity="0.55"/>
        <circle class="no-circuito" cx="150" cy="58"  r="0.8" opacity="0.45"/>
        <circle class="no-circuito" cx="292" cy="122" r="0.9" opacity="0.4"/>
        <circle class="no-circuito" cx="88"  cy="130" r="0.8" opacity="0.4"/>
        <circle class="no-circuito" cx="189" cy="24"  r="1"   opacity="0.6"/>
      </g>
    </svg>
    <?php
}
