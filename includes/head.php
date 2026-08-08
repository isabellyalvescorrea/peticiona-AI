<?php
/**
 * <head> compartilhado — Design System do Peticiona AI.
 * $pagina_titulo e $pagina_descricao podem ser definidos antes do include.
 */
require_once __DIR__ . '/config.php';

$pagina_titulo    = $pagina_titulo    ?? APP_NAME . ' — ' . APP_TAGLINE;
$pagina_descricao = $pagina_descricao ?? 'Sistema de inteligência jurídica que redige peças processuais, audita contratos e organiza a rotina de escritórios de advocacia brasileiros.';
?>
<!DOCTYPE html>
<html lang="pt-BR" class="scroll-suave">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="description" content="<?= e($pagina_descricao) ?>">
<meta name="theme-color" content="#0B132B">
<title><?= e($pagina_titulo) ?></title>

<link rel="icon" type="image/png" href="<?= e(asset('assets/img/logo.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          navy:     '#0B132B', // Azul Marinho Nobre — fundo global da aplicação
          panel:    '#13203F', // Superfície de cards, um degrau acima do fundo
          gold:     '#E2D4A8',
          sapphire: '#38BDF8',
          silk:     '#F8FAFC',
          silver:   '#94A3B8',
        },
        fontFamily: {
          sans:    ['Inter', 'system-ui', 'sans-serif'],
          display: ['"Cormorant Garamond"', 'Georgia', 'serif'],
        },
        letterSpacing: {
          luxo: '0.32em',
        },
      },
    },
  };
</script>
<link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
</head>
<body class="bg-navy font-sans text-silk antialiased selection:bg-gold selection:text-navy">
