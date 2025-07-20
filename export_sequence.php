<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['utilisateur_id'])) {
  header('Location: /login');
  exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
  ORDER BY sf.id ASC
");
$stmt->execute([$id]);
$fiches = $stmt->fetchAll();

if (!$fiches) {
  exit("❌ Aucune fiche liée à cette séquence.");
}

require_once __DIR__ . '/includes/tcpdf/tcpdf.php';

// Classe personnalisée pour le pied de page
class MYPDF extends TCPDF {
    protected $nom_enseignant = '';
    public function setNomEnseignant($nom) {
        $this->nom_enseignant = $nom;
    }
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 10);
        if (!empty($this->nom_enseignant)) {
            $this->Cell(0, 10, $this->nom_enseignant, 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }
    }
}

$pdf = new MYPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Prepli');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->setPrintFooter(true);
$pdf->setFooterFont(Array('helvetica', '', 10));
$pdf->setFooterMargin(10);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);

// Afficher le titre et les champs de la séquence en haut de la première page
$html_intro = '<h2 style="text-align:center;">Séquence : ' . htmlspecialchars($sequence['titre']) . '</h2>';
$html_intro .= '<table border="1" cellpadding="4" style="margin-top:10px;">';
$html_intro .= '<tr><td><strong>Objectifs visé(s) :</strong><br>' . nl2br(htmlspecialchars($sequence['objectifs'] ?? '')) . '</td></tr>';
$html_intro .= '<tr><td><strong>Compétence(s) visée(s) :</strong><br>' . nl2br(htmlspecialchars($sequence['competences'] ?? '')) . '</td></tr>';
$html_intro .= '<tr><td><strong>Matériel :</strong><br>' . nl2br(htmlspecialchars($sequence['materiel'] ?? '')) . '</td></tr>';
$html_intro .= '<tr><td><strong>Modalité(s) d\'évaluation :</strong><br>' . nl2br(htmlspecialchars($sequence['evaluation'] ?? '')) . '</td></tr>';
$html_intro .= '<tr><td><strong>Bilan pédagogique et didactique :</strong><br>' . nl2br(htmlspecialchars($sequence['bilan'] ?? '')) . '</td></tr>';
$html_intro .= '<tr><td><strong>Prolongement(s) possible(s) :</strong><br>' . nl2br(htmlspecialchars($sequence['prolongement'] ?? '')) . '</td></tr>';
$html_intro .= '</table><br>';
// Tableau récapitulatif des séances
$html_intro .= '<h3 style="margin-top:20px;">Séances de la séquence</h3>';
$html_intro .= '<table border="1" cellpadding="4" style="width:100%;border-collapse:collapse;">';
$html_intro .= '<tr style="background:#f0f0f0;text-align:center;"><th>N° Séance</th><th>Titre de la séance</th><th>Durée</th><th>Objectifs</th></tr>';
foreach ($fiches as $idx => $fiche) {
  $html_intro .= '<tr>';
  $html_intro .= '<td style="text-align:center;">' . ($idx+1) . '/' . count($fiches) . '</td>';
  $html_intro .= '<td>' . htmlspecialchars($fiche['seance']) . '</td>';
  $html_intro .= '<td>' . htmlspecialchars($fiche['duree']) . '</td>';
  $html_intro .= '<td>' . nl2br(htmlspecialchars($fiche['objectifs'])) . '</td>';
  $html_intro .= '</tr>';
}
$html_intro .= '</table>';
$pdf->writeHTML($html_intro, true, false, true, false, '');
// Ne pas exporter chaque fiche individuellement
$pdf->Output("sequence_{$sequence['titre']}.pdf", 'D');
exit;
