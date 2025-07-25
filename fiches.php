<?php

session_start();
if (!isset($_SESSION['utilisateur_id'])) {
  header('Location: /login');
  exit;
}

require_once __DIR__ . '/includes/config.php';
$stmt = $pdo->prepare("SELECT * FROM fiches WHERE utilisateur_id = ? ORDER BY id DESC");
$stmt->execute([$_SESSION['utilisateur_id']]);
$fiches = $stmt->fetchAll();

// Limite fiche dynamique
$stmt = $pdo->prepare("SELECT limite_fiches FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['utilisateur_id']]);
$limiteFiches = $stmt->fetchColumn() ?: 1;
$stmt = $pdo->prepare("SELECT COUNT(*) FROM fiches WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['utilisateur_id']]);
$nbFiches = $stmt->fetchColumn();
$limiteFiche = $nbFiches >= $limiteFiches;
?>

<!DOCTYPE html>
<html lang="fr">
<?php $page_title = 'Liste des fiches'; include __DIR__ . '/includes/head.php'; ?>
<body class="font-sans bg-gray-50 min-h-screen">
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pt-20">
  <div class="flex flex-col items-center mb-8">
    <h1 class="text-3xl md:text-4xl font-bold text-blue-700 mb-8 text-center">Mes fiches de préparation</h1>
    <p class="text-gray-600 text-center mb-6">Gérez vos fiches de préparation de séances</p>
    <?php if ($limiteFiche): ?>
      <div class="mb-4 rounded bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-2 flex items-center gap-2">
        ⚠️ Limite atteinte : vous avez déjà créé <?= $nbFiches ?> fiche(s) (limite = <?= $limiteFiches ?>).
      </div>
    <?php endif; ?>
    <a href="/ajouter" class="inline-flex items-center justify-center w-full max-w-xs px-6 py-3 bg-blue-600 text-white text-lg font-semibold rounded-lg shadow hover:bg-blue-700 transition mb-4">
      <span class="text-2xl mr-2">➕</span> Ajouter une fiche
    </a>
  </div>

  <?php if (isset($_GET['success'])): ?>
    <div class="mb-6 rounded bg-green-50 border border-green-200 text-green-800 px-4 py-3 flex items-center gap-2">
      ✅ Fiche enregistrée ou modifiée avec succès.
    </div>
  <?php endif; ?>

  <?php if (empty($fiches)): ?>
    <div class="text-center py-12">
      <div class="text-gray-400 text-6xl mb-4">📄</div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune fiche trouvée</h3>
      <p class="text-gray-600 mb-6">Commencez par créer votre première fiche de préparation</p>
      <a href="/ajouter" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition duration-200">
        Créer ma première fiche
      </a>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($fiches as $fiche): ?>
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition duration-200 border border-gray-200">
          <div class="p-6">
            <div class="flex items-start justify-between mb-4">
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 mb-1"><?= htmlspecialchars($fiche['seance']) ?></h3>
                <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($fiche['sequence']) ?></p>
                <div class="flex flex-wrap gap-2">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <?= htmlspecialchars($fiche['domaine']) ?>
                  </span>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <?= htmlspecialchars($fiche['niveau']) ?>
                  </span>
                </div>
              </div>
            </div>
            
            <div class="border-t border-gray-200 pt-4">
              <p class="text-sm text-gray-600 mb-4">
                <span class="font-medium">Enseignant :</span> <?= htmlspecialchars($fiche['nom_enseignant']) ?>
              </p>
              
              <div class="flex flex-wrap gap-2">
                <a href="/modifier/<?= $fiche['id'] ?>" class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 text-xs font-medium rounded hover:bg-blue-200 transition duration-200">
                  ✏️ Modifier
                </a>
                <a href="/dupliquer/<?= $fiche['id'] ?>" class="inline-flex items-center px-3 py-1.5 bg-green-100 text-green-700 text-xs font-medium rounded hover:bg-green-200 transition duration-200">
                  🧬 Dupliquer
                </a>
                <a href="/export/<?= $fiche['id'] ?>/pdf" class="inline-flex items-center px-3 py-1.5 bg-purple-100 text-purple-700 text-xs font-medium rounded hover:bg-purple-200 transition duration-200">
                  📄 Export PDF
                </a>
                <a href="/supprimer/<?= $fiche['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette fiche ?');" class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 text-xs font-medium rounded hover:bg-red-200 transition duration-200">
                  🗑️ Supprimer
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include_once 'includes/footer.php'; ?>
</body>
</html>
