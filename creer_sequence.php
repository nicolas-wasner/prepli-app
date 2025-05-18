<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['utilisateur_id'])) {
  header('Location: login.php');
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
<head>
  <meta charset="UTF-8">
  <title>Créer une séquence</title>
  <link rel="stylesheet" href="style.css">
  <style>
  .checkbox-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #ccc;
    padding: 1rem;
  }

  .checkbox-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1rem;
  }

  .checkbox-item input[type="checkbox"] {
    transform: scale(1.2);
    margin: 0;
  }
</style>

</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>

  <div class="container">
    <h1>Créer une séquence 📚</h1>

    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>
    <?php if ($erreur): ?><p style="color:red;"><?= $erreur ?></p><?php endif; ?>

    <form method="post" class="form-sequence">
      <label>Titre de la séquence :
        <input type="text" name="titre" required>
      </label>

      <label>Description :
        <textarea name="description" rows="4"></textarea>
      </label>

      <label>Fiches à inclure :</label>
      <div class="checkbox-list">
  <?php foreach ($fiches as $fiche): ?>
    <label class="checkbox-item">
      <input type="checkbox" name="fiches[]" value="<?= $fiche['id'] ?>">
      <?= htmlspecialchars($fiche['sequence']) ?> – <?= htmlspecialchars($fiche['seance']) ?>
    </label>
  <?php endforeach; ?>
</div>


      <button type="submit" style="margin-top:1rem;">💾 Enregistrer la séquence</button>
    </form>
  </div>
</body>
</html>
