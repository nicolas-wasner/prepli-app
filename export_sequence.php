<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['utilisateur_id'])) {
  header('Location: login.php');
  exit;
}
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_DEPRECATED);

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM sequences WHERE id = ? AND utilisateur_id = ?");
$stmt->execute([$id, $_SESSION['utilisateur_id']]);
$sequence = $stmt->fetch();

if (!$sequence) {
  exit("❌ Séquence introuvable ou non autorisée.");
}

$stmt = $pdo->prepare("
  SELECT f.*
  FROM fiches f
  INNER JOIN sequences_fiches sf ON sf.id_fiche = f.id
  WHERE sf.id_sequence = ?
  ORDER BY f.id ASC
");
$stmt->execute([$id]);
$fiches = $stmt->fetchAll();

if (!$fiches) {
  exit("❌ Aucune fiche liée à cette séquence.");
}

require_once __DIR__ . '/includes/tcpdf/tcpdf.php';

$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 10);

$html = '<h2 style="text-align:center;">Séquence : ' . htmlspecialchars($sequence['titre']) . '</h2>';
$html .= '<p><strong>Description :</strong><br>' . nl2br(htmlspecialchars($sequence['description'])) . '</p>';
$html .= '<hr>';

$champs = [
  'Domaine' => 'domaine',
  'Niveau' => 'niveau',
  'Durée' => 'duree',
  'Séquence' => 'sequence',
  'Séance' => 'seance',
  'Objectifs' => 'objectifs',
  'Compétences' => 'competences',
  'Prérequis' => 'prerequis',
  'Matériel' => 'materiel',
  'Déroulement' => 'deroulement',
  'Consignes' => 'consignes',
  'Évaluation' => 'evaluation',
  'Différenciation' => 'differenciation',
  'Remarques' => 'remarques',
  'Enseignant' => 'nom_enseignant',
];

foreach ($fiches as $fiche) {
  $html .= '<h3>📄 Séance : ' . htmlspecialchars($fiche['seance']) . '</h3>';
  $html .= '<table border="1" cellpadding="6" cellspacing="0" width="100%">';
  foreach ($champs as $label => $key) {
    $value = htmlspecialchars((string) ($fiche[$key] ?? ''));
    $html .= '<tr><th width="25%">' . $label . '</th><td>' . nl2br($value) . '</td></tr>';    
  }
  $html .= '</table><br>';
}

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output("sequence_{$sequence['id']}.pdf", 'D');
exit;
