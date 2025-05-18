<?php
session_start();
require_once __DIR__ . '/includes/config.php';

// Masquer les erreurs deprecated PHP 8.1+
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_DEPRECATED);

if (!isset($_SESSION['utilisateur_id'])) {
  header('Location: login.php');
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

$success = '';

// Enregistrement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $stmt = $pdo->prepare("UPDATE fiches SET
    domaine = ?, niveau = ?, duree = ?, sequence = ?, seance = ?,
    objectifs = ?, competences = ?, prerequis = ?, nom_enseignant = ?,
    deroulement_json = ?
    WHERE id = ? AND utilisateur_id = ?");

  $stmt->execute([
    $_POST['domaine'],
    $_POST['niveau'],
    $_POST['duree'],
    $_POST['sequence'],
    $_POST['seance'],
    $_POST['objectifs'],
    $_POST['competences'],
    $_POST['prerequis'],
    $_POST['nom_enseignant'],
    $_POST['deroulement_json'],
    $id,
    $_SESSION['utilisateur_id']
  ]);

  $success = "✅ Fiche mise à jour avec succès.";
}

// Données à afficher
$deroulement_data = json_decode($fiche['deroulement_json'] ?? '[]', true);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Modifier fiche</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>
  <div class="container">
    <h1>✏️ Modifier la fiche « <?= htmlspecialchars((string) $fiche['seance']) ?> »</h1>

    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>

    <form method="post">
      <input type="text" name="domaine" placeholder="Domaine" value="<?= htmlspecialchars((string) $fiche['domaine']) ?>" required>
      <input type="text" name="niveau" placeholder="Niveau" value="<?= htmlspecialchars((string) $fiche['niveau']) ?>" required>
      <input type="text" name="duree" placeholder="Durée" value="<?= htmlspecialchars((string) $fiche['duree']) ?>" required>
      <input type="text" name="sequence" placeholder="Séquence" value="<?= htmlspecialchars((string) $fiche['sequence']) ?>" required>
      <input type="text" name="seance" placeholder="Séance" value="<?= htmlspecialchars((string) $fiche['seance']) ?>" required>
      <textarea name="objectifs" placeholder="Objectifs visés"><?= htmlspecialchars((string) $fiche['objectifs']) ?></textarea>
      <textarea name="competences" placeholder="Compétences visées"><?= htmlspecialchars((string) $fiche['competences']) ?></textarea>
      <textarea name="prerequis" placeholder="Prérequis"><?= htmlspecialchars((string) $fiche['prerequis']) ?></textarea>
      <input type="text" name="nom_enseignant" placeholder="Nom de l'enseignant" value="<?= htmlspecialchars((string) $fiche['nom_enseignant']) ?>">

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
