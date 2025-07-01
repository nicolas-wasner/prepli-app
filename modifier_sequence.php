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
<head>
  <meta charset="UTF-8">
  <title>Modifier séquence</title>
  <style>
    .checkbox-list {
      max-height: 200px;
      overflow-y: auto;
      border: 1px solid #ccc;
      padding: 1rem;
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>

  <div class="container">
    <h1>✏️ Modifier la séquence « <?= htmlspecialchars($sequence['titre']) ?> »</h1>

    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>

    <form method="post">
      <label>Titre :
        <input type="text" name="titre" value="<?= htmlspecialchars($sequence['titre']) ?>" required>
      </label>

      <label>Description :
        <textarea name="description" rows="4"><?= htmlspecialchars($sequence['description']) ?></textarea>
      </label>

      <label>Fiches associées :</label>
      <div class="checkbox-list">
        <?php foreach ($fiches as $fiche): ?>
          <label>
            <input type="checkbox" name="fiches[]" value="<?= $fiche['id'] ?>"
              <?= in_array($fiche['id'], $fiches_associees) ? 'checked' : '' ?>>
            <?= htmlspecialchars($fiche['sequence']) ?> – <?= htmlspecialchars($fiche['seance']) ?>
          </label><br>
        <?php endforeach; ?>
      </div>

      <button type="submit" style="margin-top:1rem;">💾 Enregistrer les modifications</button>
    </form>
  </div>
</body>
</html>
