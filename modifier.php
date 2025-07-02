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

  $stmt = $pdo->prepare("UPDATE fiches SET
    domaine = ?, niveau = ?, duree = ?, sequence = ?, seance = ?,
    objectifs = ?, competences = ?, competences_scccc = ?, prerequis = ?, afc = ?,
    evaluation = ?, bilan = ?, prolongement = ?, remediation = ?,
    nom_enseignant = ?, deroulement_json = ?
    WHERE id = ? AND utilisateur_id = ?");

  $stmt->execute([
    $_POST['domaine'], $_POST['niveau'], $_POST['duree'], $_POST['sequence'], $_POST['seance'],
    $_POST['objectifs'], $_POST['competences'], $_POST['competences_scccc'] ?? '', $_POST['prerequis'], $_POST['afc'],
    $_POST['evaluation'], $_POST['bilan'], $_POST['prolongement'], $_POST['remediation'],
    $_POST['nom_enseignant'], $deroulement_json,
    $id, $_SESSION['utilisateur_id']
  ]);

  $success = "✅ Fiche mise à jour avec succès.";
}

// Pour affichage
$deroulement_data = json_decode($fiche['deroulement_json'] ?? '[]', true);
?>

<!DOCTYPE html>
<html lang="fr">
<?php $page_title = 'Modifier fiche'; include __DIR__ . '/includes/head.php'; ?>
<body class="font-sans bg-gray-50 min-h-screen">
  <?php include __DIR__ . '/includes/header.php'; ?>
  <main class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg p-8 my-10 pt-16">
    <h1 class="text-2xl font-bold text-blue-700 mb-6 flex items-center gap-2">✏️ Modifier la fiche <span class="text-gray-900">« <?= htmlspecialchars((string) $fiche['seance']) ?> »</span></h1>
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
          <input type="text" name="niveau" placeholder="Niveau" value="<?= htmlspecialchars($fiche['niveau']) ?>" required class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500">
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
        <label class="block text-sm font-medium text-gray-700 mb-1">Compétence visée</label>
        <select name="competences" required class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500">
          <option value="">-- Sélectionnez une compétence visée --</option>
          <optgroup label="Mobiliser le langage dans toutes ses dimensions">
            <option value="Oser entrer en communication" <?= $fiche['competences'] === 'Oser entrer en communication' ? 'selected' : '' ?>>Oser entrer en communication</option>
            <option value="Comprendre et apprendre" <?= $fiche['competences'] === 'Comprendre et apprendre' ? 'selected' : '' ?>>Comprendre et apprendre</option>
            <option value="Échanger et réfléchir avec les autres" <?= $fiche['competences'] === 'Échanger et réfléchir avec les autres' ? 'selected' : '' ?>>Échanger et réfléchir avec les autres</option>
            <option value="Se préparer à apprendre à lire" <?= $fiche['competences'] === 'Se préparer à apprendre à lire' ? 'selected' : '' ?>>Se préparer à apprendre à lire</option>
            <option value="Développer la conscience phonologique" <?= $fiche['competences'] === 'Développer la conscience phonologique' ? 'selected' : '' ?>>Développer la conscience phonologique</option>
            <option value="Comprendre le principe alphabétique" <?= $fiche['competences'] === 'Comprendre le principe alphabétique' ? 'selected' : '' ?>>Comprendre le principe alphabétique</option>
            <option value="Produire des discours variés" <?= $fiche['competences'] === 'Produire des discours variés' ? 'selected' : '' ?>>Produire des discours variés</option>
            <option value="Découvrir les fonctions de l'écrit" <?= $fiche['competences'] === 'Découvrir les fonctions de l\'écrit' ? 'selected' : '' ?>>Découvrir les fonctions de l'écrit</option>
            <option value="Commencer à produire des écrits" <?= $fiche['competences'] === 'Commencer à produire des écrits' ? 'selected' : '' ?>>Commencer à produire des écrits</option>
            <option value="Se familiariser avec l'écrit dans toutes ses formes" <?= $fiche['competences'] === 'Se familiariser avec l\'écrit dans toutes ses formes' ? 'selected' : '' ?>>Se familiariser avec l'écrit dans toutes ses formes</option>
          </optgroup>
          <optgroup label="Agir, s'exprimer, comprendre à travers l'activité physique">
            <option value="Agir dans des environnements variés" <?= $fiche['competences'] === 'Agir dans des environnements variés' ? 'selected' : '' ?>>Agir dans des environnements variés</option>
            <option value="Adapter ses déplacements à des contraintes" <?= $fiche['competences'] === 'Adapter ses déplacements à des contraintes' ? 'selected' : '' ?>>Adapter ses déplacements à des contraintes</option>
            <option value="Coopérer et s'opposer individuellement ou collectivement" <?= $fiche['competences'] === 'Coopérer et s\'opposer individuellement ou collectivement' ? 'selected' : '' ?>>Coopérer et s'opposer individuellement ou collectivement</option>
            <option value="Exprimer des intentions par le geste" <?= $fiche['competences'] === 'Exprimer des intentions par le geste' ? 'selected' : '' ?>>Exprimer des intentions par le geste</option>
            <option value="Apprendre à respecter des règles" <?= $fiche['competences'] === 'Apprendre à respecter des règles' ? 'selected' : '' ?>>Apprendre à respecter des règles</option>
            <option value="Développer sa motricité fine et globale" <?= $fiche['competences'] === 'Développer sa motricité fine et globale' ? 'selected' : '' ?>>Développer sa motricité fine et globale</option>
            <option value="Se repérer dans l'espace avec son corps" <?= $fiche['competences'] === 'Se repérer dans l\'espace avec son corps' ? 'selected' : '' ?>>Se repérer dans l'espace avec son corps</option>
          </optgroup>
          <optgroup label="Agir, s'exprimer, comprendre à travers les activités artistiques">
            <option value="Expérimenter les matériaux, les outils, les supports" <?= $fiche['competences'] === 'Expérimenter les matériaux, les outils, les supports' ? 'selected' : '' ?>>Expérimenter les matériaux, les outils, les supports</option>
            <option value="Créer des productions plastiques et visuelles" <?= $fiche['competences'] === 'Créer des productions plastiques et visuelles' ? 'selected' : '' ?>>Créer des productions plastiques et visuelles</option>
            <option value="Observer et décrire des œuvres" <?= $fiche['competences'] === 'Observer et décrire des œuvres' ? 'selected' : '' ?>>Observer et décrire des œuvres</option>
            <option value="Explorer des univers sonores" <?= $fiche['competences'] === 'Explorer des univers sonores' ? 'selected' : '' ?>>Explorer des univers sonores</option>
            <option value="Participer à des jeux vocaux et corporels" <?= $fiche['competences'] === 'Participer à des jeux vocaux et corporels' ? 'selected' : '' ?>>Participer à des jeux vocaux et corporels</option>
            <option value="Chanter seul et en groupe" <?= $fiche['competences'] === 'Chanter seul et en groupe' ? 'selected' : '' ?>>Chanter seul et en groupe</option>
            <option value="Jouer avec sa voix et son corps" <?= $fiche['competences'] === 'Jouer avec sa voix et son corps' ? 'selected' : '' ?>>Jouer avec sa voix et son corps</option>
            <option value="Imaginer, inventer, interpréter" <?= $fiche['competences'] === 'Imaginer, inventer, interpréter' ? 'selected' : '' ?>>Imaginer, inventer, interpréter</option>
          </optgroup>
          <optgroup label="Construire les premiers outils pour structurer sa pensée">
            <option value="Dénombrer des quantités" <?= $fiche['competences'] === 'Dénombrer des quantités' ? 'selected' : '' ?>>Dénombrer des quantités</option>
            <option value="Associer un nombre à une quantité" <?= $fiche['competences'] === 'Associer un nombre à une quantité' ? 'selected' : '' ?>>Associer un nombre à une quantité</option>
            <option value="Utiliser le comptage pour résoudre des problèmes" <?= $fiche['competences'] === 'Utiliser le comptage pour résoudre des problèmes' ? 'selected' : '' ?>>Utiliser le comptage pour résoudre des problèmes</option>
            <option value="Comprendre les nombres comme positions" <?= $fiche['competences'] === 'Comprendre les nombres comme positions' ? 'selected' : '' ?>>Comprendre les nombres comme positions</option>
            <option value="Utiliser les premiers symboles mathématiques" <?= $fiche['competences'] === 'Utiliser les premiers symboles mathématiques' ? 'selected' : '' ?>>Utiliser les premiers symboles mathématiques</option>
            <option value="Reproduire, compléter, créer des suites logiques" <?= $fiche['competences'] === 'Reproduire, compléter, créer des suites logiques' ? 'selected' : '' ?>>Reproduire, compléter, créer des suites logiques</option>
            <option value="Reconnaître et nommer des formes" <?= $fiche['competences'] === 'Reconnaître et nommer des formes' ? 'selected' : '' ?>>Reconnaître et nommer des formes</option>
            <option value="Comparer, classer des objets selon des critères" <?= $fiche['competences'] === 'Comparer, classer des objets selon des critères' ? 'selected' : '' ?>>Comparer, classer des objets selon des critères</option>
            <option value="Se repérer dans le temps court (journée, semaine)" <?= $fiche['competences'] === 'Se repérer dans le temps court (journée, semaine)' ? 'selected' : '' ?>>Se repérer dans le temps court (journée, semaine)</option>
          </optgroup>
          <optgroup label="Explorer le monde">
            <option value="Découvrir les objets, matières, phénomènes du vivant" <?= $fiche['competences'] === 'Découvrir les objets, matières, phénomènes du vivant' ? 'selected' : '' ?>>Découvrir les objets, matières, phénomènes du vivant</option>
            <option value="Utiliser ses sens pour observer" <?= $fiche['competences'] === 'Utiliser ses sens pour observer' ? 'selected' : '' ?>>Utiliser ses sens pour observer</option>
            <option value="Identifier les caractéristiques du vivant et des objets" <?= $fiche['competences'] === 'Identifier les caractéristiques du vivant et des objets' ? 'selected' : '' ?>>Identifier les caractéristiques du vivant et des objets</option>
            <option value="Se repérer dans le temps (jours, mois, saisons)" <?= $fiche['competences'] === 'Se repérer dans le temps (jours, mois, saisons)' ? 'selected' : '' ?>>Se repérer dans le temps (jours, mois, saisons)</option>
            <option value="Se repérer dans l'espace (école, classe, parcours)" <?= $fiche['competences'] === 'Se repérer dans l\'espace (école, classe, parcours)' ? 'selected' : '' ?>>Se repérer dans l'espace (école, classe, parcours)</option>
            <option value="Découvrir l'usage d'objets techniques simples" <?= $fiche['competences'] === 'Découvrir l\'usage d\'objets techniques simples' ? 'selected' : '' ?>>Découvrir l'usage d'objets techniques simples</option>
            <option value="Manipuler des outils numériques" <?= $fiche['competences'] === 'Manipuler des outils numériques' ? 'selected' : '' ?>>Manipuler des outils numériques</option>
            <option value="Observer les effets de ses actions sur l'environnement" <?= $fiche['competences'] === 'Observer les effets de ses actions sur l\'environnement' ? 'selected' : '' ?>>Observer les effets de ses actions sur l'environnement</option>
          </optgroup>
        </select>
      </div>
      <div id="competences_scccc_container" style="display: none;">
        <label class="block text-sm font-medium text-gray-700 mb-1">Compétence du SCCCC</label>
        <select name="competences_scccc" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500">
          <option value="">-- Sélectionnez une compétence du SCCCC --</option>
          <option value="Comprendre, s'exprimer en utilisant la langue française à l'oral et à l'écrit" <?= $fiche['competences_scccc'] === 'Comprendre, s\'exprimer en utilisant la langue française à l\'oral et à l\'écrit' ? 'selected' : '' ?>>Comprendre, s'exprimer en utilisant la langue française à l'oral et à l'écrit</option>
          <option value="Comprendre, s'exprimer en utilisant une langue étrangère et, le cas échéant, une langue régionale" <?= $fiche['competences_scccc'] === 'Comprendre, s\'exprimer en utilisant une langue étrangère et, le cas échéant, une langue régionale' ? 'selected' : '' ?>>Comprendre, s'exprimer en utilisant une langue étrangère et, le cas échéant, une langue régionale</option>
          <option value="Comprendre, s'exprimer en utilisant les langages mathématiques, scientifiques et informatiques" <?= $fiche['competences_scccc'] === 'Comprendre, s\'exprimer en utilisant les langages mathématiques, scientifiques et informatiques' ? 'selected' : '' ?>>Comprendre, s'exprimer en utilisant les langages mathématiques, scientifiques et informatiques</option>
          <option value="Comprendre, s'exprimer en utilisant les langages des arts et du corps" <?= $fiche['competences_scccc'] === 'Comprendre, s\'exprimer en utilisant les langages des arts et du corps' ? 'selected' : '' ?>>Comprendre, s'exprimer en utilisant les langages des arts et du corps</option>
          <option value="Apprendre à apprendre, seul ou collectivement, en classe ou en dehors" <?= $fiche['competences_scccc'] === 'Apprendre à apprendre, seul ou collectivement, en classe ou en dehors' ? 'selected' : '' ?>>Apprendre à apprendre, seul ou collectivement, en classe ou en dehors</option>
          <option value="Maîtriser les techniques usuelles de l'information et de la documentation" <?= $fiche['competences_scccc'] === 'Maîtriser les techniques usuelles de l\'information et de la documentation' ? 'selected' : '' ?>>Maîtriser les techniques usuelles de l'information et de la documentation</option>
          <option value="Mobiliser des outils numériques pour apprendre, échanger, communiquer" <?= $fiche['competences_scccc'] === 'Mobiliser des outils numériques pour apprendre, échanger, communiquer' ? 'selected' : '' ?>>Mobiliser des outils numériques pour apprendre, échanger, communiquer</option>
          <option value="Comprendre les règles et le droit" <?= $fiche['competences_scccc'] === 'Comprendre les règles et le droit' ? 'selected' : '' ?>>Comprendre les règles et le droit</option>
          <option value="Respecter autrui et accepter les différences" <?= $fiche['competences_scccc'] === 'Respecter autrui et accepter les différences' ? 'selected' : '' ?>>Respecter autrui et accepter les différences</option>
          <option value="Agir de façon éthique et responsable" <?= $fiche['competences_scccc'] === 'Agir de façon éthique et responsable' ? 'selected' : '' ?>>Agir de façon éthique et responsable</option>
          <option value="Faire preuve de réflexion et de discernement" <?= $fiche['competences_scccc'] === 'Faire preuve de réflexion et de discernement' ? 'selected' : '' ?>>Faire preuve de réflexion et de discernement</option>
          <option value="Se situer dans l'espace et dans le temps" <?= $fiche['competences_scccc'] === 'Se situer dans l\'espace et dans le temps' ? 'selected' : '' ?>>Se situer dans l'espace et dans le temps</option>
          <option value="Analyser et comprendre les organisations humaines et les représentations du monde" <?= $fiche['competences_scccc'] === 'Analyser et comprendre les organisations humaines et les représentations du monde' ? 'selected' : '' ?>>Analyser et comprendre les organisations humaines et les représentations du monde</option>
          <option value="Raisonner, imaginer, élaborer, produire" <?= $fiche['competences_scccc'] === 'Raisonner, imaginer, élaborer, produire' ? 'selected' : '' ?>>Raisonner, imaginer, élaborer, produire</option>
        </select>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">AFC</label>
          <textarea name="afc" placeholder="AFC" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($fiche['afc']) ?></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Prérequis</label>
          <textarea name="prerequis" placeholder="Prérequis" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($fiche['prerequis']) ?></textarea>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Modalités d'évaluation</label>
          <textarea name="evaluation" placeholder="Modalités d'évaluation" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($fiche['evaluation']) ?></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Bilan pédagogique et didactique</label>
          <textarea name="bilan" placeholder="Bilan pédagogique et didactique" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($fiche['bilan']) ?></textarea>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Prolongement(s) possible(s)</label>
          <textarea name="prolongement" placeholder="Prolongement(s) possible(s)" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($fiche['prolongement']) ?></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Remédiation(s) éventuelle(s)</label>
          <textarea name="remediation" placeholder="Remédiation(s) éventuelle(s)" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($fiche['remediation']) ?></textarea>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nom de l'enseignant</label>
        <input type="text" name="nom_enseignant" placeholder="Nom de l'enseignant" value="<?= htmlspecialchars($fiche['nom_enseignant']) ?>" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500">
      </div>
      <hr class="my-6 border-gray-200">
      <h3 class="text-lg font-bold text-gray-800 mb-2">Déroulement de la séance</h3>
      <div class="overflow-x-auto">
        <table id="deroulement-table" class="min-w-full w-full border border-gray-200 rounded-lg text-sm bg-gray-50">
          <tbody></tbody>
        </table>
      </div>
      <button type="button" onclick="addDeroulementRow()" class="mt-4 mb-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">➕ Ajouter une ligne</button>
      <input type="hidden" name="deroulement_json" id="deroulement_json">
      <div class="flex justify-end mt-8">
        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition">💾 Enregistrer la fiche</button>
      </div>
    </form>
  </main>

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

      // Vérifier si la compétence sélectionnée appartient au domaine sélectionné
      if (competencesSelect.selectedIndex > 0) {
        const selectedOption = competencesSelect.options[competencesSelect.selectedIndex];
        if (selectedOption.parentElement.label !== selectedDomaine) {
          competencesSelect.value = '';
        }
      }

      // Afficher le champ des compétences SCCCC uniquement pour les cycles 2 et 3
      if (selectedOptgroup === "École élémentaire (cycle 2 à 3)") {
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
      const table = document.querySelector('#deroulement-table tbody');
      const row = document.createElement('tr');
      // On va utiliser une seule cellule qui contient toute la structure UX
      const cell = document.createElement('td');
      cell.colSpan = 8;
      // Bloc principal
      const bloc = document.createElement('div');
      bloc.className = 'space-y-2 border rounded-lg p-3 bg-white mb-2 shadow';
      // Ligne 1 : 3 champs côte à côte
      const ligne1 = document.createElement('div');
      ligne1.className = 'grid grid-cols-1 md:grid-cols-3 gap-4';
      const champs1 = [
        {name: 'phase', label: 'Phase & durée'},
        {name: 'deroulement', label: 'Déroulement'},
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
      btn.className = 'w-10 h-10 bg-blue-500 hover:bg-blue-600 text-white rounded flex items-center justify-center ml-2';
      btn.innerText = '🗑️';
      btn.onclick = function() { bloc.closest('tr').remove(); };
      btnGroup.appendChild(btn);
      ligne2.appendChild(btnGroup);
      bloc.appendChild(ligne2);
      cell.appendChild(bloc);
      row.appendChild(cell);
      table.appendChild(row);
    }

    const deroulement_initial = <?= json_encode($deroulement_data) ?>;
    deroulement_initial.forEach(data => addDeroulementRow(data));

    document.querySelector('form').addEventListener('submit', function (e) {
      const rows = document.querySelectorAll('#deroulement-table tbody tr');
      const data = [];
      rows.forEach(row => {
        const inputs = row.querySelectorAll('input');
        const item = {};
        inputs.forEach(input => {
          item[input.name.replace('[]', '')] = input.value;
        });
        data.push(item);
      });
      document.getElementById('deroulement_json').value = JSON.stringify(data);
    });
  </script>
</body>
</html>
