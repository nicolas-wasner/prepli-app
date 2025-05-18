<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['utilisateur_id'])) {
  header('Location: login.php');
  exit;
}

$id = (int) ($_GET['id'] ?? 0);

// Vérifie que la fiche appartient à l'utilisateur connecté
$stmt = $pdo->prepare("SELECT * FROM fiches WHERE id = ? AND utilisateur_id = ?");
$stmt->execute([$id, $_SESSION['utilisateur_id']]);
$fiche = $stmt->fetch();

if (!$fiche) {
  echo "<p>❌ Fiche introuvable ou non autorisée.</p>";
  exit;
}

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $stmt = $pdo->prepare("UPDATE fiches SET
    domaine = ?, niveau = ?, duree = ?, sequence = ?, seance = ?,
    objectifs = ?, competences = ?, prerequis = ?, nom_enseignant = ?,
    materiel = ?, deroulement = ?, consignes = ?, evaluation = ?, differenciation = ?, remarques = ?
    WHERE id = ? AND utilisateur_id = ?");

  $stmt->execute([
    $_POST['domaine'],
    $_POST['niveau'],
    $_POST['duree'],
    $_POST['sequence'],
    $_POST['seance'],
    $_POST['objectifs'],
    $_POST['competences'],
    $_POST['prerequis'],
    $_POST['nom_enseignant'],
    $_POST['materiel'],
    $_POST['deroulement'],
    $_POST['consignes'],
    $_POST['evaluation'],
    $_POST['differenciation'],
    $_POST['remarques'],
    $id,
    $_SESSION['utilisateur_id']
  ]);

  $success = "✅ Fiche mise à jour avec succès.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Modifier fiche</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>
  <div class="container">
    <h1>✏️ Modifier la fiche « <?= htmlspecialchars($fiche['seance']) ?> »</h1>

    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>

    <form method="post">
      <input type="text" name="domaine" placeholder="Domaine" value="<?= htmlspecialchars($fiche['domaine']) ?>" required>
      <input type="text" name="niveau" placeholder="Niveau" value="<?= htmlspecialchars($fiche['niveau']) ?>" required>
      <input type="text" name="duree" placeholder="Durée" value="<?= htmlspecialchars($fiche['duree']) ?>" required>
      <input type="text" name="sequence" placeholder="Séquence" value="<?= htmlspecialchars($fiche['sequence']) ?>" required>
      <input type="text" name="seance" placeholder="Séance" value="<?= htmlspecialchars($fiche['seance']) ?>" required>
      <textarea name="objectifs" placeholder="Objectifs visés"><?= htmlspecialchars($fiche['objectifs']) ?></textarea>
      <textarea name="competences" placeholder="Compétences visées"><?= htmlspecialchars($fiche['competences']) ?></textarea>
      <textarea name="prerequis" placeholder="Prérequis"><?= htmlspecialchars($fiche['prerequis']) ?></textarea>
      <input type="text" name="nom_enseignant" placeholder="Nom de l'enseignant" value="<?= htmlspecialchars($fiche['nom_enseignant']) ?>">

      <textarea name="materiel" placeholder="Matériel nécessaire"><?= htmlspecialchars($fiche['materiel']) ?></textarea>
      <textarea name="deroulement" placeholder="Déroulement ou étapes"><?= htmlspecialchars($fiche['deroulement']) ?></textarea>
      <textarea name="consignes" placeholder="Consignes données aux élèves"><?= htmlspecialchars($fiche['consignes']) ?></textarea>
      <textarea name="evaluation" placeholder="Évaluation ou trace écrite"><?= htmlspecialchars($fiche['evaluation']) ?></textarea>
      <textarea name="differenciation" placeholder="Différenciation possible"><?= htmlspecialchars($fiche['differenciation']) ?></textarea>
      <textarea name="remarques" placeholder="Commentaires / remarques"><?= htmlspecialchars($fiche['remarques']) ?></textarea>

      <button type="submit">💾 Enregistrer les modifi
