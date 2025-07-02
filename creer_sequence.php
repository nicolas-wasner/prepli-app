<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['utilisateur_id'])) {
  header('Location: /login');
  exit;
}

$success = '';
$erreur = '';

// Récupère les fiches de l'utilisateur connecté
$stmt = $pdo->prepare("SELECT * FROM fiches WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['utilisateur_id']]);
$fiches = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $titre = trim($_POST['titre']);
  $description = trim($_POST['description']);
  $selectionnees = $_POST['fiches'] ?? [];

  if ($titre && count($selectionnees) > 0) {
    // Insère la séquence
    $stmt = $pdo->prepare("INSERT INTO sequences (titre, description, utilisateur_id) VALUES (?, ?, ?)");
    $stmt->execute([$titre, $description, $_SESSION['utilisateur_id']]);
    $id_sequence = $pdo->lastInsertId();

    // Lier chaque fiche à la séquence
    $stmt = $pdo->prepare("INSERT INTO sequences_fiches (id_sequence, id_fiche) VALUES (?, ?)");
    foreach ($selectionnees as $fiche_id) {
      $stmt->execute([$id_sequence, $fiche_id]);
    }

    $success = "✅ Séquence créée avec succès.";
  } else {
    $erreur = "❌ Remplis un titre et sélectionne au moins une fiche.";
  }
}
?>

<!DOCTYPE html>
<html lang="fr">
<?php $page_title = 'Créer une séquence'; include __DIR__ . '/includes/head.php'; ?>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>

  <div class="container pt-16">
    <h1>Créer une séquence 📚</h1>

    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>
    <?php if ($erreur): ?><p style="color:red;"><?= $erreur ?></p><?php endif; ?>

    <form method="post" class="space-y-6 max-w-xl bg-white rounded-xl shadow p-8 mt-8">
      <label class="block mb-2 font-semibold text-gray-700">Titre de la séquence :
        <input type="text" name="titre" required class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </label>

      <label class="block mb-2 font-semibold text-gray-700">Description :
        <textarea name="description" rows="4" class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
      </label>

      <label class="block mb-2 font-semibold text-gray-700">Fiches à inclure :</label>
      <div class="space-y-2">
        <?php foreach ($fiches as $fiche): ?>
          <label class="flex items-center gap-2 text-gray-700">
            <input type="checkbox" name="fiches[]" value="<?= $fiche['id'] ?>" class="form-checkbox h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
            <span><?= htmlspecialchars($fiche['sequence']) ?> – <?= htmlspecialchars($fiche['seance']) ?></span>
          </label>
        <?php endforeach; ?>
      </div>

      <button type="submit" class="mt-6 px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition flex items-center gap-2"><span>💾</span> Enregistrer la séquence</button>
    </form>
  </div>
</body>
</html>
