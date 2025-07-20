<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['utilisateur_id'])) {
  header('Location: /login');
  exit;
}

$id = (int) ($_GET['id'] ?? 0);

// Vérifie que la fiche appartient à l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM fiches WHERE id = ? AND utilisateur_id = ?");
$stmt->execute([$id, $_SESSION['utilisateur_id']]);
$fiche = $stmt->fetch();

if (!$fiche) {
  echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Erreur duplication</title><link rel="stylesheet" href="/public-tailwind.css"></head><body class="font-sans bg-gray-50 min-h-screen flex items-center justify-center"><div class="max-w-lg w-full bg-white rounded-xl shadow-lg p-8 text-center"><h1 class="text-2xl font-bold text-red-600 mb-4">❌ Fiche introuvable ou non autorisée</h1><a href="/fiches" class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Retour à la liste des fiches</a></div></body></html>';
  exit;
}

// Dupliquer la fiche avec la structure actuelle
$stmt = $pdo->prepare("INSERT INTO fiches (
  domaine, niveau, duree, sequence, seance, objectifs, competences, competences_scccc, afc,
  prerequis, critere_realisation, critere_reussite, nom_enseignant, deroulement_json,
  evaluation, bilan, prolongement, remediation,
  utilisateur_id
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->execute([
  $fiche['domaine'],
  $fiche['niveau'],
  $fiche['duree'],
  $fiche['sequence'],
  $fiche['seance'] . ' (copie)',
  $fiche['objectifs'],
  $fiche['competences'],
  $fiche['competences_scccc'],
  $fiche['afc'],
  $fiche['prerequis'],
  $fiche['critere_realisation'],
  $fiche['critere_reussite'],
  $fiche['nom_enseignant'],
  $fiche['deroulement_json'],
  $fiche['evaluation'],
  $fiche['bilan'],
  $fiche['prolongement'],
  $fiche['remediation'],
  $_SESSION['utilisateur_id']
]);

header('Location: /fiches.php?success=1');
exit;
