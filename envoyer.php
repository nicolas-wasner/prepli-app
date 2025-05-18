<?php
require_once __DIR__ . '/includes/config.php';
session_start();
if (!isset($_SESSION['utilisateur_id'])) {
  header('Location: login.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $stmt = $pdo->prepare("INSERT INTO fiches (domaine, niveau, duree, sequence, seance, objectifs, competences, prerequis, nom_enseignant)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->execute([
    $_POST['domaine'],
    $_POST['niveau'],
    $_POST['duree'],
    $_POST['sequence'],
    $_POST['seance'],
    $_POST['objectifs'],
    $_POST['competences'],
    $_POST['prerequis'],
    $_POST['nom_enseignant']
  ]);

  echo "<p>✅ Fiche enregistrée avec succès !</p><a href='index.php'>Retour</a>";
} else {
  header('Location: index.php');
  exit;
}