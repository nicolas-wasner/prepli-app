<?php
require_once __DIR__ . '/includes/config.php';

session_start();
if (!isset($_SESSION['utilisateur_id'])) {
  header('Location: /login');
  exit;
}

if (!isset($_GET['id'])) {
  header('Location: /fiches');
  exit;
}

$id = (int) $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM fiches WHERE id = ?");
$stmt->execute([$id]);

header('Location: /fiches');
exit;
