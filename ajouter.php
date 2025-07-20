<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['utilisateur_id'])) {
  header('Location: /login');
  exit;
}

$success = '';
$limiteAtteinte = false;
$limiteMessage = '';
// Récupère la limite personnalisée pour l'utilisateur
$stmt = $pdo->prepare("SELECT limite_fiches, limite_sequences FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['utilisateur_id']]);
$limites = $stmt->fetch();
$limiteFiches = $limites['limite_fiches'] ?? 1;
$limiteSequences = $limites['limite_sequences'] ?? 1;
// Vérifie la limite fiche
$stmt = $pdo->prepare("SELECT COUNT(*) FROM fiches WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['utilisateur_id']]);
$nbFiches = $stmt->fetchColumn();
if ($nbFiches >= $limiteFiches) {
  $limiteAtteinte = true;
  $limiteMessage = "❌ Limite atteinte : vous ne pouvez créer que $limiteFiches fiche(s).";
}
// Vérifie la limite séquence
$stmt = $pdo->prepare("SELECT COUNT(*) FROM sequences WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['utilisateur_id']]);
$nbSequences = $stmt->fetchColumn();
if ($nbSequences >= $limiteSequences) {
  $limiteAtteinte = true;
  $limiteMessage = "❌ Limite atteinte : vous ne pouvez créer que $limiteFiches fiche(s) et $limiteSequences séquence(s).";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $limiteAtteinte) {
  $success = '';
  $erreur = $limiteMessage;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $afc = isset($_POST['afc']) ? json_encode($_POST['afc']) : '';
  $stmt = $pdo->prepare("INSERT INTO fiches (
    domaine, niveau, duree, sequence, seance, objectifs, competences, competences_scccc, afc,
    prerequis, critere_realisation, critere_reussite, nom_enseignant, deroulement_json,
    evaluation, bilan, prolongement, remediation,
    utilisateur_id
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

  $stmt->execute([
    $_POST['domaine'],
    $_POST['niveau'],
    $_POST['duree'],
    $_POST['sequence'],
    $_POST['seance'],
    $_POST['objectifs'],
    json_encode($_POST['competences']), // ici
    json_encode($_POST['competences_scccc']), // ici
    $afc,
    $_POST['prerequis'],
    $_POST['critere_realisation'],
    $_POST['critere_reussite'],
    $_POST['nom_enseignant'],
    $_POST['deroulement_json'],
    $_POST['evaluation'],
    $_POST['bilan'],
    $_POST['prolongement'],
    $_POST['remediation'],
    $_SESSION['utilisateur_id']
  ]);

  header('Location: /fiches.php?success=1');
  exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<?php $page_title = 'Ajouter une fiche'; include __DIR__ . '/includes/head.php'; ?>
<body class="font-sans bg-gray-50 min-h-screen">
  <?php include __DIR__ . '/includes/header.php'; ?>
  <div class="max-w-3xl mx-auto px-4 py-12 pt-20">
    <h1 class="text-3xl md:text-4xl font-bold text-blue-700 mb-8 text-center">Créer une fiche de préparation</h1>
    <?php if ($limiteAtteinte): ?>
      <div class="mb-6 rounded bg-red-50 border border-red-200 text-red-800 px-4 py-3 flex items-center gap-2">
        <?= $limiteMessage ?>
      </div>
    <?php endif; ?>
    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>

    <form action="" method="post" class="space-y-6 max-w-2xl bg-white rounded-xl shadow p-8 mt-8" <?php if ($limiteAtteinte) echo 'style="pointer-events:none;opacity:0.5;"'; ?>>
      <label class="block mb-2 font-semibold text-gray-700">Domaine :
        <select name="domaine" id="domaine_select" required class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">-- Sélectionnez un domaine d'apprentissage --</option>
          <optgroup label="École maternelle (cycle 1)">
            <option value="Mobiliser le langage dans toutes ses dimensions">Mobiliser le langage dans toutes ses dimensions</option>
            <option value="Agir, s'exprimer, comprendre à travers l'activité physique">Agir, s'exprimer, comprendre à travers l'activité physique</option>
            <option value="Agir, s'exprimer, comprendre à travers les activités artistiques">Agir, s'exprimer, comprendre à travers les activités artistiques</option>
            <option value="Construire les premiers outils pour structurer sa pensée">Construire les premiers outils pour structurer sa pensée</option>
            <option value="Explorer le monde">Explorer le monde</option>
          </optgroup>
          <optgroup label="École élémentaire et collège (cycle 2 à 3)">
            <option value="Les langages pour penser et communiquer">Les langages pour penser et communiquer</option>
            <option value="Les méthodes et outils pour apprendre">Les méthodes et outils pour apprendre</option>
            <option value="La formation de la personne et du citoyen">La formation de la personne et du citoyen</option>
            <option value="Les systèmes naturels et techniques">Les systèmes naturels et techniques</option>
            <option value="Les représentations du monde et l'activité humaine">Les représentations du monde et l'activité humaine</option>
          </optgroup>
          <optgroup label="Transversal (tout cycle)">
            <option value="Langues vivantes étrangères et régionales">Langues vivantes étrangères et régionales</option>
            <option value="Éducation au développement durable">Éducation au développement durable</option>
            <option value="Éducation artistique et culturelle">Éducation artistique et culturelle</option>
          </optgroup>
        </select>
      </label>
      <label class="block mb-2 font-semibold text-gray-700">Niveau :
        <input type="text" name="niveau" placeholder="Cycle (Niveau de classe)" required class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </label>
      <label class="block mb-2 font-semibold text-gray-700">Durée :
        <input type="text" name="duree" placeholder="Durée" required class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </label>
      <label class="block mb-2 font-semibold text-gray-700">Séquence :
        <input type="text" name="sequence" placeholder="Séquence" required class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </label>
      <label class="block mb-2 font-semibold text-gray-700">Séance :
        <input type="text" name="seance" placeholder="Séance n° : Titre de la séance" required class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </label>
      <label class="block mb-2 font-semibold text-gray-700">Objectifs visés :
        <textarea name="objectifs" placeholder="Objectifs visés" required class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
      </label>
      <label class="block mb-2 font-semibold text-gray-700">Compétence(s) visée(s) :</label>
      <div id="competences_list"></div>
      <button type="button" id="add_competence_btn" class="mb-4 px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200">➕ Ajouter une compétence</button>
      <script>
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
        const domaineSelect = document.getElementById('domaine_select');
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
        domaineSelect.addEventListener('change', () => {
          // Réinitialiser la liste des compétences si le domaine change
          competencesList.innerHTML = '';
        });
        document.addEventListener('DOMContentLoaded', () => {
          if (domaineSelect.value) {
            competencesList.innerHTML = '';
          }
        });
      </script>
      <div id="competences_scccc_container" style="display: none;">
        <label class="block text-sm font-medium text-gray-700 mb-1">Compétence(s) du SCCCC</label>
        <div id="competences_scccc_list"></div>
        <button type="button" id="add_competence_scccc_btn" class="mb-4 px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200">➕ Ajouter une compétence SCCCC</button>
      </div>
      <script>
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
      </script>
      <label class="block mb-2 font-semibold text-gray-700">AFC <span class="text-xs text-gray-500">(optionnel)</span> :</label>
      <div id="afc_list"></div>
      <button type="button" id="add_afc_btn" class="mb-4 px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200">➕ Ajouter un attendu</button>
      <script>
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
        domaineSelect.addEventListener('change', () => {
          afcList.innerHTML = '';
        });
        document.addEventListener('DOMContentLoaded', () => {
          if (domaineSelect.value) {
            afcList.innerHTML = '';
          }
        });
      </script>
      <label class="block mb-2 font-semibold text-gray-700">Prérequis <span class="text-red-500">*</span> :
        <textarea name="prerequis" placeholder="Prérequis" required class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
      </label>
      <label class="block mb-2 font-semibold text-gray-700">Critère de réalisation <span class="text-red-500">*</span> :
        <textarea name="critere_realisation" placeholder="Comment je fais pour réussir" required class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
      </label>
      <label class="block mb-2 font-semibold text-gray-700">Critère de réussite <span class="text-red-500">*</span> :
        <textarea name="critere_reussite" placeholder="Comment je sais que j'ai réussi" required class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
      </label>
      <label class="block mb-2 font-semibold text-gray-700">Modalités d'évaluation <span class="text-red-500">*</span> :
        <textarea name="evaluation" placeholder="Modalités d'évaluation" required class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
      </label>
      <label class="block mb-2 font-semibold text-gray-700">Bilan pédagogique et didactique <span class="text-xs text-gray-500">(optionnel)</span> :
        <textarea name="bilan" placeholder="Bilan pédagogique et didactique" class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
      </label>
      <label class="block mb-2 font-semibold text-gray-700">Prolongement(s) possible(s) <span class="text-xs text-gray-500">(optionnel)</span> :
        <textarea name="prolongement" placeholder="Prolongement(s) possible(s)" class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
      </label>
      <label class="block mb-2 font-semibold text-gray-700">Remédiation(s) éventuelle(s) <span class="text-xs text-gray-500">(optionnel)</span> :
        <textarea name="remediation" placeholder="Remédiation(s) éventuelle(s)" class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
      </label>
      <label class="block mb-2 font-semibold text-gray-700">Nom de l'enseignant <span class="text-red-500">*</span> :
        <input type="text" name="nom_enseignant" placeholder="Nom de l'enseignant" required class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </label>

      <h3 class="text-lg font-bold text-gray-800 mb-2">Déroulement de la séance</h3>
      <div id="deroulement-list" class="space-y-4 mb-4"></div>
      <div class="flex justify-center mb-8">
        <button type="button" onclick="addDeroulementRow()" class="px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 transition">➕ Ajouter une ligne</button>
      </div>
      <input type="hidden" name="deroulement_json" id="deroulement_json">

      <button type="submit">💾 Enregistrer</button>
    </form>
  </div>

  <script>
    // Fonction pour filtrer les compétences en fonction du domaine sélectionné
    function filterCompetences() {
      const domaineSelect = document.querySelector('select[name="domaine"]');
      const competencesSelect = document.querySelector('select[name="competences"]');
      const competencesSccccContainer = document.getElementById('competences_scccc_container');
      const selectedDomaine = domaineSelect.value;
      const selectedOptgroup = domaineSelect.options[domaineSelect.selectedIndex]?.parentElement?.label;

      // Cacher toutes les optgroup de compétences
      const optgroups = competencesSelect.querySelectorAll('optgroup');
      optgroups.forEach(optgroup => {
        optgroup.style.display = 'none';
      });

      // Afficher seulement l'optgroup correspondant au domaine sélectionné
      if (selectedDomaine) {
        const matchingOptgroup = competencesSelect.querySelector(`optgroup[label="${selectedDomaine}"]`);
        if (matchingOptgroup) {
          matchingOptgroup.style.display = '';
        }
      }

      // Réinitialiser la sélection si la compétence actuelle n'appartient pas au domaine sélectionné
      let optionFound = false;
      const selectedOption = competencesSelect.options[competencesSelect.selectedIndex];
      if (selectedOption.parentElement.label === selectedDomaine) {
        optionFound = true;
      }

      if (!optionFound) {
        competencesSelect.value = '';
      }

      // Afficher le champ des compétences SCCCC uniquement pour les cycles 2 et 3
      if (selectedOptgroup === "École élémentaire et collège (cycle 2 à 4)") {
        competencesSccccContainer.style.display = 'block';
      } else {
        competencesSccccContainer.style.display = 'none';
        document.querySelector('select[name="competences_scccc"]').value = '';
      }
    }

    // Appliquer le filtre au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
      filterCompetences();

      // Ajouter un écouteur d'événement pour le changement de domaine
      document.querySelector('select[name="domaine"]').addEventListener('change', filterCompetences);
    });

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
      console.log('deroulement_json envoyé :', document.getElementById('deroulement_json').value);
    });
  </script>
</body>
</html>
