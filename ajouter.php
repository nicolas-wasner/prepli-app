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
        domaine, niveau, duree, sequence, seance, objectifs, competences, afc,
        prerequis, nom_enseignant, deroulement_json,
        evaluation, bilan, prolongement, remediation,
        utilisateur_id
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");



  $stmt->execute([
    $_POST['domaine'],
    $_POST['niveau'],
    $_POST['duree'],
    $_POST['sequence'],
    $_POST['seance'],
    $_POST['objectifs'],
    $_POST['competences'],
    $_POST['afc'],
    $_POST['prerequis'],
    $_POST['nom_enseignant'],
    $_POST['deroulement_json'],
    $_SESSION['utilisateur_id'],
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
<head>
  <meta charset="UTF-8">
  <title>Ajouter une fiche</title>
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>
  <div class="container">
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
      <textarea name="competences" placeholder="Compétences visées" required></textarea>
      <textarea name="afc" placeholder="AFC"></textarea>
      <textarea name="prerequis" placeholder="Prérequis" required></textarea>
      <input type="text" name="nom_enseignant" placeholder="Nom de l'enseignant" required>
      <textarea name="evaluation" placeholder="Modalités d’évaluation"></textarea>
      <textarea name="bilan" placeholder="Bilan pédagogique et didactique"></textarea>
      <textarea name="prolongement" placeholder="Prolongement(s) possible(s)"></textarea>
      <textarea name="remediation" placeholder="Remédiation(s) éventuelle(s)"></textarea>

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


      <button type="submit">💾 Enregistrer</button>
    </form>
  </div>

  <script>
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
