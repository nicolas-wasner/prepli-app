<?php
http_response_code(404);
include 'includes/header.php'; // si tu utilises un header global
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Page non trouvée</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h1>Erreur 404 – Page introuvable</h1>
  <p>La page que vous cherchez n’existe pas ou a été déplacée.</p>
  <p><a href="/">← Retour à l’accueil</a></p>
</body>
</html>
