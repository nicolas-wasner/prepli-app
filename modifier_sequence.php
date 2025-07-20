<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['utilisateur_id'])) {
  header('Location: /login');
  exit;
}

$id = (int) ($_GET['id'] ?? 0);

// Vérifie que la séquence appartient à l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM sequences WHERE id = ? AND utilisateur_id = ?");
$stmt->execute([$id, $_SESSION['utilisateur_id']]);
$sequence = $stmt->fetch();

if (!$sequence) {
  echo "<p>❌ Séquence introuvable ou non autorisée.</p>";
  exit;
}

// Récupère toutes les fiches de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM fiches WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['utilisateur_id']]);
$fiches = $stmt->fetchAll();

// Récupère les fiches associées à cette séquence
$stmt = $pdo->prepare("SELECT id_fiche FROM sequences_fiches WHERE id_sequence = ?");
$stmt->execute([$id]);
$fiches_associees = array_column($stmt->fetchAll(), 'id_fiche');

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $titre = trim($_POST['titre']);
  $objectifs = trim($_POST['objectifs']);
  $competences = trim($_POST['competences']);
  $materiel = trim($_POST['materiel']);
  $bilan = trim($_POST['bilan'] ?? '');
  $evaluation = trim($_POST['evaluation']);
  $prolongement = trim($_POST['prolongement'] ?? '');
  $selectionnees = $_POST['fiches'] ?? [];
  $ordre_fiches = array_filter(array_map('intval', explode(',', $_POST['ordre_fiches'] ?? '')));

  if ($titre && $objectifs && $competences && $materiel && $evaluation) {
    $stmt = $pdo->prepare("UPDATE sequences SET titre = ?, objectifs = ?, competences = ?, materiel = ?, bilan = ?, evaluation = ?, prolongement = ? WHERE id = ?");
    $stmt->execute([$titre, $objectifs, $competences, $materiel, $bilan, $evaluation, $prolongement, $id]);
    $pdo->prepare("DELETE FROM sequences_fiches WHERE id_sequence = ?")->execute([$id]);
    $stmt = $pdo->prepare("INSERT INTO sequences_fiches (id_sequence, id_fiche) VALUES (?, ?)");
    foreach ($ordre_fiches as $fiche_id) {
      $stmt->execute([$id, $fiche_id]);
    }
    $success = "✅ Séquence mise à jour avec succès.";
    header('Location: /sequences?success=1');
    exit;
  } else {
    $error = "❌ Merci de remplir tous les champs obligatoires.";
  }
}
?>

<!DOCTYPE html>
<html lang="fr">
<?php $page_title = 'Modifier séquence'; include __DIR__ . '/includes/head.php'; ?>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>

  <div class="container pt-16">
    <h1>✏️ Modifier la séquence « <?= htmlspecialchars($sequence['titre']) ?> »</h1>

    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>

    <form method="post" class="space-y-6 max-w-xl bg-white rounded-xl shadow p-8 mt-8">
      <label class="block mb-2 font-semibold text-gray-700">Titre :
        <input type="text" name="titre" value="<?= htmlspecialchars($sequence['titre']) ?>" required class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </label>

      <label class="block mb-2 font-semibold text-gray-700">Objectifs visé(s) de la séquence <span class="text-red-500">*</span> :
        <textarea name="objectifs" required class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($sequence['objectifs'] ?? '') ?></textarea>
      </label>
      <label class="block mb-2 font-semibold text-gray-700">Compétence(s) visée(s) de la séquence <span class="text-red-500">*</span> :
        <textarea name="competences" required class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($sequence['competences'] ?? '') ?></textarea>
      </label>
      <label class="block mb-2 font-semibold text-gray-700">Matériel <span class="text-red-500">*</span> :
        <textarea name="materiel" required class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($sequence['materiel'] ?? '') ?></textarea>
      </label>
      <label class="block mb-2 font-semibold text-gray-700">Bilan pédagogique et didactique <span class="text-gray-400">(optionnel)</span> :
        <textarea name="bilan" class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($sequence['bilan'] ?? '') ?></textarea>
      </label>
      <label class="block mb-2 font-semibold text-gray-700">Modalité(s) d'évaluation <span class="text-red-500">*</span> :
        <textarea name="evaluation" required class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($sequence['evaluation'] ?? '') ?></textarea>
      </label>
      <label class="block mb-2 font-semibold text-gray-700">Prolongement possible(s) <span class="text-gray-400">(optionnel)</span> :
        <textarea name="prolongement" class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($sequence['prolongement'] ?? '') ?></textarea>
      </label>

      <label class="block mb-2 font-semibold text-gray-700">Fiches associées :</label>
      <div class="space-y-2" id="fiches-checkboxes">
        <?php foreach ($fiches as $fiche): ?>
          <label class="flex items-center gap-2 text-gray-700">
            <input type="checkbox" name="fiches[]" value="<?= $fiche['id'] ?>" <?= in_array($fiche['id'], $fiches_associees) ? 'checked' : '' ?> class="form-checkbox h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 fiche-checkbox align-middle" style="min-width:1.25rem; min-height:1.25rem;" />
            <span><?= htmlspecialchars($fiche['sequence']) ?> – <?= htmlspecialchars($fiche['seance']) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
      <input type="hidden" name="ordre_fiches" id="ordre_fiches" value="">
      <div id="recap-fiches" class="mt-6"></div>
      <button type="submit" class="mt-6 px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition flex items-center gap-2"><span>💾</span> Enregistrer les modifications</button>
    </form>
  </div>
  <script>
    const fichesData = <?php echo json_encode(array_map(function($fiche) {
      return [
        'id' => $fiche['id'],
        'seance' => $fiche['seance'],
        'duree' => $fiche['duree'],
        'objectifs' => $fiche['objectifs'],
        'sequence' => $fiche['sequence']
      ];
    }, $fiches)); ?>;
    // Initialisation de l'ordre avec les fiches associées déjà cochées
    let selectedOrder = <?php echo json_encode(array_map('strval', $fiches_associees)); ?>;
    function updateRecapTable() {
      // Synchronise selectedOrder avec les cases cochées
      const checked = Array.from(document.querySelectorAll('.fiche-checkbox:checked')).map(cb => cb.value);
      selectedOrder = selectedOrder.filter(id => checked.includes(id));
      checked.forEach(id => { if (!selectedOrder.includes(id)) selectedOrder.push(id); });
      document.getElementById('ordre_fiches').value = selectedOrder.join(',');
      const recapDiv = document.getElementById('recap-fiches');
      if (selectedOrder.length === 0) { recapDiv.innerHTML = ''; return; }
      let html = '<div class="overflow-x-auto"><table class="min-w-full w-full bg-white border border-gray-300 rounded-lg shadow-sm overflow-hidden text-sm"><thead><tr class="bg-gray-100 text-gray-700">';
      html += '<th class="px-3 py-2 w-24 text-center border-b border-gray-300">N° séance</th>';
      html += '<th class="px-3 py-2 w-56 text-center border-b border-gray-300">Titre de la séance</th>';
      html += '<th class="px-3 py-2 w-24 text-center border-b border-gray-300">Durée</th>';
      html += '<th class="px-3 py-2 min-w-[180px] text-center border-b border-gray-300">Objectifs</th>';
      html += '<th class="px-3 py-2 w-24 text-center border-b border-gray-300">Ordre</th>';
      html += '</tr></thead><tbody>';
      selectedOrder.forEach((id, idx) => {
        const fiche = fichesData.find(f => f.id.toString() === id);
        if (!fiche) return;
        html += `<tr class="border-b border-gray-200">`;
        html += `<td class="px-3 py-2 text-center align-middle">${idx+1}/${selectedOrder.length}</td>`;
        html += `<td class="px-3 py-2 text-center align-middle break-words">${fiche.seance}</td>`;
        html += `<td class="px-3 py-2 text-center align-middle">${fiche.duree}</td>`;
        html += `<td class="px-3 py-2 text-center align-middle break-words whitespace-pre-line">${fiche.objectifs}</td>`;
        html += `<td class="px-1 py-2 flex gap-2 justify-center align-middle">`;
        html += `<button type="button" class="move-up flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-700 rounded-full shadow hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-400" data-idx="${idx}"><span aria-hidden="true" class="text-lg">▲</span></button>`;
        html += `<button type="button" class="move-down flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-700 rounded-full shadow hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-400 ml-1" data-idx="${idx}"><span aria-hidden="true" class="text-lg">▼</span></button>`;
        html += '</td></tr>';
      });
      html += '</tbody></table></div>';
      recapDiv.innerHTML = html;
      recapDiv.querySelectorAll('.move-up').forEach(btn => {
        btn.onclick = function() {
          const i = parseInt(this.dataset.idx);
          if (i > 0) {
            [selectedOrder[i-1], selectedOrder[i]] = [selectedOrder[i], selectedOrder[i-1]];
            updateRecapTable();
          }
        };
      });
      recapDiv.querySelectorAll('.move-down').forEach(btn => {
        btn.onclick = function() {
          const i = parseInt(this.dataset.idx);
          if (i < selectedOrder.length-1) {
            [selectedOrder[i+1], selectedOrder[i]] = [selectedOrder[i], selectedOrder[i+1]];
            updateRecapTable();
          }
        };
      });
    }
    document.querySelectorAll('.fiche-checkbox').forEach(cb => {
      cb.addEventListener('change', updateRecapTable);
    });
    updateRecapTable();
  </script>
</body>
</html>
