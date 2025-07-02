<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['utilisateur_id'])) {
  header('Location: /login');
  exit;
}

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $stmt = $pdo->prepare("INSERT INTO fiches (
    domaine, niveau, duree, sequence, seance, objectifs, competences, competences_scccc, afc,
    prerequis, nom_enseignant, deroulement_json,
    evaluation, bilan, prolongement, remediation,
    utilisateur_id
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

  $stmt->execute([
    $_POST['domaine'],
    $_POST['niveau'],
    $_POST['duree'],
    $_POST['sequence'],
    $_POST['seance'],
    $_POST['objectifs'],
    $_POST['competences'],
    $_POST['competences_scccc'] ?? '',
    $_POST['afc'],
    $_POST['prerequis'],
    $_POST['nom_enseignant'],
    $_POST['deroulement_json'],
    $_POST['evaluation'],
    $_POST['bilan'],
    $_POST['prolongement'],
    $_POST['remediation'],
    $_SESSION['utilisateur_id']
  ]);

  $success = "✅ Fiche enregistrée avec succès.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<?php $page_title = 'Ajouter une fiche'; include __DIR__ . '/includes/head.php'; ?>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>
  <div class="container pt-16">
    <h1>Créer une fiche de préparation</h1>
    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>

    <form action="" method="post">
      <select name="domaine" required>
        <option value="">-- Sélectionnez un domaine d'apprentissage --</option>
        <optgroup label="École maternelle (cycle 1)">
          <option value="Mobiliser le langage dans toutes ses dimensions">Mobiliser le langage dans toutes ses dimensions</option>
          <option value="Agir, s'exprimer, comprendre à travers l'activité physique">Agir, s'exprimer, comprendre à travers l'activité physique</option>
          <option value="Agir, s'exprimer, comprendre à travers les activités artistiques">Agir, s'exprimer, comprendre à travers les activités artistiques</option>
          <option value="Construire les premiers outils pour structurer sa pensée">Construire les premiers outils pour structurer sa pensée</option>
          <option value="Explorer le monde">Explorer le monde</option>
        </optgroup>
        <optgroup label="École élémentaire et collège (cycle 2 à 4)">
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
      <input type="text" name="niveau" placeholder="Niveau" required>
      <input type="text" name="duree" placeholder="Durée" required>
      <input type="text" name="sequence" placeholder="Séquence" required>
      <input type="text" name="seance" placeholder="Séance" required>
      <textarea name="objectifs" placeholder="Objectifs visés" required></textarea>
      <select name="competences" required>
        <option value="">-- Sélectionnez une compétence visée --</option>
        <optgroup label="Mobiliser le langage dans toutes ses dimensions">
          <option value="Oser entrer en communication">Oser entrer en communication</option>
          <option value="Comprendre et apprendre">Comprendre et apprendre</option>
          <option value="Échanger et réfléchir avec les autres">Échanger et réfléchir avec les autres</option>
          <option value="Se préparer à apprendre à lire">Se préparer à apprendre à lire</option>
          <option value="Développer la conscience phonologique">Développer la conscience phonologique</option>
          <option value="Comprendre le principe alphabétique">Comprendre le principe alphabétique</option>
          <option value="Produire des discours variés">Produire des discours variés</option>
          <option value="Découvrir les fonctions de l'écrit">Découvrir les fonctions de l'écrit</option>
          <option value="Commencer à produire des écrits">Commencer à produire des écrits</option>
          <option value="Se familiariser avec l'écrit dans toutes ses formes">Se familiariser avec l'écrit dans toutes ses formes</option>
        </optgroup>
        <optgroup label="Agir, s'exprimer, comprendre à travers l'activité physique">
          <option value="Agir dans des environnements variés">Agir dans des environnements variés</option>
          <option value="Adapter ses déplacements à des contraintes">Adapter ses déplacements à des contraintes</option>
          <option value="Coopérer et s'opposer individuellement ou collectivement">Coopérer et s'opposer individuellement ou collectivement</option>
          <option value="Exprimer des intentions par le geste">Exprimer des intentions par le geste</option>
          <option value="Apprendre à respecter des règles">Apprendre à respecter des règles</option>
          <option value="Développer sa motricité fine et globale">Développer sa motricité fine et globale</option>
          <option value="Se repérer dans l'espace avec son corps">Se repérer dans l'espace avec son corps</option>
        </optgroup>
        <optgroup label="Agir, s'exprimer, comprendre à travers les activités artistiques">
          <option value="Expérimenter les matériaux, les outils, les supports">Expérimenter les matériaux, les outils, les supports</option>
          <option value="Créer des productions plastiques et visuelles">Créer des productions plastiques et visuelles</option>
          <option value="Observer et décrire des œuvres">Observer et décrire des œuvres</option>
          <option value="Explorer des univers sonores">Explorer des univers sonores</option>
          <option value="Participer à des jeux vocaux et corporels">Participer à des jeux vocaux et corporels</option>
          <option value="Chanter seul et en groupe">Chanter seul et en groupe</option>
          <option value="Jouer avec sa voix et son corps">Jouer avec sa voix et son corps</option>
          <option value="Imaginer, inventer, interpréter">Imaginer, inventer, interpréter</option>
        </optgroup>
        <optgroup label="Construire les premiers outils pour structurer sa pensée">
          <option value="Dénombrer des quantités">Dénombrer des quantités</option>
          <option value="Associer un nombre à une quantité">Associer un nombre à une quantité</option>
          <option value="Utiliser le comptage pour résoudre des problèmes">Utiliser le comptage pour résoudre des problèmes</option>
          <option value="Comprendre les nombres comme positions">Comprendre les nombres comme positions</option>
          <option value="Utiliser les premiers symboles mathématiques">Utiliser les premiers symboles mathématiques</option>
          <option value="Reproduire, compléter, créer des suites logiques">Reproduire, compléter, créer des suites logiques</option>
          <option value="Reconnaître et nommer des formes">Reconnaître et nommer des formes</option>
          <option value="Comparer, classer des objets selon des critères">Comparer, classer des objets selon des critères</option>
          <option value="Se repérer dans le temps court (journée, semaine)">Se repérer dans le temps court (journée, semaine)</option>
        </optgroup>
        <optgroup label="Explorer le monde">
          <option value="Découvrir les objets, matières, phénomènes du vivant">Découvrir les objets, matières, phénomènes du vivant</option>
          <option value="Utiliser ses sens pour observer">Utiliser ses sens pour observer</option>
          <option value="Identifier les caractéristiques du vivant et des objets">Identifier les caractéristiques du vivant et des objets</option>
          <option value="Se repérer dans le temps (jours, mois, saisons)">Se repérer dans le temps (jours, mois, saisons)</option>
          <option value="Se repérer dans l'espace (école, classe, parcours)">Se repérer dans l'espace (école, classe, parcours)</option>
          <option value="Découvrir l'usage d'objets techniques simples">Découvrir l'usage d'objets techniques simples</option>
          <option value="Manipuler des outils numériques">Manipuler des outils numériques</option>
          <option value="Observer les effets de ses actions sur l'environnement">Observer les effets de ses actions sur l'environnement</option>
        </optgroup>
      </select>

      <div id="competences_scccc_container" style="display: none;">
        <select name="competences_scccc">
          <option value="">-- Sélectionnez une compétence du SCCCC --</option>
          <option value="Comprendre, s'exprimer en utilisant la langue française à l'oral et à l'écrit">Comprendre, s'exprimer en utilisant la langue française à l'oral et à l'écrit</option>
          <option value="Comprendre, s'exprimer en utilisant une langue étrangère et, le cas échéant, une langue régionale">Comprendre, s'exprimer en utilisant une langue étrangère et, le cas échéant, une langue régionale</option>
          <option value="Comprendre, s'exprimer en utilisant les langages mathématiques, scientifiques et informatiques">Comprendre, s'exprimer en utilisant les langages mathématiques, scientifiques et informatiques</option>
          <option value="Comprendre, s'exprimer en utilisant les langages des arts et du corps">Comprendre, s'exprimer en utilisant les langages des arts et du corps</option>
          <option value="Apprendre à apprendre, seul ou collectivement, en classe ou en dehors">Apprendre à apprendre, seul ou collectivement, en classe ou en dehors</option>
          <option value="Maîtriser les techniques usuelles de l'information et de la documentation">Maîtriser les techniques usuelles de l'information et de la documentation</option>
          <option value="Mobiliser des outils numériques pour apprendre, échanger, communiquer">Mobiliser des outils numériques pour apprendre, échanger, communiquer</option>
          <option value="Comprendre les règles et le droit">Comprendre les règles et le droit</option>
          <option value="Respecter autrui et accepter les différences">Respecter autrui et accepter les différences</option>
          <option value="Agir de façon éthique et responsable">Agir de façon éthique et responsable</option>
          <option value="Faire preuve de réflexion et de discernement">Faire preuve de réflexion et de discernement</option>
          <option value="Se situer dans l'espace et dans le temps">Se situer dans l'espace et dans le temps</option>
          <option value="Analyser et comprendre les organisations humaines et les représentations du monde">Analyser et comprendre les organisations humaines et les représentations du monde</option>
          <option value="Raisonner, imaginer, élaborer, produire">Raisonner, imaginer, élaborer, produire</option>
        </select>
      </div>
      <textarea name="afc" placeholder="AFC"></textarea>
      <textarea name="prerequis" placeholder="Prérequis" required></textarea>
      <input type="text" name="nom_enseignant" placeholder="Nom de l'enseignant" required>
      <textarea name="evaluation" placeholder="Modalités d'évaluation"></textarea>
      <textarea name="bilan" placeholder="Bilan pédagogique et didactique"></textarea>
      <textarea name="prolongement" placeholder="Prolongement(s) possible(s)"></textarea>
      <textarea name="remediation" placeholder="Remédiation(s) éventuelle(s)"></textarea>

      <h3 class="text-lg font-bold text-gray-800 mb-2">Déroulement de la séance</h3>
      <div class="overflow-x-auto">
        <table id="deroulement-table" class="min-w-full w-full border border-gray-200 rounded-lg text-sm bg-gray-50">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-2 border-b whitespace-normal w-40">Phase & durée</th>
              <th class="px-4 py-2 border-b whitespace-normal w-56">Déroulement</th>
              <th class="px-4 py-2 border-b whitespace-normal w-40">Consigne</th>
              <th class="px-4 py-2 border-b whitespace-normal w-40">Rôle enseignant</th>
              <th class="px-4 py-2 border-b whitespace-normal w-40">Rôle élève</th>
              <th class="px-4 py-2 border-b whitespace-normal w-48">Différenciation</th>
              <th class="px-4 py-2 border-b whitespace-normal w-40">Matériel</th>
              <th class="px-2 py-2 border-b w-10"></th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
      <button type="button" onclick="addDeroulementRow()">➕ Ajouter une ligne</button>
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
