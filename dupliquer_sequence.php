<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['utilisateur_id'])) {
  header('Location: /login');
  exit;
}

$id = (int) ($_GET['id'] ?? 0);

// Vérifier la propriété de la séquence
$stmt = $pdo->prepare("SELECT * FROM sequences WHERE id = ? AND utilisateur_id = ?");
$stmt->execute([$id, $_SESSION['utilisateur_id']]);
$sequence = $stmt->fetch();

if (!$sequence) {
  exit("❌ Séquence introuvable ou non autorisée.");
}

// Copier la séquence
$titre_copie = $sequence['titre'] . ' (copie)';
$stmt = $pdo->prepare("INSERT INTO sequences (titre, description, utilisateur_id) VALUES (?, ?, ?)");
$stmt->execute([$titre_copie, $sequence['description'], $_SESSION['utilisateur_id']]);
$new_id = $pdo->lastInsertId();

// Copier les liens fiches <-> séquence
$stmt_liens = $pdo->prepare("SELECT id_fiche FROM sequences_fiches WHERE id_sequence = ?");
$stmt_liens->execute([$id]);
$fiches = $stmt_liens->fetchAll();

$insert = $pdo->prepare("INSERT INTO sequences_fiches (id_sequence, id_fiche) VALUES (?, ?)");
foreach ($fiches as $fiche) {
  $insert->execute([$new_id, $fiche['id_fiche']]);
}

// Redirection
header("Location: sequences.php");
exit;
