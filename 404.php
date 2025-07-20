<?php
http_response_code(404);
?>

<!DOCTYPE html>
<html lang="fr">
<?php $page_title = 'Page non trouvée'; include __DIR__ . '/includes/head.php'; ?>
<body class="font-sans pt-20 min-h-screen">
  <?php include 'includes/header.php'; ?>
  <div class="max-w-2xl mx-auto px-4 py-12">
    <h1 class="text-3xl md:text-4xl font-bold text-blue-700 mb-8 text-center">Erreur 404 – Page introuvable</h1>
    <p class="text-center">La page que vous cherchez n'existe pas ou a été déplacée.</p>
    <p class="text-center"><a href="/" class="text-blue-600 hover:underline">← Retour à l'accueil</a></p>
  </div>
</body>
</html>
