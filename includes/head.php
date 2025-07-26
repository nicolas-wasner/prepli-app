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
  <link rel="icon" type="image/png" href="./img/favicon/favicon-96x96.png" sizes="96x96" />
<link rel="icon" type="image/svg+xml" href="./img/favicon/favicon.svg" />
<link rel="shortcut icon" href="./img/favicon/favicon.ico" />
<link rel="apple-touch-icon" sizes="180x180" href="./img/favicon/apple-touch-icon.png" />
<meta name="apple-mobile-web-app-title" content="Prepli" />
<link rel="manifest" href="./img/favicon/site.webmanifest" />
</head>
<script src="/app.js"></script> 