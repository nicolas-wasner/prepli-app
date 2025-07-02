<?php
http_response_code(404);
?>

<!DOCTYPE html>
<html lang="fr">
<?php $page_title = 'Page non trouvée'; include __DIR__ . '/includes/head.php'; ?>
<body class="pt-16">
  <?php include 'includes/header.php'; // include header within body tag ?>
  <h1>Erreur 404 – Page introuvable</h1>
  <p>La page que vous cherchez n'existe pas ou a été déplacée.</p>
  <p><a href="/">← Retour à l'accueil</a></p>
</body>
</html>
