# Peticiona AI — Protótipo de Interface

Front-end visual completo do SaaS de Inteligência e Gestão Jurídica **Peticiona AI**,
em PHP puro + Tailwind CSS, para o mercado de advocacia brasileiro.

> **Protótipo visual.** Não há backend, banco de dados, chaves de API ou chamadas de rede.
> Todos os textos, indicadores e respostas do assistente são estáticos e demonstrativos.

## Como executar

Requer PHP 8.0+ (validado em PHP 8.4).

```bash
php -S localhost:8000 -t "C:\Peticiona AI"
```

Depois abra <http://localhost:8000>.

## Estrutura

```
index.php                      Landing page pública — 5 seções em single-page scroll
dashboard.php                  Painel · Visão Geral
gerador-de-pecas.php           Painel · Gerador de Peças
analisador-de-contratos.php    Painel · Analisador de Contratos
meus-clientes.php              Painel · Meus Clientes & Processos

includes/
  config.php                   Paleta, navegação e todo o conteúdo textual (arrays PHP)
  head.php                     <head> compartilhado + configuração do Tailwind
  header-publico.php           Header fixo, âncoras e menu mobile
  footer-publico.php           Rodapé público + modal "Acessar Sistema"
  header-painel.php            Barra lateral e topo do ambiente logado
  footer-painel.php            Rodapé do ambiente logado
  arte-hero.php                Ilustrações vetoriais do Hero (balança e tribunal)

assets/
  css/app.css                  Acabamento de luxo: filetes, texturas, micro-interações
  js/app.js                    Menu, modal, revelações e simuladores mockados
  img/logo.png                 Logo oficial (recortada ao conteúdo)
  img/referencia-prototipo.jpeg  Gabarito visual usado como referência
```

## Design system

| Papel | Cor |
| --- | --- |
| Fundo da aplicação | `#0B132B` (Azul Marinho Nobre) |
| Cards e contêineres | `#13203F` |
| Destaques / botões | `#E2D4A8` (Ouro Champanhe) |
| Badges técnicas | `#38BDF8` (Azul Safira) |
| Texto principal | `#F8FAFC` |
| Subtítulos | `#E2E8F0` (`slate-200`) |
| Texto secundário | `#94A3B8` |

O tom dos cards (`#13203F`) acompanha o fundo: precisa ficar um degrau acima do
Azul Marinho para que as superfícies continuem legíveis sem depender só da borda.

Tipografia: **Inter** em toda a hierarquia de títulos e texto corrido.
**Cormorant Garamond** fica reservada aos acentos — rótulos de seção, numerais
ordinais e a folha virtual da peça processual.

**Sem ícones.** Toda a comunicação visual é feita por tipografia, filetes, numerais
ordinais, hierarquia e luz — inclusive o botão de menu mobile, construído com três
filetes em CSS. As ilustrações do Hero são desenho de linha em SVG, não iconografia.

## Notas de implementação

- O header público tem fundo **100% sólido** (`#070A12`), sem transparência e sem `backdrop-blur`.
- Rolagem suave via `scroll-behavior: smooth`, com `scroll-padding-top` compensando o header fixo.
- As artes laterais do Hero são ocultadas abaixo de `lg` para manter a leitura limpa no mobile.
- Os gradientes dourados do SVG usam `gradientUnits="userSpaceOnUse"`: traços perfeitamente
  horizontais ou verticais têm *bounding box* de dimensão zero e não seriam pintados de outro modo.
- `@media (prefers-reduced-motion: reduce)` desliga animações e rolagem suave.
- Ausência de overflow lateral verificada em 5 páginas × 10 larguras (360px a 1920px).

## Próximos passos (fora do escopo deste protótipo)

Integração com backend, autenticação real, persistência de clientes e peças,
geração efetiva de `.docx`/PDF e conexão com o modelo de linguagem.
