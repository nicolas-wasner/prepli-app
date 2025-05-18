<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['utilisateur_id'])) {
  header('Location: login.php');
  exit;
}

$id = (int) ($_GET['id'] ?? 0);
$format = $_GET['format'] ?? 'word';

// Vérifie l'accès à la fiche
$stmt = $pdo->prepare("SELECT * FROM fiches WHERE id = ? AND utilisateur_id = ?");
$stmt->execute([$id, $_SESSION['utilisateur_id']]);
$fiche = $stmt->fetch();

if (!$fiche) {
  exit("❌ Fiche introuvable.");
}

if ($format === 'pdf') {
  require_once __DIR__ . '/includes/tcpdf/tcpdf.php';
  $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
  $pdf->SetMargins(15, 20, 15);
  $pdf->AddPage();
  $pdf->SetFont('dejavusans', '', 10);

  $html = '<h2 style="text-align:center;">Fiche de préparation : ' . htmlspecialchars($fiche['seance']) . '</h2><table border="1" cellpadding="6" cellspacing="0" width="100%">';
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

  foreach ($champs as $label => $key) {
    $html .= '<tr><th width="25%">' . $label . '</th><td>' . nl2br(htmlspecialchars($fiche[$key])) . '</td></tr>';
  }

  $html .= '</table>';
  $pdf->writeHTML($html, true, false, true, false, '');
  $pdf->Output("fiche_{$fiche['id']}.pdf", 'D');
  exit;
}

// Export Word
header("Content-type: application/vnd.ms-word");
header("Content-Disposition: attachment;Filename=fiche_{$fiche['id']}.docx");
echo "<html><meta charset='UTF-8'><body>";
echo "<h2 style='text-align:center;'>Fiche de préparation : " . htmlspecialchars($fiche['seance']) . "</h2><table border='1' cellpadding='6' cellspacing='0' width='100%'>";
foreach ($champs as $label => $key) {
  echo "<tr><th width='25%'>$label</th><td>" . nl2br(htmlspecialchars($fiche[$key])) . "</td></tr>";
}
echo "</table></body></html>";
