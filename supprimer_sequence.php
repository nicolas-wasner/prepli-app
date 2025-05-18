<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['utilisateur_id'])) {
  header('Location: login.php');
  exit;
}

$id = (int) ($_GET['id'] ?? 0);

// Vérifie que la séquence appartient bien à l'utilisateur connecté
$stmt = $pdo->prepare("SELECT id FROM sequences WHERE id = ? AND utilisateur_id = ?");
$stmt->execute([$id, $_SESSION['utilisateur_id']]);
$sequence = $stmt->fetch();

if (!$sequence) {
  echo "<p>❌ Séquence introuvable ou non autorisée.</p>";
  exit;
}

// Supprimer les liens avec les fiches
$stmt = $pdo->prepare("DELETE FROM sequences_fiches WHERE id_sequence = ?");
$stmt->execute([$id]);

// Supprimer la séquence elle-même
$stmt = $pdo->prepare("DELETE FROM sequences WHERE id = ?");
$stmt->execute([$id]);

header('Location: sequences.php');
exit;
