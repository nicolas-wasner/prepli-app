<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['utilisateur_id'])) {
  header('Location: /login');
  exit;
}

$success = '';
$erreur = '';
$limiteAtteinte = false;
$limiteMessage = '';
// Récupère la limite personnalisée pour l'utilisateur
$stmt = $pdo->prepare("SELECT limite_fiches, limite_sequences FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['utilisateur_id']]);
$limites = $stmt->fetch();
$limiteFiches = $limites['limite_fiches'] ?? 1;
$limiteSequences = $limites['limite_sequences'] ?? 1;
// Vérifie la limite séquence
$stmt = $pdo->prepare("SELECT COUNT(*) FROM sequences WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['utilisateur_id']]);
$nbSequences = $stmt->fetchColumn();
if ($nbSequences >= $limiteSequences) {
  $limiteAtteinte = true;
  $limiteMessage = "❌ Limite atteinte : vous ne pouvez créer que $limiteSequences séquence(s).";
}
// Vérifie la limite fiche
$stmt = $pdo->prepare("SELECT COUNT(*) FROM fiches WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['utilisateur_id']]);
$nbFiches = $stmt->fetchColumn();
if ($nbFiches >= $limiteFiches) {
  $limiteAtteinte = true;
  $limiteMessage = "❌ Limite atteinte : vous ne pouvez créer que $limiteFiches fiche(s) et $limiteSequences séquence(s).";
}

// Récupère les fiches de l'utilisateur connecté
$stmt = $pdo->prepare("SELECT * FROM fiches WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['utilisateur_id']]);
$fiches = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $limiteAtteinte) {
  $success = '';
  $erreur = $limiteMessage;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $titre = trim($_POST['titre']);
  $objectifs = trim($_POST['objectifs']);
  $competences = trim($_POST['competences']);
  $materiel = trim($_POST['materiel']);
  $bilan = trim($_POST['bilan'] ?? '');
  $evaluation = trim($_POST['evaluation']);
  $prolongement = trim($_POST['prolongement'] ?? '');
  $ordre_fiches = array_filter(array_map('intval', explode(',', $_POST['ordre_fiches'] ?? '')));

  if ($titre && $objectifs && $competences && $materiel && $evaluation && count($ordre_fiches) > 0) {
    $stmt = $pdo->prepare("INSERT INTO sequences (titre, objectifs, competences, materiel, bilan, evaluation, prolongement, utilisateur_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$titre, $objectifs, $competences, $materiel, $bilan, $evaluation, $prolongement, $_SESSION['utilisateur_id']]);
    $id_sequence = $pdo->lastInsertId();
    $stmt = $pdo->prepare("INSERT INTO sequences_fiches (id_sequence, id_fiche) VALUES (?, ?)");
    foreach ($ordre_fiches as $fiche_id) {
      $stmt->execute([$id_sequence, $fiche_id]);
    }
    $success = "✅ Séquence créée avec succès.";
    header('Location: /sequences?success=1');
    exit;
  } else {
    $erreur = "❌ Merci de remplir tous les champs obligatoires et de sélectionner au moins une fiche.";
  }
}
?>

<!DOCTYPE html>
<html lang="fr">
<?php $page_title = 'Créer une séquence'; include __DIR__ . '/includes/head.php'; ?>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>

  <div class="container pt-16">
    <h1>Créer une séquence 📚</h1>
    <?php if ($limiteAtteinte): ?>
      <div class="mb-6 rounded bg-red-50 border border-red-200 text-red-800 px-4 py-3 flex items-center gap-2">
        <?= $limiteMessage ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>
    <?php if ($erreur): ?><p style="color:red;"><?= $erreur ?></p><?php endif; ?>

    <form method="post" class="space-y-6 max-w-xl bg-white rounded-xl shadow p-8 mt-8" <?php if ($limiteAtteinte) echo 'style="pointer-events:none;opacity:0.5;"'; ?>>
      <label class="block mb-2 font-semibold text-gray-700">Titre de la séquence :
        <input type="text" name="titre" required class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </label>

      <label class="block mb-2 font-semibold text-gray-700">Objectifs visé(s) de la séquence <span class="text-red-500">*</span> :
        <textarea name="objectifs" required class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
      </label>
      <label class="block mb-2 font-semibold text-gray-700">Compétence(s) visée(s) de la séquence <span class="text-red-500">*</span> :
        <textarea name="competences" required class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
      </label>
      <label class="block mb-2 font-semibold text-gray-700">Matériel <span class="text-red-500">*</span> :
        <textarea name="materiel" required class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
      </label>
      <label class="block mb-2 font-semibold text-gray-700">Bilan pédagogique et didactique <span class="text-gray-400">(optionnel)</span> :
        <textarea name="bilan" class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
      </label>
      <label class="block mb-2 font-semibold text-gray-700">Modalité(s) d'évaluation <span class="text-red-500">*</span> :
        <textarea name="evaluation" required class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
      </label>
      <label class="block mb-2 font-semibold text-gray-700">Prolongement possible(s) <span class="text-gray-400">(optionnel)</span> :
        <textarea name="prolongement" class="w-full mt-1 p-2 border border-gray-300 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
      </label>

      <label class="block mb-2 font-semibold text-gray-700">Fiches à inclure :</label>
      <div class="space-y-2" id="fiches-checkboxes">
        <?php foreach ($fiches as $fiche): ?>
          <label class="flex items-center gap-2 text-gray-700">
            <input type="checkbox" name="fiches[]" value="<?= $fiche['id'] ?>" class="form-checkbox h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 fiche-checkbox align-middle" style="min-width:1.25rem; min-height:1.25rem;" />
            <span><?= htmlspecialchars($fiche['sequence']) ?> – <?= htmlspecialchars($fiche['seance']) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
      <input type="hidden" name="ordre_fiches" id="ordre_fiches" value="">
      <div id="recap-fiches" class="mt-6"></div>
      <button type="submit" class="mt-6 px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition flex items-center gap-2"><span>💾</span> Enregistrer la séquence</button>
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
    let selectedOrder = [];
    function updateRecapTable() {
      // Synchronise selectedOrder avec les cases cochées
      const checked = Array.from(document.querySelectorAll('.fiche-checkbox:checked')).map(cb => cb.value);
      // Retire les fiches décochées
      selectedOrder = selectedOrder.filter(id => checked.includes(id));
      // Ajoute les nouvelles fiches cochées à la fin
      checked.forEach(id => { if (!selectedOrder.includes(id)) selectedOrder.push(id); });
      // Met à jour le champ caché
      document.getElementById('ordre_fiches').value = selectedOrder.join(',');
      // Affichage du tableau
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
      // Ajout listeners flèches
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
    // Initialisation
    updateRecapTable();
  </script>
</body>
</html>
