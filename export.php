<?php
session_start();
require_once __DIR__ . '/includes/config.php';

ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_DEPRECATED);

if (!isset($_SESSION['utilisateur_id'])) {
  header('Location: login.php');
  exit;
}

$id = (int) ($_GET['id'] ?? 0);
$format = $_GET['format'] ?? 'pdf';

$stmt = $pdo->prepare("SELECT * FROM fiches WHERE id = ? AND utilisateur_id = ?");
$stmt->execute([$id, $_SESSION['utilisateur_id']]);
$fiche = $stmt->fetch();

if (!$fiche) {
  exit("❌ Fiche introuvable.");
}

function champ($f, $cle) {
  return nl2br(htmlspecialchars((string) ($f[$cle] ?? '')));
}

$deroulements = json_decode($fiche['deroulement_json'] ?? '[]', true);

// === PDF EXPORT ===
if ($format === 'pdf') {
  require_once __DIR__ . '/includes/tcpdf/tcpdf.php';

  $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
  $pdf->SetMargins(15, 20, 15);
  $pdf->AddPage();
  $pdf->SetFont('dejavusans', '', 10);

  $html = '<h2 style="text-align:center;">Fiche de préparation de séance</h2>
  <table border="1" cellpadding="5" cellspacing="0" width="100%">
    <tr><th width="25%">Domaine</th><td>' . champ($fiche, 'domaine') . '</td></tr>
    <tr><th>Niveau</th><td>' . champ($fiche, 'niveau') . '</td></tr>
    <tr><th>Durée</th><td>' . champ($fiche, 'duree') . '</td></tr>
    <tr><th>Séquence</th><td>' . champ($fiche, 'sequence') . '</td></tr>
    <tr><th>Séance</th><td>' . champ($fiche, 'seance') . '</td></tr>
    <tr><th>Objectifs</th><td>' . champ($fiche, 'objectifs') . '</td></tr>
    <tr><th>Compétences</th><td>' . champ($fiche, 'competences') . '</td></tr>
    <tr><th>Prérequis</th><td>' . champ($fiche, 'prerequis') . '</td></tr>
  </table><br>';

  $html .= '<h3>Déroulement de la séance</h3>
  <table border="1" cellpadding="4" cellspacing="0" width="100%">
    <thead>
      <tr style="background-color:#f0f0f0;">
        <th>Phase & durée</th><th>Déroulement</th><th>Consigne</th>
        <th>Rôle enseignant</th><th>Rôle élève</th><th>Différenciation</th><th>Matériel</th>
      </tr>
    </thead><tbody>';

  foreach ($deroulements as $ligne) {
    $html .= '<tr>';
    foreach (['phase','deroulement','consignes','role_enseignant','role_eleve','differenciation','materiel'] as $cle) {
      $html .= '<td>' . nl2br(htmlspecialchars((string) ($ligne[$cle] ?? ''))) . '</td>';
    }
    $html .= '</tr>';
  }

  $html .= '</tbody></table><br>';

  $html .= '<p><strong>Nom de l’enseignant :</strong><br>' . champ($fiche, 'nom_enseignant') . '</p>';

  $pdf->writeHTML($html, true, false, true, false, '');
  $pdf->Output("fiche_{$fiche['id']}.pdf", 'D');
  exit;
}

// === WORD EXPORT ===
if ($format === 'word') {
  require_once __DIR__ . '/includes/phpword/bootstrap.php';
  use PhpOffice\PhpWord\PhpWord;
  use PhpOffice\PhpWord\IOFactory;

  $word = new PhpWord();
  $section = $word->addSection(['orientation' => 'landscape']);
  $word->addTableStyle('MainTable', ['borderSize' => 6, 'borderColor' => '999999', 'cellMargin' => 80], ['bgColor' => 'f0f0f0']);

  $section->addText("Fiche de préparation de séance", ['bold' => true, 'size' => 14], ['alignment' => 'center']);
  $section->addTextBreak(1);

  $table = $section->addTable('MainTable');
  $infos = [
    'Domaine' => 'domaine', 'Niveau' => 'niveau', 'Durée' => 'duree',
    'Séquence' => 'sequence', 'Séance' => 'seance',
    'Objectifs' => 'objectifs', 'Compétences' => 'competences', 'Prérequis' => 'prerequis'
  ];
  foreach ($infos as $label => $cle) {
    $table->addRow();
    $table->addCell(3000)->addText($label, ['bold' => true]);
    $table->addCell(12000)->addText((string) ($fiche[$cle] ?? ''));
  }

  $section->addTextBreak(1);
  $section->addText("Déroulement de la séance", ['bold' => true, 'size' => 12]);

  $table = $section->addTable('MainTable');
  $cols = ['Phase & durée', 'Déroulement', 'Consigne', 'Rôle enseignant', 'Rôle élève', 'Différenciation', 'Matériel'];
  $keys = ['phase', 'deroulement', 'consignes', 'role_enseignant', 'role_eleve', 'differenciation', 'materiel'];

  $table->addRow();
  foreach ($cols as $col) $table->addCell()->addText($col, ['bold' => true]);

  foreach ($deroulements as $ligne) {
    $table->addRow();
    foreach ($keys as $k) {
      $table->addCell()->addText((string) ($ligne[$k] ?? ''));
    }
  }

  $section->addTextBreak(1);
  $section->addText("Nom de l’enseignant :", ['bold' => true]);
  $section->addText((string) $fiche['nom_enseignant']);

  header("Content-Description: File Transfer");
  header('Content-Disposition: attachment; filename="fiche_' . $fiche['id'] . '.docx"');
  header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');

  $writer = IOFactory::createWriter($word, 'Word2007');
  $writer->save("php://output");
  exit;
}
