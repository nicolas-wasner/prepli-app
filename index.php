<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<?php $page_title = 'PrepLi – Plateforme de préparation pédagogique'; include __DIR__ . '/includes/head.php'; ?>
<body class="font-sans bg-gray-50 min-h-screen">
<?php include __DIR__ . '/includes/header.php'; ?>
  <div class="flex flex-col items-center justify-center min-h-[80vh] px-4 pt-24 bg-gradient-to-b from-blue-50 to-white">
    <h1 class="text-4xl md:text-5xl font-extrabold text-blue-700 mb-4 text-center">PrepLi</h1>
    <h2 class="text-xl md:text-2xl text-blue-900 font-semibold mb-6 text-center">La plateforme moderne pour préparer, organiser et exporter vos séances et séquences pédagogiques</h2>
    <p class="max-w-2xl text-lg text-gray-700 text-center mb-8">
      Gagnez du temps dans la préparation de vos cours, centralisez vos fiches et séquences, collaborez et exportez vos documents en PDF ou Word en un clic. PrepLi simplifie la vie des enseignants du primaire et du secondaire avec une interface intuitive et des outils puissants.
    </p>
    <ul class="max-w-xl w-full mb-8 grid grid-cols-1 md:grid-cols-2 gap-4">
      <li class="flex items-center gap-3 bg-white rounded-lg shadow p-4 border border-blue-100"><span class="text-2xl">⚡</span> Création rapide de fiches et séquences</li>
      <li class="flex items-center gap-3 bg-white rounded-lg shadow p-4 border border-blue-100"><span class="text-2xl">📦</span> Export PDF professionnel</li>
      <li class="flex items-center gap-3 bg-white rounded-lg shadow p-4 border border-blue-100"><span class="text-2xl">🔒</span> Données sécurisées et privées</li>
      <li class="flex items-center gap-3 bg-white rounded-lg shadow p-4 border border-blue-100"><span class="text-2xl">🧩</span> Gestion intuitive des séances et séquences</li>
    </ul>
    <div class="flex flex-col md:flex-row gap-4 w-full max-w-xs md:max-w-md justify-center items-center mb-8">
      <a href="/inscription" class="block bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg text-lg font-semibold text-center shadow transition w-full md:w-auto">🆕 Créer un compte</a>
      <a href="/login" class="block bg-gray-200 hover:bg-gray-300 text-blue-700 py-3 px-6 rounded-lg text-lg font-semibold text-center shadow transition w-full md:w-auto">🔐 Connexion</a>
    </div>
  </div>
  <footer class="w-full text-center text-gray-400 text-sm py-4 bg-transparent absolute bottom-0 left-0">
    &copy; <?= date('Y') ?> PrepLi – Facilitez votre préparation pédagogique
  </footer>
</body>
</html>
