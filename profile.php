<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<?php $page_title = 'Mon profil – Application PrepLi'; include __DIR__ . '/includes/head.php'; ?>
<body class="font-sans bg-gray-50 min-h-screen">
<?php include __DIR__ . '/includes/header.php'; ?>
<?php
require_once __DIR__ . '/includes/config.php';
$stmt = $pdo->prepare("SELECT limite_fiches, limite_sequences FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['utilisateur_id']]);
$limites = $stmt->fetch();
$limiteFiches = $limites['limite_fiches'] ?? 1;
$limiteSequences = $limites['limite_sequences'] ?? 1;
$stmt = $pdo->prepare("SELECT COUNT(*) FROM fiches WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['utilisateur_id']]);
$nbFiches = $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM sequences WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['utilisateur_id']]);
$nbSequences = $stmt->fetchColumn();
?>
  <div class="flex flex-col items-center justify-center min-h-[80vh] px-4 pt-20">
    <h1 class="text-3xl md:text-4xl font-bold text-blue-700 mb-8 text-center">Bienvenue dans l'application PrepLi 📚</h1>

    <div class="flex flex-col gap-4 w-full max-w-xs">
      <?php if (isset($_SESSION['utilisateur_id'])): ?>
        <a href="/fiches" class="block bg-blue-500 hover:bg-blue-600 text-white py-3 px-6 rounded-lg text-lg font-semibold text-center shadow transition">📄 Mes fiches</a>
        <a href="/sequences" class="block bg-blue-500 hover:bg-blue-600 text-white py-3 px-6 rounded-lg text-lg font-semibold text-center shadow transition">🧩 Mes séquences</a>
        <a href="/logout" class="block bg-gray-300 hover:bg-gray-400 text-gray-800 py-3 px-6 rounded-lg text-lg font-semibold text-center shadow transition">🚪 Se déconnecter</a>
      <?php else: ?>
        <a href="/login" class="block bg-blue-500 hover:bg-blue-600 text-white py-3 px-6 rounded-lg text-lg font-semibold text-center shadow transition">🔐 Connexion</a>
        <a href="/inscription" class="block bg-green-500 hover:bg-green-600 text-white py-3 px-6 rounded-lg text-lg font-semibold text-center shadow transition">🆕 Créer un compte</a>
      <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['utilisateur_nom'])): ?>
      <div class="mt-8 text-gray-600 text-base text-center">
        Connecté en tant que <strong><?= htmlspecialchars($_SESSION['utilisateur_nom']) ?></strong>
      </div>
    <?php endif; ?>
    <div class="mt-8 text-gray-700 text-base text-center">
      <div class="mb-2">Vous avez créé <strong><?= $nbFiches ?></strong> fiche<?= $nbFiches > 1 ? 's' : '' ?> (limite = <?= $limiteFiches ?>) et <strong><?= $nbSequences ?></strong> séquence<?= $nbSequences > 1 ? 's' : '' ?> (limite = <?= $limiteSequences ?>).</div>
      <div class="text-sm text-yellow-700">Limite : 1 fiche(s) et 1 séquence(s) maximum par utilisateur.</div>
    </div>
  </div>
</body>
</html> 