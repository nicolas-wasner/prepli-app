<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['utilisateur_id'])) {
  header('Location: login.php');
  exit;
}

$id = (int) ($_GET['id'] ?? 0);

// Vérifie que la fiche appartient à l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM fiches WHERE id = ? AND utilisateur_id = ?");
$stmt->execute([$id, $_SESSION['utilisateur_id']]);
$fiche = $stmt->fetch();

if (!$fiche) {
  exit("❌ Fiche introuvable ou non autorisée.");
}

// Dupliquer la fiche
$stmt = $pdo->prepare("INSERT INTO fiches (
  domaine, niveau, duree, sequence, seance,
  objectifs, competences, prerequis, nom_enseignant,
  materiel, deroulement, consignes, evaluation, differenciation, remarques,
  utilisateur_id
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->execute([
  $fiche['domaine'],
  $fiche['niveau'],
  $fiche['duree'],
  $fiche['sequence'],
  $fiche['seance'] . ' (copie)',
  $fiche['objectifs'],
  $fiche['competences'],
  $fiche['prerequis'],
  $fiche['nom_enseignant'],
  $fiche['materiel'],
  $fiche['deroulement'],
  $fiche['consignes'],
  $fiche['evaluation'],
  $fiche['differenciation'],
  $fiche['remarques'],
  $_SESSION['utilisateur_id']
]);

header('Location: fiches.php');
exit;
