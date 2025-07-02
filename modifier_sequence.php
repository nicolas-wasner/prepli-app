<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['utilisateur_id'])) {
  header('Location: /login');
  exit;
}

$id = (int) ($_GET['id'] ?? 0);

// Vérifie que la séquence appartient à l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM sequences WHERE id = ? AND utilisateur_id = ?");
$stmt->execute([$id, $_SESSION['utilisateur_id']]);
$sequence = $stmt->fetch();

if (!$sequence) {
  echo "<p>❌ Séquence introuvable ou non autorisée.</p>";
  exit;
}

// Récupère toutes les fiches de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM fiches WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['utilisateur_id']]);
$fiches = $stmt->fetchAll();

// Récupère les fiches associées à cette séquence
$stmt = $pdo->prepare("SELECT id_fiche FROM sequences_fiches WHERE id_sequence = ?");
$stmt->execute([$id]);
$fiches_associees = array_column($stmt->fetchAll(), 'id_fiche');

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $titre = trim($_POST['titre']);
  $description = trim($_POST['description']);
  $selectionnees = $_POST['fiches'] ?? [];

  if ($titre) {
    // Mise à jour de la séquence
    $stmt = $pdo->prepare("UPDATE sequences SET titre = ?, description = ? WHERE id = ?");
    $stmt->execute([$titre, $description, $id]);

    // Mise à jour des associations
    $pdo->prepare("DELETE FROM sequences_fiches WHERE id_sequence = ?")->execute([$id]);
    $stmt = $pdo->prepare("INSERT INTO sequences_fiches (id_sequence, id_fiche) VALUES (?, ?)");
    foreach ($selectionnees as $fiche_id) {
      $stmt->execute([$id, $fiche_id]);
    }

    $success = "✅ Séquence mise à jour avec succès.";
    // Rafraîchir les fiches associées
    $fiches_associees = $selectionnees;
  }
}
?>

<!DOCTYPE html>
<html lang="fr">
<?php $page_title = 'Modifier séquence'; include __DIR__ . '/includes/head.php'; ?>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>

  <div class="container pt-16">
    <h1>✏️ Modifier la séquence « <?= htmlspecialchars($sequence['titre']) ?> »</h1>

    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>

    <form method="post" class="space-y-6 max-w-xl bg-white rounded-xl shadow p-8 mt-8">
      <label class="block mb-2 font-semibold text-gray-700">Titre :
        <input type="text" name="titre" value="<?= htmlspecialchars($sequence['titre']) ?>" required class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </label>

      <label class="block mb-2 font-semibold text-gray-700">Description :
        <textarea name="description" rows="4" class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($sequence['description']) ?></textarea>
      </label>

      <label class="block mb-2 font-semibold text-gray-700">Fiches associées :</label>
      <div class="space-y-2">
        <?php foreach ($fiches as $fiche): ?>
          <label class="flex items-center gap-2 text-gray-700">
            <input type="checkbox" name="fiches[]" value="<?= $fiche['id'] ?>" <?= in_array($fiche['id'], $fiches_associees) ? 'checked' : '' ?> class="form-checkbox h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
            <span><?= htmlspecialchars($fiche['sequence']) ?> – <?= htmlspecialchars($fiche['seance']) ?></span>
          </label>
        <?php endforeach; ?>
      </div>

      <button type="submit" class="mt-6 px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition flex items-center gap-2"><span>💾</span> Enregistrer les modifications</button>
    </form>
  </div>
</body>
</html>
