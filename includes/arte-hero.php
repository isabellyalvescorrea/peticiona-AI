<?php
/**
 * Curvas ambientes do Hero.
 *
 * Traçado, curvatura, gradientes, opacidades e espessuras são exatamente os
 * originais — as duas famílias que nasciam junto aos desenhos da balança e do
 * tribunal. Removidos os ícones e a luz quente de base, restaram as curvas
 * intactas, cada família fluindo da sua lateral em direção ao centro da tela.
 */

/** Família da esquerda. */
function arte_ondas_esquerda(): void
{
    ?>
    <svg class="arte-hero h-full w-full" viewBox="0 0 330 300" fill="none" aria-hidden="true" focusable="false">
      <defs>
        <!-- Acende no sentido do centro da tela e se apaga na borda externa,
             para a linha sumir em vez de cortar seco. -->
        <linearGradient id="bal-onda" gradientUnits="userSpaceOnUse" x1="0" y1="0" x2="330" y2="0">
          <stop offset="0%"   stop-color="#E2D4A8" stop-opacity="0.06"/>
          <stop offset="35%"  stop-color="#F4EACB" stop-opacity="0.95"/>
          <stop offset="72%"  stop-color="#E2D4A8" stop-opacity="0.85"/>
          <stop offset="100%" stop-color="#E2D4A8" stop-opacity="0"/>
        </linearGradient>
      </defs>

      <g class="ondas-ambiente" fill="none" stroke="url(#bal-onda)">
        <path class="onda" d="M-16 176 C 44 196 104 214 168 224 C 232 234 288 240 344 242" opacity="0.75" stroke-width="1.25"/>
        <path class="onda" d="M-16 192 C 44 212 108 230 172 240 C 236 250 292 256 344 258" opacity="0.6"  stroke-width="1.1"/>
        <path class="onda" d="M-16 210 C 48 228 112 246 176 254 C 240 262 296 268 344 270" opacity="0.5"  stroke-width="1"/>
        <path class="onda" d="M-16 232 C 52 246 116 258 180 266 C 244 274 300 280 344 282" opacity="0.42" stroke-width="0.95"/>
        <path class="onda" d="M-16 250 C 56 238 122 232 188 240 C 248 248 300 262 344 274" opacity="0.9"  stroke-width="1.4"/>
        <path class="onda" d="M-16 266 C 60 254 128 248 194 258 C 254 266 304 280 344 292" opacity="0.55" stroke-width="1.05"/>
      </g>
    </svg>
    <?php
}

/** Família da direita, espelhada: aqui o centro da tela fica à esquerda. */
function arte_ondas_direita(): void
{
    ?>
    <svg class="arte-hero h-full w-full" viewBox="0 0 320 300" fill="none" aria-hidden="true" focusable="false">
      <defs>
        <linearGradient id="tri-onda" gradientUnits="userSpaceOnUse" x1="0" y1="0" x2="320" y2="0">
          <stop offset="0%"   stop-color="#E2D4A8" stop-opacity="0"/>
          <stop offset="28%"  stop-color="#E2D4A8" stop-opacity="0.85"/>
          <stop offset="65%"  stop-color="#F4EACB" stop-opacity="0.95"/>
          <stop offset="100%" stop-color="#E2D4A8" stop-opacity="0.06"/>
        </linearGradient>
      </defs>

      <g class="ondas-ambiente" fill="none" stroke="url(#tri-onda)">
        <path class="onda" d="M-16 236 C 40 232 96 220 152 206 C 216 190 272 172 336 158" opacity="0.72" stroke-width="1.25"/>
        <path class="onda" d="M-16 252 C 40 248 100 236 156 222 C 220 206 276 188 336 174" opacity="0.58" stroke-width="1.1"/>
        <path class="onda" d="M-16 266 C 44 262 104 250 160 236 C 224 220 280 202 336 190" opacity="0.48" stroke-width="1"/>
        <path class="onda" d="M-16 282 C 48 278 108 266 164 252 C 228 236 284 220 336 208" opacity="0.4"  stroke-width="0.95"/>
        <path class="onda" d="M-16 292 C 52 286 116 268 176 246 C 236 224 288 206 336 196" opacity="0.9"  stroke-width="1.4"/>
        <path class="onda" d="M-16 300 C 56 296 122 280 182 258 C 242 236 292 220 336 212" opacity="0.52" stroke-width="1.05"/>
      </g>
    </svg>
    <?php
}
