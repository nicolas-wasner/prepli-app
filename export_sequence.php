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

// Afficher le titre et la description de la séquence en haut de la première page
$html_intro = '<h2 style="text-align:center;">Séquence : ' . htmlspecialchars($sequence['titre']) . '</h2>';
// Ajout des champs séquence
$html_intro .= '<table border="1" cellpadding="4" style="margin-top:10px;">';
$html_intro .= '<tr><td><strong>Objectifs visé(s) :</strong><br>' . nl2br(htmlspecialchars($sequence['objectifs'] ?? '')) . '</td></tr>';
$html_intro .= '<tr><td><strong>Compétence(s) visée(s) :</strong><br>' . nl2br(htmlspecialchars($sequence['competences'] ?? '')) . '</td></tr>';
$html_intro .= '<tr><td><strong>Matériel :</strong><br>' . nl2br(htmlspecialchars($sequence['materiel'] ?? '')) . '</td></tr>';
$html_intro .= '<tr><td><strong>Modalité(s) d\'évaluation :</strong><br>' . nl2br(htmlspecialchars($sequence['evaluation'] ?? '')) . '</td></tr>';
$html_intro .= '<tr><td><strong>Bilan pédagogique et didactique :</strong><br>' . nl2br(htmlspecialchars($sequence['bilan'] ?? '')) . '</td></tr>';
$html_intro .= '<tr><td><strong>Prolongement(s) possible(s) :</strong><br>' . nl2br(htmlspecialchars($sequence['prolongement'] ?? '')) . '</td></tr>';
$html_intro .= '</table><hr>';
$pdf->writeHTML($html_intro, true, false, true, false, '');

foreach ($fiches as $fiche) {
    $pdf->setNomEnseignant($fiche['nom_enseignant'] ?? '');
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, $fiche['seance'], 0, 1, 'C');
    $pdf->Ln(3);
    $pdf->SetFont('helvetica', '', 10);

    // Infos générales (3x3)
    $html = '<table border="1" cellpadding="4">';
    $rows = [
        ['Domaine d\'apprentissage' => $fiche['domaine'], 'Niveau' => $fiche['niveau'], 'Durée totale de la séance' => $fiche['duree']],
    ];
    foreach ($rows as $row) {
        $html .= '<tr>';
        foreach ($row as $label => $val) {
            $html .= '<td><strong>' . htmlspecialchars($label) . '</strong><br>' . nl2br(htmlspecialchars($val)) . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</table><br>';

    // Tableau 2x2
    $html .= '<table border="1" cellpadding="4">
                 <tr><td><strong>Place de la séance dans la séquence :</strong><br>' . nl2br(htmlspecialchars($fiche['sequence'])) . '</td>
                 <td><strong>Titre de la séquence :</strong><br>' . nl2br(htmlspecialchars($fiche['sequence'])) . '</td></tr>
                </table><br>';

    // Pour l'affichage des compétences :
    $competences = json_decode($fiche['competences'] ?? '[]', true);
    $competences_affichage = is_array($competences) ? implode(', ', $competences) : htmlspecialchars($fiche['competences']);

    $html .= '<table border="1" cellpadding="4">
             <tr><td><strong>Compétences visé(s) :</strong><br>' . nl2br(htmlspecialchars($competences_affichage)) . '</td>
             <td><strong>Compétences du SCCCC :</strong><br>' . nl2br(htmlspecialchars($fiche['competences_scccc'] ?? '')) . '</td></tr>
            </table><br>';

    // Prérequis
    $html .= '<table border="1" cellpadding="4"><tr><td><strong>Prérequis :</strong><br>' . nl2br(htmlspecialchars($fiche['prerequis'])) . '</td></tr></table><br>';
    $html .= '<table border="1" cellpadding="4"><tr><td><strong>Critère de réalisation :</strong><br>' . nl2br(htmlspecialchars($fiche['critere_realisation'] ?? '')) . '</td></tr></table><br>';
    $html .= '<table border="1" cellpadding="4"><tr><td><strong>Critère de réussite :</strong><br>' . nl2br(htmlspecialchars($fiche['critere_reussite'] ?? '')) . '</td></tr></table><br>';
    // Objectifs
    $html .= '<table border="1" cellpadding="4"><tr><td><strong>Objectif(s) visé(s) :</strong><br>' . nl2br(htmlspecialchars($fiche['objectifs'])) . '</td></tr></table><br>';
    // AFC
    $html .= '<table border="1" cellpadding="4"><tr><td><strong>AFC :</strong><br>' . nl2br(htmlspecialchars($fiche['afc'] ?? '')) . '</td></tr></table><br>';

    // Déroulement de la séance
    $deroulements = json_decode($fiche['deroulement_json'] ?? '[]', true);
    $html .= '<h3>Déroulement de la séance</h3>';
    $html .= '<table border="1" cellpadding="4"><tr>';
    $headers = ["Phase et durée", "Déroulement", "Consigne", "Rôle de l'enseignant", "Rôle de l'élève", "Différenciation", "Matériel"];
    foreach ($headers as $h) {
        $html .= '<th>' . htmlspecialchars($h) . '</th>';
    }
    $html .= '</tr>';
    if (is_array($deroulements)) {
        foreach ($deroulements as $l) {
            $html .= '<tr>';
            $html .= '<td>' . nl2br(htmlspecialchars($l['phase'] ?? '')) . '</td>';
            $html .= '<td>' . nl2br(htmlspecialchars($l['deroulement'] ?? '')) . '</td>';
            $html .= '<td>' . nl2br(htmlspecialchars($l['consignes'] ?? '')) . '</td>';
            $html .= '<td>' . nl2br(htmlspecialchars($l['role_enseignant'] ?? '')) . '</td>';
            $html .= '<td>' . nl2br(htmlspecialchars($l['role_eleve'] ?? '')) . '</td>';
            $html .= '<td>' . nl2br(htmlspecialchars($l['differenciation'] ?? '')) . '</td>';
            $html .= '<td>' . nl2br(htmlspecialchars($l['materiel'] ?? '')) . '</td>';
            $html .= '</tr>';
        }
    }
    $html .= '</table><br>';

    // Blocs finaux
    $finals = [
        "Modalités d'évaluation" => 'evaluation',
        "Bilan pédagogique et didactique" => 'bilan',
        "Prolongement(s) possible(s)" => 'prolongement',
        "Remédiation(s) éventuelle(s)" => 'remediation',
    ];
    foreach ($finals as $label => $key) {
        $html .= '<table border="1" cellpadding="4"><tr><td><strong>' . htmlspecialchars($label) . ' :</strong><br>' . nl2br(htmlspecialchars($fiche[$key] ?? '')) . '</td></tr></table><br>';
    }

    $pdf->writeHTML($html, true, false, true, false, '');
}

$pdf->Output("sequence_{$sequence['titre']}.pdf", 'D');
exit;
