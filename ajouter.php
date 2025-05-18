<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['utilisateur_id'])) {
  header('Location: login.php');
  exit;
}

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $stmt = $pdo->prepare("INSERT INTO fiches (domaine, niveau, duree, sequence, seance, objectifs, competences, prerequis, nom_enseignant, materiel, deroulement, consignes, evaluation, differenciation, remarques, utilisateur_id)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

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
    $_SESSION['utilisateur_id']
  ]);

  $success = "✅ Fiche enregistrée avec succès.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Ajouter une fiche</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>
  <div class="container">
    <h1>Créer une fiche de préparation</h1>
    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>

    <form action="" method="post">
      <input type="text" name="domaine" placeholder="Domaine" required>
      <input type="text" name="niveau" placeholder="Niveau" required>
      <input type="text" name="duree" placeholder="Durée" required>
      <input type="text" name="sequence" placeholder="Séquence" required>
      <input type="text" name="seance" placeholder="Séance" required>
      <textarea name="objectifs" placeholder="Objectifs visés" required></textarea>
      <textarea name="competences" placeholder="Compétences visées" required></textarea>
      <textarea name="prerequis" placeholder="Prérequis" required></textarea>
      <input type="text" name="nom_enseignant" placeholder="Nom de l'enseignant" required>

      <textarea name="materiel" placeholder="Matériel nécessaire"></textarea>
      <textarea name="deroulement" placeholder="Déroulement ou étapes de la séance"></textarea>
      <textarea name="consignes" placeholder="Consignes données aux élèves"></textarea>
      <textarea name="evaluation" placeholder="Modalités d’évaluation ou trace écrite"></textarea>
      <textarea name="differenciation" placeholder="Différenciation possible"></textarea>
      <textarea name="remarques" placeholder="Commentaires / remarques"></textarea>

      <button type="submit">💾 Enregistrer</button>
    </form>
  </div>
</body>
</html>
