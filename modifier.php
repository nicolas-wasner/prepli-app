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
<head>
  <meta charset="UTF-8">
  <title>Modifier fiche</title>
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>
  <div class="container">
    <h1>✏️ Modifier la fiche « <?= htmlspecialchars((string) $fiche['seance']) ?> »</h1>
    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>

    <form method="post">
      <select name="domaine" required>
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
      <input type="text" name="niveau" placeholder="Niveau" value="<?= htmlspecialchars($fiche['niveau']) ?>" required>
      <input type="text" name="duree" placeholder="Durée" value="<?= htmlspecialchars($fiche['duree']) ?>" required>
      <input type="text" name="sequence" placeholder="Séquence" value="<?= htmlspecialchars($fiche['sequence']) ?>" required>
      <input type="text" name="seance" placeholder="Séance" value="<?= htmlspecialchars($fiche['seance']) ?>" required>
      <textarea name="objectifs" placeholder="Objectifs visés"><?= htmlspecialchars($fiche['objectifs']) ?></textarea>
      <select name="competences" required>
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

      <div id="competences_scccc_container" style="display: none;">
        <select name="competences_scccc">
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
      <textarea name="afc" placeholder="AFC"><?= htmlspecialchars($fiche['afc']) ?></textarea>
      <textarea name="prerequis" placeholder="Prérequis"><?= htmlspecialchars($fiche['prerequis']) ?></textarea>
      <textarea name="evaluation" placeholder="Modalités d’évaluation"><?= htmlspecialchars($fiche['evaluation']) ?></textarea>
      <textarea name="bilan" placeholder="Bilan pédagogique et didactique"><?= htmlspecialchars($fiche['bilan']) ?></textarea>
      <textarea name="prolongement" placeholder="Prolongement(s) possible(s)"><?= htmlspecialchars($fiche['prolongement']) ?></textarea>
      <textarea name="remediation" placeholder="Remédiation(s) éventuelle(s)"><?= htmlspecialchars($fiche['remediation']) ?></textarea>
      <input type="text" name="nom_enseignant" placeholder="Nom de l'enseignant" value="<?= htmlspecialchars($fiche['nom_enseignant']) ?>">

      <h3>Déroulement de la séance</h3>
      <table id="deroulement-table" border="1" cellpadding="4" cellspacing="0" width="100%">
        <thead>
          <tr>
            <th>Phase & durée</th>
            <th>Déroulement</th>
            <th>Consigne</th>
            <th>Rôle enseignant</th>
            <th>Rôle élève</th>
            <th>Différenciation</th>
            <th>Matériel</th>
            <th></th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
      <button type="button" onclick="addDeroulementRow()">➕ Ajouter une ligne</button>
      <input type="hidden" name="deroulement_json" id="deroulement_json">
      <br><br>
      <button type="submit">💾 Enregistrer les modifications</button>
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
      const champs = ['phase', 'deroulement', 'consignes', 'role_enseignant', 'role_eleve', 'differenciation', 'materiel'];
      champs.forEach(name => {
        const cell = document.createElement('td');
        const input = document.createElement('input');
        input.type = 'text';
        input.name = name + '[]';
        input.value = data[name] || '';
        cell.appendChild(input);
        row.appendChild(cell);
      });
      const remove = document.createElement('td');
      remove.innerHTML = '<button type="button" onclick="this.closest(\'tr\').remove()">🗑️</button>';
      row.appendChild(remove);
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
