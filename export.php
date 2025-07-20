<?php
session_start();
require_once __DIR__ . '/includes/config.php';

//ini_set('display_errors', 0);
//error_reporting(E_ALL & ~E_DEPRECATED);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/includes/phpword/src/PhpWord/Autoloader.php';
\PhpOffice\PhpWord\Autoloader::register();
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

$id = (int) ($_GET['id'] ?? 0);
$format = $_GET['format'] ?? 'pdf';

$stmt = $pdo->prepare("SELECT * FROM fiches WHERE id = ? AND utilisateur_id = ?");
$stmt->execute([$id, $_SESSION['utilisateur_id']]);
$fiche = $stmt->fetch();

if (!$fiche) {
  echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Erreur export</title><link rel="stylesheet" href="/public-tailwind.css"></head><body class="font-sans bg-gray-50 min-h-screen flex items-center justify-center"><div class="max-w-lg w-full bg-white rounded-xl shadow-lg p-8 text-center"><h1 class="text-2xl font-bold text-red-600 mb-4">❌ Fiche introuvable</h1><p class="mb-6">La fiche demandée n\'existe pas ou vous n\'y avez pas accès.</p><a href="/fiches" class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Retour à la liste des fiches</a></div></body></html>';
  exit;
}
// Vérification des champs obligatoires
$champs_obligatoires = ['domaine','niveau','duree','sequence','seance','objectifs','competences','prerequis','critere_realisation','critere_reussite','evaluation','nom_enseignant','deroulement_json'];
foreach ($champs_obligatoires as $champ) {
  if (empty($fiche[$champ]) || (is_string($fiche[$champ]) && trim($fiche[$champ]) === '')) {
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Erreur export</title><link rel="stylesheet" href="/public-tailwind.css"></head><body class="font-sans bg-gray-50 min-h-screen flex items-center justify-center"><div class="max-w-lg w-full bg-white rounded-xl shadow-lg p-8 text-center"><h1 class="text-2xl font-bold text-red-600 mb-4">❌ Export impossible</h1><p class="mb-6">Certains champs obligatoires sont manquants ou incomplets dans cette fiche.<br><span class="text-sm text-gray-600">Champ manquant : <b>' . htmlspecialchars($champ) . '</b></span></p><a href="/modifier/' . (int)$fiche['id'] . '" class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Retour à la modification</a></div></body></html>';
    exit;
  }
}

function champ($f, $cle) {
  return nl2br(htmlspecialchars((string) ($f[$cle] ?? '')));
}
$deroulements = json_decode($fiche['deroulement_json'] ?? '[]', true);

// Pour l'affichage des compétences :
$competences = json_decode($fiche['competences'] ?? '[]', true);
$competences_affichage = is_array($competences) ? implode(', ', $competences) : htmlspecialchars($fiche['competences']);

// PDF
if ($format === 'pdf') {
    require_once __DIR__ . '/includes/tcpdf/tcpdf.php';

    // Extend TCPDF to customize the footer
    class MYPDF extends TCPDF {
        protected $nom_enseignant = '';

        public function setNomEnseignant($nom) {
            $this->nom_enseignant = $nom;
        }

        // Page footer
        public function Footer() {
            // Position at 15 mm from bottom
            $this->SetY(-15);
            // Set font
            $this->SetFont('helvetica', 'I', 10);
            // Teacher name
            if (!empty($this->nom_enseignant)) {
                $this->Cell(0, 10, $this->nom_enseignant, 0, false, 'C', 0, '', 0, false, 'T', 'M');
            }
        }
    }

    // Création du PDF
    $pdf = new MYPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Prepli');
    $pdf->SetTitle('Séance : ' . $fiche['seance']);
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(TRUE, 10);

    // Configuration du footer avec le nom de l'enseignant
    $pdf->setPrintFooter(true);
    $pdf->setFooterFont(Array('helvetica', '', 10));
    $pdf->setFooterMargin(10);
    // Définir le nom de l'enseignant dans le footer
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
//        ['Place de la séance dans la séquence' => $fiche['sequence'], 'Titre de la séquence' => $fiche['sequence']],
//        ['Objectif(s) visé(s)' => $fiche['objectifs'], 'Compétence(s) visée(s)' => $fiche['competences']],
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

    // Tableau 2x2
    $html .= '<table border="1" cellpadding="4">
                 <tr><td><strong>Compétences visé(s) :</strong><br>' . nl2br(htmlspecialchars($competences_affichage)) . '</td>
                 <td><strong>Compétences du SCCCC :</strong><br>' . nl2br(htmlspecialchars($fiche['competences_scccc'] ?? '')) . '</td></tr>
                </table><br>';

    // Prérequis
    $html .= '<table border="1" cellpadding="4"><tr><td><strong>Prérequis :</strong><br>' . nl2br(htmlspecialchars($fiche['prerequis'])) . '</td></tr></table><br>';

     // Tableau 2x2
     $html .= '<table border="1" cellpadding="4">
     <tr><td><strong>Critère de réalisation :</strong><br>' . nl2br(htmlspecialchars($fiche['critere_realisation'] ?? '')) . '</td>
     <td><strong>Critère de réussite :</strong><br>' . nl2br(htmlspecialchars($fiche['critere_reussite'] ?? '')) . '</td></tr>
    </table><br>';
    //$html .= '<table border="1" cellpadding="4"><tr><td><strong>Critère de réalisation :</strong><br>' . nl2br(htmlspecialchars($fiche['critere_realisation'] ?? '')) . '</td></tr></table><br>';
    //$html .= '<table border="1" cellpadding="4"><tr><td><strong>Critère de réussite :</strong><br>' . nl2br(htmlspecialchars($fiche['critere_reussite'] ?? '')) . '</td></tr></table><br>';

    // Objectifs
    $html .= '<table border="1" cellpadding="4"><tr><td><strong>Objectif(s) visé(s) :</strong><br>' . nl2br(htmlspecialchars($fiche['objectifs'])) . '</td></tr></table><br>';

    // AFC
    $html .= '<table border="1" cellpadding="4"><tr><td><strong>AFC :</strong><br>' . nl2br(htmlspecialchars($fiche['afc'])) . '</td></tr></table><br>';

    // Déroulement de la séance
    $html .= '<h3>Déroulement de la séance</h3>';
    $html .= '<table border="1" cellpadding="4"><tr>';
    $headers = ["Phase et durée", "Déroulement", "Consigne", "Rôle de l'enseignant", "Rôle de l'élève", "Différenciation", "Matériel"];
    foreach ($headers as $h) {
        $html .= '<th>' . htmlspecialchars($h) . '</th>';
    }
    $html .= '</tr>';
    foreach ($deroulements as $l) {
        $html .= '<tr>';
        $html .= '<td>' . nl2br(htmlspecialchars($l['phase'])) . '</td>';
        $html .= '<td>' . nl2br(htmlspecialchars($l['deroulement'])) . '</td>';
        $html .= '<td>' . nl2br(htmlspecialchars($l['consignes'])) . '</td>';
        $html .= '<td>' . nl2br(htmlspecialchars($l['role_enseignant'])) . '</td>';
        $html .= '<td>' . nl2br(htmlspecialchars($l['role_eleve'])) . '</td>';
        $html .= '<td>' . nl2br(htmlspecialchars($l['differenciation'])) . '</td>';
        $html .= '<td>' . nl2br(htmlspecialchars($l['materiel'])) . '</td>';
        $html .= '</tr>';
    }
    $html .= '</table><br>';

    // Blocs finaux
    $finals = [
        "Modalités d\'évaluation" => 'evaluation',
        "Bilan pédagogique et didactique" => 'bilan',
        "Prolongement(s) possible(s)" => 'prolongement',
        "Remédiation(s) éventuelle(s)" => 'remediation',
    ];
    foreach ($finals as $label => $key) {
        $html .= '<table border="1" cellpadding="4"><tr><td><strong>' . htmlspecialchars($label) . ' :</strong><br>' . nl2br(htmlspecialchars($fiche[$key])) . '</td></tr></table><br>';
    }

    // Rendu HTML dans le PDF
    $pdf->writeHTML($html, true, false, true, false, '');

    // Export
    $pdf->Output("fiche_{$fiche['id']}.pdf", 'D');
    exit;
}


/* WORD
if ($format === 'word') {

    $word = new PhpWord();
    $section = $word->addSection(['orientation' => 'landscape']);

    $section->addText("Fiche de préparation de séance", ['bold' => true, 'size' => 14], ['alignment' => 'center']);
    $section->addTextBreak(1);

    // Tableau 3x3
    $table = $section->addTable(['borderSize' => 6]);
    $triples = [
        ['Domaine d\'apprentissage', 'domaine', 'Niveau', 'niveau', 'Durée totale de la séance', 'duree'],
        ['Place de la séance dans la séquence', 'sequence', 'Titre de la séquence', 'sequence', 'Titre de la séance', 'seance'],
        ['Objectif(s) visé(s)', 'objectifs', 'Compétence(s) visée(s)', 'competences', 'Prérequis', 'prerequis']
    ];
    foreach ($triples as $row) {
        $table->addRow();
        for ($i = 0; $i < 3; $i++) {
            $table->addCell()->addText($row[$i * 2] . " :", ['bold' => true]);
            $table->addCell()->addText($fiche[$row[$i * 2 + 1]]);
        }
    }

    // Prérequis
    $section->addTextBreak();
    $section->addText("AFC :", ['bold' => true]);
    $section->addText($fiche['afc']);

    // Déroulement
    $section->addTextBreak();
    $section->addText("Déroulement de la séance", ['bold' => true]);
    $table2 = $section->addTable(['borderSize' => 6]);
    $headers = ["Phase et durée", "Déroulement", "Consigne", "Rôle de l'enseignant", "Rôle de l'élève", "Différenciation", "Matériel"];
    $table2->addRow();
    foreach ($headers as $h) $table2->addCell()->addText($h, ['bold' => true]);

    foreach ($deroulements as $d) {
        $table2->addRow();
        $table2->addCell()->addText($d['phase']);
        $table2->addCell()->addText($d['deroulement']);
        $table2->addCell()->addText($d['consignes']);
        $table2->addCell()->addText($d['role_enseignant']);
        $table2->addCell()->addText($d['role_eleve']);
        $table2->addCell()->addText($d['differenciation']);
        $table2->addCell()->addText($d['materiel']);
    }

    // Blocs finaux
    $section->addTextBreak(1);
    $f = [
        "Modalités d\'évaluation" => 'evaluation',
        "Bilan pédagogique et didactique" => 'bilan',
        "Prolongement(s) possible(s)" => 'prolongement',
        "Remédiation(s) éventuelle(s)" => 'remediation',
    ];
    foreach ($f as $label => $key) {
        $section->addTextBreak();
        $section->addText($label . " :", ['bold' => true]);
        $section->addText($fiche[$key]);
    }

    $temp = tempnam(sys_get_temp_dir(), 'fiche') . '.docx';
    IOFactory::createWriter($word, 'Word2007')->save($temp);

    header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
    header("Content-Disposition: attachment; filename=fiche_{$fiche['id']}.docx");
    readfile($temp);
    unlink($temp);
    exit;
}
*/
?>
