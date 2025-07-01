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
    objectifs = ?, competences = ?, prerequis = ?, afc = ?,
    evaluation = ?, bilan = ?, prolongement = ?, remediation = ?,
    nom_enseignant = ?, deroulement_json = ?
    WHERE id = ? AND utilisateur_id = ?");

  $stmt->execute([
    $_POST['domaine'], $_POST['niveau'], $_POST['duree'], $_POST['sequence'], $_POST['seance'],
    $_POST['objectifs'], $_POST['competences'], $_POST['prerequis'], $_POST['afc'],
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
      <textarea name="competences" placeholder="Compétences visées"><?= htmlspecialchars($fiche['competences']) ?></textarea>
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
