<?php
// Utilisation : définir $page_title avant d'inclure ce fichier pour personnaliser le titre
if (!isset($page_title)) {
  $page_title = 'PrepLi – Application de préparation de séances';
}
?>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="/public-tailwind.css">
  <title><?= htmlspecialchars($page_title) ?></title>
</head> 