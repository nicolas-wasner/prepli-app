<?php
session_start();
require_once __DIR__ . '/includes/config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$success = null;
$error = null;

if (!isset($_SESSION['utilisateur_id'])) {
  header('Location: /login');
  exit;
}

$id = (int) ($_GET['id'] ?? 0);

// Récupération de la fiche
$stmt = $pdo->prepare("SELECT * FROM fiches WHERE id = ? AND utilisateur_id = ?");
$stmt->execute([$id, $_SESSION['utilisateur_id']]);
$fiche = $stmt->fetch();

if (!$fiche) {
  echo "<p>❌ Fiche introuvable ou non autorisée.</p>";
  exit;
}

// Traitement de la mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $deroulement_json = $_POST['deroulement_json'] ?? '[]';
  $test = json_decode($deroulement_json, true);
  if (json_last_error() !== JSON_ERROR_NONE) {
    exit("❌ Erreur JSON : " . json_last_error_msg());
  }

  $afc = isset($_POST['afc']) ? json_encode($_POST['afc']) : '';
  $competences_scccc = isset($_POST['competences_scccc']) ? json_encode($_POST['competences_scccc']) : '[]';

  $stmt = $pdo->prepare("UPDATE fiches SET
    domaine = ?, niveau = ?, duree = ?, sequence = ?, seance = ?,
    objectifs = ?, competences = ?, competences_scccc = ?, prerequis = ?, critere_realisation = ?, critere_reussite = ?, afc = ?,
    evaluation = ?, bilan = ?, prolongement = ?, remediation = ?,
    nom_enseignant = ?, deroulement_json = ?
    WHERE id = ? AND utilisateur_id = ?");

  $stmt->execute([
    $_POST['domaine'], $_POST['niveau'], $_POST['duree'], $_POST['sequence'], $_POST['seance'],
    $_POST['objectifs'], json_encode($_POST['competences']), $competences_scccc, $_POST['prerequis'], $_POST['critere_realisation'], $_POST['critere_reussite'], $afc,
    $_POST['evaluation'], $_POST['bilan'], $_POST['prolongement'], $_POST['remediation'],
    $_POST['nom_enseignant'], $deroulement_json,
    $id, $_SESSION['utilisateur_id']
  ]);

  $success = "✅ Fiche mise à jour avec succès.";
  header('Location: /fiches.php?success=1');
  exit;
}

// Pour affichage
$deroulement_data = json_decode($fiche['deroulement_json'] ?? '[]', true);
?>

<!DOCTYPE html>
<html lang="fr">
<?php $page_title = 'Modifier fiche'; include __DIR__ . '/includes/head.php'; ?>
<body class="font-sans bg-gray-50 min-h-screen">
  <?php include __DIR__ . '/includes/header.php'; ?>
  <main class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg p-8 my-10 pt-20">
    <h1 class="text-3xl md:text-4xl font-bold text-blue-700 mb-8 text-center">Modifier la fiche</h1>
    <?php if ($success): ?>
      <div class="mb-6 rounded bg-green-50 border border-green-200 text-green-800 px-4 py-3 flex items-center gap-2">
        <span>✅</span> <span><?= $success ?></span>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="mb-6 rounded bg-red-50 border border-red-200 text-red-800 px-4 py-3 flex items-center gap-2">
        <span>❌</span> <span><?= $error ?></span>
      </div>
    <?php endif; ?>
    <form method="post" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Domaine</label>
        <select name="domaine" required class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500">
          <option value="">-- Sélectionnez un domaine d'apprentissage --</option>
          <optgroup label="École maternelle (cycle 1)">
            <option value="Mobiliser le langage dans toutes ses dimensions" <?= $fiche['domaine'] === 'Mobiliser le langage dans toutes ses dimensions' ? 'selected' : '' ?>>Mobiliser le langage dans toutes ses dimensions</option>
            <option value="Agir, s'exprimer, comprendre à travers l'activité physique" <?= $fiche['domaine'] === 'Agir, s\'exprimer, comprendre à travers l\'activité physique' ? 'selected' : '' ?>>Agir, s'exprimer, comprendre à travers l'activité physique</option>
            <option value="Agir, s'exprimer, comprendre à travers les activités artistiques" <?= $fiche['domaine'] === 'Agir, s\'exprimer, comprendre à travers les activités artistiques' ? 'selected' : '' ?>>Agir, s'exprimer, comprendre à travers les activités artistiques</option>
            <option value="Construire les premiers outils pour structurer sa pensée" <?= $fiche['domaine'] === 'Construire les premiers outils pour structurer sa pensée' ? 'selected' : '' ?>>Construire les premiers outils pour structurer sa pensée</option>
            <option value="Explorer le monde" <?= $fiche['domaine'] === 'Explorer le monde' ? 'selected' : '' ?>>Explorer le monde</option>
          </optgroup>
          <optgroup label="École élémentaire (cycle 2 à 3)">
            <option value="Les langages pour penser et communiquer" <?= $fiche['domaine'] === 'Les langages pour penser et communiquer' ? 'selected' : '' ?>>Les langages pour penser et communiquer</option>
            <option value="Les méthodes et outils pour apprendre" <?= $fiche['domaine'] === 'Les méthodes et outils pour apprendre' ? 'selected' : '' ?>>Les méthodes et outils pour apprendre</option>
            <option value="La formation de la personne et du citoyen" <?= $fiche['domaine'] === 'La formation de la personne et du citoyen' ? 'selected' : '' ?>>La formation de la personne et du citoyen</option>
            <option value="Les systèmes naturels et techniques" <?= $fiche['domaine'] === 'Les systèmes naturels et techniques' ? 'selected' : '' ?>>Les systèmes naturels et techniques</option>
            <option value="Les représentations du monde et l'activité humaine" <?= $fiche['domaine'] === 'Les représentations du monde et l\'activité humaine' ? 'selected' : '' ?>>Les représentations du monde et l'activité humaine</option>
          </optgroup>
          <optgroup label="Transversal (tout cycle)">
            <option value="Langues vivantes étrangères et régionales" <?= $fiche['domaine'] === 'Langues vivantes étrangères et régionales' ? 'selected' : '' ?>>Langues vivantes étrangères et régionales</option>
            <option value="Éducation au développement durable" <?= $fiche['domaine'] === 'Éducation au développement durable' ? 'selected' : '' ?>>Éducation au développement durable</option>
            <option value="Éducation artistique et culturelle" <?= $fiche['domaine'] === 'Éducation artistique et culturelle' ? 'selected' : '' ?>>Éducation artistique et culturelle</option>
          </optgroup>
        </select>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Niveau</label>
          <input type="text" name="niveau" placeholder="Cycle (Niveau de classe)" value="<?= htmlspecialchars($fiche['niveau']) ?>" required class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Durée</label>
          <input type="text" name="duree" placeholder="Durée" value="<?= htmlspecialchars($fiche['duree']) ?>" required class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500">
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Séquence</label>
          <input type="text" name="sequence" placeholder="Séquence" value="<?= htmlspecialchars($fiche['sequence']) ?>" required class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Séance</label>
          <input type="text" name="seance" placeholder="Séance" value="<?= htmlspecialchars($fiche['seance']) ?>" required class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500">
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Objectifs visés</label>
        <textarea name="objectifs" placeholder="Objectifs visés" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($fiche['objectifs']) ?></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Compétence(s) visée(s)</label>
        <div id="competences_list"></div>
        <button type="button" id="add_competence_btn" class="mb-4 px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200">➕ Ajouter une compétence</button>
      </div>
      <div id="competences_scccc_container" style="display: none;">
        <label class="block text-sm font-medium text-gray-700 mb-1">Compétence(s) du SCCCC</label>
        <div id="competences_scccc_list"></div>
        <button type="button" id="add_competence_scccc_btn" class="mb-4 px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200">➕ Ajouter une compétence SCCCC</button>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">AFC <span class="text-xs text-gray-500">(optionnel)</span></label>
        <div id="afc_list"></div>
        <button type="button" id="add_afc_btn" class="mb-4 px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200">➕ Ajouter un attendu</button>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Prérequis <span class="text-red-500">*</span></label>
          <textarea name="prerequis" placeholder="Prérequis" required class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($fiche['prerequis']) ?></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Critère de réalisation <span class="text-red-500">*</span></label>
          <textarea name="critere_realisation" placeholder="Comment je fais pour réussir" required class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($fiche['critere_realisation'] ?? '') ?></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Critère de réussite <span class="text-red-500">*</span></label>
          <textarea name="critere_reussite" placeholder="Comment je sais que j'ai réussi" required class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($fiche['critere_reussite'] ?? '') ?></textarea>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Modalités d'évaluation <span class="text-red-500">*</span></label>
          <textarea name="evaluation" placeholder="Modalités d'évaluation" required class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($fiche['evaluation']) ?></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Bilan pédagogique et didactique <span class="text-xs text-gray-500">(optionnel)</span></label>
          <textarea name="bilan" placeholder="Bilan pédagogique et didactique" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($fiche['bilan']) ?></textarea>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Prolongement(s) possible(s) <span class="text-xs text-gray-500">(optionnel)</span></label>
          <textarea name="prolongement" placeholder="Prolongement(s) possible(s)" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($fiche['prolongement']) ?></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Remédiation(s) éventuelle(s) <span class="text-xs text-gray-500">(optionnel)</span></label>
          <textarea name="remediation" placeholder="Remédiation(s) éventuelle(s)" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($fiche['remediation']) ?></textarea>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nom de l'enseignant <span class="text-red-500">*</span></label>
        <input type="text" name="nom_enseignant" placeholder="Nom de l'enseignant" value="<?= htmlspecialchars($fiche['nom_enseignant']) ?>" required class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500">
      </div>
      <hr class="my-6 border-gray-200">
      <h3 class="text-lg font-bold text-gray-800 mb-2">Déroulement de la séance</h3>
      <div id="deroulement-list" class="space-y-4 mb-4"></div>
      <div class="flex justify-center mb-8">
        <button type="button" onclick="addDeroulementRow()" class="px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 transition">➕ Ajouter une ligne</button>
      </div>
      <input type="hidden" name="deroulement_json" id="deroulement_json">
      <div class="flex justify-end mt-8">
        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition">💾 Enregistrer la fiche</button>
      </div>
    </form>
  </main>

  <script>
    var initialCompetencesScccc = <?php echo json_encode(json_decode($fiche['competences_scccc'] ?? '[]', true) ?: []); ?>;
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // --- Compétences dynamiques ---
      const competencesParDomaine = {
        "Mobiliser le langage dans toutes ses dimensions": [
          "Oser entrer en communication",
          "Comprendre et apprendre",
          "Échanger et réfléchir avec les autres",
          "Se préparer à apprendre à lire",
          "Développer la conscience phonologique",
          "Comprendre le principe alphabétique",
          "Produire des discours variés",
          "Découvrir les fonctions de l'écrit",
          "Commencer à produire des écrits",
          "Se familiariser avec l'écrit dans toutes ses formes"
        ],
        "Agir, s'exprimer, comprendre à travers l'activité physique": [
          "Agir dans des environnements variés",
          "Adapter ses déplacements à des contraintes",
          "Coopérer et s'opposer individuellement ou collectivement",
          "Exprimer des intentions par le geste",
          "Apprendre à respecter des règles",
          "Développer sa motricité fine et globale",
          "Se repérer dans l'espace avec son corps"
        ],
        "Agir, s'exprimer, comprendre à travers les activités artistiques": [
          "Expérimenter les matériaux, les outils, les supports",
          "Créer des productions plastiques et visuelles",
          "Observer et décrire des œuvres",
          "Explorer des univers sonores",
          "Participer à des jeux vocaux et corporels",
          "Chanter seul et en groupe",
          "Jouer avec sa voix et son corps",
          "Imaginer, inventer, interpréter"
        ],
        "Construire les premiers outils pour structurer sa pensée": [
          "Dénombrer des quantités",
          "Associer un nombre à une quantité",
          "Utiliser le comptage pour résoudre des problèmes",
          "Comprendre les nombres comme positions",
          "Utiliser les premiers symboles mathématiques",
          "Reproduire, compléter, créer des suites logiques",
          "Reconnaître et nommer des formes",
          "Comparer, classer des objets selon des critères",
          "Se repérer dans le temps court (journée, semaine)"
        ],
        "Explorer le monde": [
          "Découvrir les objets, matières, phénomènes du vivant",
          "Utiliser ses sens pour observer",
          "Identifier les caractéristiques du vivant et des objets",
          "Se repérer dans le temps (jours, mois, saisons)",
          "Se repérer dans l'espace (école, classe, parcours)",
          "Découvrir l'usage d'objets techniques simples",
          "Manipuler des outils numériques",
          "Observer les effets de ses actions sur l'environnement"
        ],
        // Cycle 2 et 3
        "Les langages pour penser et communiquer": [
          "Lire, comprendre et interpréter des textes variés",
          "Écrire des textes variés",
          "S'exprimer à l'oral avec clarté",
          "Comprendre et utiliser le vocabulaire",
          "Maîtriser l'orthographe et la grammaire",
          "Utiliser les mathématiques pour résoudre des problèmes",
          "Communiquer en langues vivantes étrangères"
        ],
        "Les méthodes et outils pour apprendre": [
          "Organiser son travail et ses apprentissages",
          "Utiliser des outils numériques pour apprendre",
          "Rechercher, trier et exploiter des informations",
          "Travailler en groupe et coopérer",
          "Développer l'autonomie et l'initiative"
        ],
        "La formation de la personne et du citoyen": [
          "Respecter les règles de vie collective",
          "Développer l'esprit critique et le jugement",
          "S'engager dans un projet collectif",
          "Comprendre les valeurs de la République",
          "Prendre des responsabilités dans la classe ou l'école"
        ],
        "Les systèmes naturels et techniques": [
          "Observer et décrire le monde du vivant",
          "Comprendre le fonctionnement des objets techniques",
          "Réaliser des expériences scientifiques",
          "Développer des attitudes responsables envers l'environnement"
        ],
        "Les représentations du monde et l'activité humaine": [
          "Se repérer dans l'espace et le temps",
          "Comprendre l'histoire et la géographie",
          "Découvrir les arts et la culture",
          "Analyser des documents historiques ou géographiques"
        ],
        // Transversal
        "Langues vivantes étrangères et régionales": [
          "Comprendre et s'exprimer à l'oral",
          "Lire et comprendre des textes simples",
          "Écrire des messages courts",
          "Découvrir d'autres cultures"
        ],
        "Éducation au développement durable": [
          "Comprendre les enjeux du développement durable",
          "Adopter des comportements éco-responsables",
          "Participer à des projets de protection de l'environnement"
        ],
        "Éducation artistique et culturelle": [
          "Pratiquer des activités artistiques",
          "Découvrir des œuvres et des artistes",
          "Exprimer ses émotions et ses idées par l'art",
          "Participer à des projets culturels"
        ]
      };
      const domaineSelect = document.querySelector('select[name="domaine"]');
      const competencesList = document.getElementById('competences_list');
      const addCompetenceBtn = document.getElementById('add_competence_btn');
      function renderCompetenceSelect(selectedValue = '') {
        const domaine = domaineSelect.value;
        const competences = competencesParDomaine[domaine] || [];
        const select = document.createElement('select');
        select.name = 'competences[]';
        select.required = true;
        select.className = 'mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 mr-2';
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = '-- Sélectionnez une compétence --';
        select.appendChild(defaultOption);
        competences.forEach(comp => {
          const option = document.createElement('option');
          option.value = comp;
          option.textContent = comp;
          if (comp === selectedValue) option.selected = true;
          select.appendChild(option);
        });
        return select;
      }
      function addCompetenceRow(selectedValue = '') {
        const row = document.createElement('div');
        row.className = 'flex items-center mb-2';
        const select = renderCompetenceSelect(selectedValue);
        row.appendChild(select);
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'ml-2 px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200';
        removeBtn.textContent = '🗑️';
        removeBtn.onclick = () => row.remove();
        row.appendChild(removeBtn);
        competencesList.appendChild(row);
      }
      addCompetenceBtn.addEventListener('click', () => addCompetenceRow());
      domaineSelect.addEventListener('change', () => { competencesList.innerHTML = ''; });
      // Pré-remplir les compétences existantes
      const initialCompetences = <?php echo json_encode(json_decode($fiche['competences'] ?? '[]', true) ?: []); ?>;
      if (domaineSelect.value && initialCompetences.length) {
        competencesList.innerHTML = '';
        initialCompetences.forEach(val => addCompetenceRow(val));
      }

      // --- AFC dynamiques ---
      const afcParDomaine = {
        "Mobiliser le langage dans toutes ses dimensions": [
          "Communiquer avec les autres à travers des actions ou des propos.",
          "Comprendre des textes lus par l’adulte.",
          "S’exprimer dans un langage oral syntaxiquement correct et précis.",
          "Commencer à écrire seul."
        ],
        "Agir, s'exprimer, comprendre à travers l'activité physique": [
          "Adapter ses déplacements à des environnements variés.",
          "Coopérer et s’opposer individuellement ou collectivement."
        ],
        "Agir, s'exprimer, comprendre à travers les activités artistiques": [
          "Réaliser une composition plastique.",
          "Pratiquer des activités artistiques variées."
        ],
        "Construire les premiers outils pour structurer sa pensée": [
          "Dénombrer, comparer, résoudre des problèmes.",
          "Se repérer dans le temps et l’espace."
        ],
        "Explorer le monde": [
          "Observer, questionner le monde du vivant, de la matière, des objets.",
          "Utiliser des outils numériques."
        ],
        // Cycle 2
        "Les langages pour penser et communiquer": [
          "Lire avec aisance (à haute voix, silencieusement).",
          "Écrire de manière autonome.",
          "Comprendre des textes variés."
        ],
        "Les méthodes et outils pour apprendre": [
          "Organiser son travail et ses apprentissages.",
          "Utiliser des outils numériques pour apprendre."
        ],
        "La formation de la personne et du citoyen": [
          "Respecter les règles de vie collective.",
          "Développer l’esprit critique et le jugement."
        ],
        "Les systèmes naturels et techniques": [
          "Résoudre des problèmes impliquant des grandeurs et des mesures.",
          "Utiliser les nombres entiers pour dénombrer, ordonner, repérer, comparer."
        ],
        "Les représentations du monde et l'activité humaine": [
          "Identifier des caractéristiques du vivant, de la matière, des objets.",
          "Se repérer dans l’espace et le temps."
        ],
        "Langues vivantes étrangères et régionales": [
          "Comprendre des messages oraux simples.",
          "S’exprimer oralement en continu."
        ],
        // Cycle 3
        "Les langages pour penser et communiquer (C3)": [
          "Lire, comprendre et interpréter un texte littéraire adapté à son âge.",
          "Rédiger des écrits variés."
        ],
        "Mathématiques (C3)": [
          "Résoudre des problèmes impliquant des fractions, des nombres décimaux.",
          "Utiliser les outils numériques pour représenter des données."
        ],
        "Sciences et technologie (C3)": [
          "Pratiquer des démarches scientifiques et technologiques."
        ],
        "Histoire-Géographie-EMC (C3)": [
          "Se repérer dans le temps et l’espace.",
          "Comprendre l’organisation du monde."
        ],
        "Langue vivante (C3)": [
          "Comprendre des textes oraux et écrits.",
          "S’exprimer à l’oral."
        ],
        "Éducation artistique et physique (C3)": [
          "Réaliser des productions artistiques.",
          "Réaliser une prestation corporelle ou sportive."
        ],
        // Transversal
        "Transversal": [
          "Respecter les règles de vie collective.",
          "Coopérer et mutualiser.",
          "S’engager dans un projet collectif.",
          "Utiliser des outils numériques de manière responsable."
        ]
      };
      const afcList = document.getElementById('afc_list');
      const addAfcBtn = document.getElementById('add_afc_btn');
      function renderAfcSelect(selectedValue = '') {
        const domaine = domaineSelect.value;
        let afcs = afcParDomaine[domaine] || [];
        if (afcs.length === 0 && domaine.includes('cycle 2')) afcs = afcParDomaine["Les langages pour penser et communiquer"];
        if (afcs.length === 0 && domaine.includes('cycle 3')) afcs = afcParDomaine["Les langages pour penser et communiquer (C3)"];
        if (afcs.length === 0 && domaine.includes('Transversal')) afcs = afcParDomaine["Transversal"];
        afcs = afcs.map(afc => afc.replace(/\.$/, ''));
        const select = document.createElement('select');
        select.name = 'afc[]';
        select.className = 'mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 mr-2';
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = '-- Sélectionnez un attendu de fin de cycle --';
        select.appendChild(defaultOption);
        afcs.forEach(afc => {
          const option = document.createElement('option');
          option.value = afc;
          option.textContent = afc;
          if (afc === selectedValue) option.selected = true;
          select.appendChild(option);
        });
        return select;
      }
      function addAfcRow(selectedValue = '') {
        const row = document.createElement('div');
        row.className = 'flex items-center mb-2';
        const select = renderAfcSelect(selectedValue);
        row.appendChild(select);
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'ml-2 px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200';
        removeBtn.textContent = '🗑️';
        removeBtn.onclick = () => row.remove();
        row.appendChild(removeBtn);
        afcList.appendChild(row);
      }
      addAfcBtn.addEventListener('click', () => addAfcRow());
      domaineSelect.addEventListener('change', () => { afcList.innerHTML = ''; });
      // Pré-remplir les AFC existants
      let selectedAfc = [];
      try { selectedAfc = JSON.parse(<?php echo json_encode($fiche['afc'] ?? '[]'); ?>) || []; } catch(e) { selectedAfc = []; }
      if (domaineSelect.value && selectedAfc.length) {
        afcList.innerHTML = '';
        selectedAfc.forEach(val => addAfcRow(val));
      }

      // --- Déroulement dynamique ---
    function addDeroulementRow(data = {}) {
        const list = document.getElementById('deroulement-list');
      const bloc = document.createElement('div');
        bloc.className = 'bg-white rounded-lg shadow p-4 mb-2';
      // Ligne 1 : 3 champs côte à côte
      const ligne1 = document.createElement('div');
      ligne1.className = 'grid grid-cols-1 md:grid-cols-3 gap-4';
      const champs1 = [
        {name: 'phase', label: 'Phase & durée'},
          {name: 'deroulement', label: 'Déroulement et modalités de travail'},
        {name: 'consignes', label: 'Consigne'}
      ];
      champs1.forEach(({name, label}) => {
        const group = document.createElement('div');
        const lab = document.createElement('label');
        lab.className = 'block text-xs font-semibold text-gray-700 mb-1';
        lab.textContent = label;
        const textarea = document.createElement('textarea');
        textarea.name = name + '[]';
        textarea.value = data[name] || '';
        textarea.className = 'w-full min-h-[2.5rem] p-2 border border-gray-300 rounded resize-y';
        group.appendChild(lab);
        group.appendChild(textarea);
        ligne1.appendChild(group);
      });
      bloc.appendChild(ligne1);
      // Ligne 2 : 4 champs côte à côte
      const ligne2 = document.createElement('div');
      ligne2.className = 'grid grid-cols-1 md:grid-cols-4 gap-4 mt-2';
      const champs2 = [
        {name: 'role_enseignant', label: 'Rôle enseignant'},
        {name: 'role_eleve', label: 'Rôle élève'},
        {name: 'differenciation', label: 'Différenciation'},
        {name: 'materiel', label: 'Matériel'}
      ];
      champs2.forEach(({name, label}) => {
        const group = document.createElement('div');
        const lab = document.createElement('label');
        lab.className = 'block text-xs font-semibold text-gray-700 mb-1';
        lab.textContent = label;
        const textarea = document.createElement('textarea');
        textarea.name = name + '[]';
        textarea.value = data[name] || '';
        textarea.className = 'w-full min-h-[2.5rem] p-2 border border-gray-300 rounded resize-y';
        group.appendChild(lab);
        group.appendChild(textarea);
        ligne2.appendChild(group);
      });
      // Bouton supprimer à droite
      const btnGroup = document.createElement('div');
      btnGroup.className = 'flex items-end justify-end mt-2';
      const btn = document.createElement('button');
      btn.type = 'button';
        btn.className = 'w-10 h-10 bg-red-500 hover:bg-red-600 text-white rounded flex items-center justify-center ml-2';
      btn.innerText = '🗑️';
        btn.onclick = function() { bloc.remove(); };
      btnGroup.appendChild(btn);
      ligne2.appendChild(btnGroup);
      bloc.appendChild(ligne2);
        list.appendChild(bloc);
    }
      // Initialisation du déroulement existant
    const deroulement_initial = <?= json_encode($deroulement_data) ?>;
    deroulement_initial.forEach(data => addDeroulementRow(data));
      // Ajout dynamique
      document.querySelector('button[onclick="addDeroulementRow()"]')?.addEventListener('click', function() { addDeroulementRow(); });
      // Génération JSON à la soumission
    document.querySelector('form').addEventListener('submit', function (e) {
        const blocs = document.querySelectorAll('#deroulement-list > div');
        if (blocs.length === 0) {
          alert('Veuillez ajouter au moins une ligne de déroulement de séance.');
          e.preventDefault();
          return false;
        }
        let allFilled = true;
        blocs.forEach(bloc => {
          const inputs = bloc.querySelectorAll('textarea');
          inputs.forEach(input => {
            if (!input.value.trim()) {
              allFilled = false;
            }
          });
        });
        if (!allFilled) {
          alert('Veuillez remplir tous les champs du déroulement de séance.');
          e.preventDefault();
          return false;
        }
      const data = [];
        blocs.forEach(bloc => {
          const inputs = bloc.querySelectorAll('textarea');
        const item = {};
        inputs.forEach(input => {
          item[input.name.replace('[]', '')] = input.value;
        });
        data.push(item);
      });
      document.getElementById('deroulement_json').value = JSON.stringify(data);
        //console.log('deroulement_json envoyé :', document.getElementById('deroulement_json').value);
      });

      // --- Compétences SCCCC dynamiques ---
      const competencesScccc = [
        "Comprendre, s'exprimer en utilisant la langue française à l'oral et à l'écrit",
        "Comprendre, s'exprimer en utilisant une langue étrangère et, le cas échéant, une langue régionale",
        "Comprendre, s'exprimer en utilisant les langages mathématiques, scientifiques et informatiques",
        "Comprendre, s'exprimer en utilisant les langages des arts et du corps",
        "Apprendre à apprendre, seul ou collectivement, en classe ou en dehors",
        "Maîtriser les techniques usuelles de l'information et de la documentation",
        "Mobiliser des outils numériques pour apprendre, échanger, communiquer",
        "Comprendre les règles et le droit",
        "Respecter autrui et accepter les différences",
        "Agir de façon éthique et responsable",
        "Faire preuve de réflexion et de discernement",
        "Se situer dans l'espace et dans le temps",
        "Analyser et comprendre les organisations humaines et les représentations du monde",
        "Raisonner, imaginer, élaborer, produire"
      ];
      const competencesSccccList = document.getElementById('competences_scccc_list');
      const addCompetenceSccccBtn = document.getElementById('add_competence_scccc_btn');
      function renderCompetenceSccccSelect(selectedValue = '') {
        const select = document.createElement('select');
        select.name = 'competences_scccc[]';
        select.className = 'mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 mr-2';
        select.style.width = '100%';
        select.style.maxWidth = '100%';
        select.style.whiteSpace = 'normal';
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = '-- Sélectionnez une compétence du SCCCC --';
        select.appendChild(defaultOption);
        competencesScccc.forEach(comp => {
          const option = document.createElement('option');
          option.value = comp;
          option.textContent = comp;
          option.title = comp;
          option.style.whiteSpace = 'normal';
          if (comp === selectedValue) option.selected = true;
          select.appendChild(option);
        });
        return select;
      }
      function addCompetenceSccccRow(selectedValue = '') {
        const row = document.createElement('div');
        row.className = 'flex items-center mb-2';
        const select = renderCompetenceSccccSelect(selectedValue);
        row.appendChild(select);
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'ml-2 px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200';
        removeBtn.textContent = '🗑️';
        removeBtn.onclick = () => row.remove();
        row.appendChild(removeBtn);
        competencesSccccList.appendChild(row);
      }
      addCompetenceSccccBtn.addEventListener('click', () => addCompetenceSccccRow());
      // Affichage dynamique du champ SCCCC selon le domaine (cycle 2 ou 3)
      function updateSccccVisibility() {
        const domaine = domaineSelect.value;
        const domainesCycle2et3 = [
          "Les langages pour penser et communiquer",
          "Les méthodes et outils pour apprendre",
          "La formation de la personne et du citoyen",
          "Les systèmes naturels et techniques",
          "Les représentations du monde et l'activité humaine"
        ];
        if (domainesCycle2et3.includes(domaine)) {
          document.getElementById('competences_scccc_container').style.display = 'block';
        } else {
          document.getElementById('competences_scccc_container').style.display = 'none';
          competencesSccccList.innerHTML = '';
        }
      }
      domaineSelect.addEventListener('change', updateSccccVisibility);
      updateSccccVisibility();
      // Pré-remplissage en modification
      if (typeof initialCompetencesScccc !== 'undefined' && initialCompetencesScccc.length) {
        competencesSccccList.innerHTML = '';
        initialCompetencesScccc.forEach(val => addCompetenceSccccRow(val));
      }
    });
  </script>
</body>
</html>
