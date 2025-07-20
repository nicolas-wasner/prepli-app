<?php
// Utilisation : définir $page_title avant d'inclure ce fichier pour personnaliser le titre
if (!isset($page_title)) {
  $page_title = 'PrepLi – Application de préparation de séances';
}
?>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/public-tailwind.css">
  <style>
    body, .font-sans, input, textarea, select, button, .menu, .nav, .header, .footer {
      font-family: 'Montserrat', Arial, sans-serif !important;
      font-size: 1rem;
    }
  </style>
  <title><?= htmlspecialchars($page_title) ?></title>
</head> 