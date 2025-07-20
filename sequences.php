<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['utilisateur_id'])) {
  header('Location: /login');
  exit;
}

// Récupère les séquences de l'utilisateur
$stmt = $pdo->prepare("
  SELECT s.*, COUNT(sf.id_fiche) AS nb_fiches
  FROM sequences s
  LEFT JOIN sequences_fiches sf ON s.id = sf.id_sequence
  WHERE s.utilisateur_id = ?
  GROUP BY s.id
  ORDER BY s.id DESC
");
$stmt->execute([$_SESSION['utilisateur_id']]);
$sequences = $stmt->fetchAll();

// Limite séquence dynamique
$stmt = $pdo->prepare("SELECT limite_sequences FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['utilisateur_id']]);
$limiteSequences = $stmt->fetchColumn() ?: 1;
$stmt = $pdo->prepare("SELECT COUNT(*) FROM sequences WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['utilisateur_id']]);
$nbSequences = $stmt->fetchColumn();
$limiteSequence = $nbSequences >= $limiteSequences;
?>

<!DOCTYPE html>
<html lang="fr">
<?php $page_title = 'Mes séquences'; include __DIR__ . '/includes/head.php'; ?>
<body class="font-sans bg-gray-50 min-h-screen">
  <?php include __DIR__ . '/includes/header.php'; ?>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pt-20">
    <?php if (isset($_GET['success'])): ?>
      <div class="mb-6 rounded bg-green-50 border border-green-200 text-green-800 px-4 py-3 flex items-center gap-2">
        ✅ Séquence enregistrée ou modifiée avec succès.
      </div>
    <?php endif; ?>
    <div class="flex flex-col items-center mb-8">
      <h1 class="text-3xl md:text-4xl font-bold text-blue-700 mb-8 text-center">🧩 Mes séquences</h1>
      <p class="text-gray-600 text-center mb-6">Gérez vos séquences pédagogiques</p>
      <?php if ($limiteSequence): ?>
        <div class="mb-4 rounded bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-2 flex items-center gap-2">
          ⚠️ Limite atteinte : vous avez déjà créé <?= $nbSequences ?> séquence(s) (limite = <?= $limiteSequences ?>).
        </div>
      <?php endif; ?>
      <a href="/creer_sequence" class="inline-flex items-center justify-center w-full max-w-xs px-6 py-3 bg-blue-600 text-white text-lg font-semibold rounded-lg shadow hover:bg-blue-700 transition mb-4">
        <span class="text-2xl mr-2">➕</span> Créer une séquence
      </a>
    </div>

    <?php if (empty($sequences)): ?>
      <div class="text-center py-12">
        <div class="text-gray-400 text-6xl mb-4">🧩</div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune séquence trouvée</h3>
        <p class="text-gray-600 mb-6">Commencez par créer votre première séquence pédagogique</p>
        <a href="/creer_sequence" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition duration-200">
          Créer ma première séquence
        </a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($sequences as $seq): ?>
          <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition duration-200 border border-gray-200">
            <div class="p-6">
              <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                  <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= htmlspecialchars($seq['titre']) ?></h3>
                  <p class="text-sm text-gray-600 mb-3 line-clamp-3"><?= htmlspecialchars($seq['objectifs']) ?></p>
                  <div class="flex items-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                      <?= $seq['nb_fiches'] ?> séance<?= $seq['nb_fiches'] > 1 ? 's' : '' ?>
                    </span>
                  </div>
                </div>
              </div>
              
              <div class="border-t border-gray-200 pt-4">
                <div class="flex flex-wrap gap-2">
                  <a href="/modifier_sequence/<?= $seq['id'] ?>" class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 text-xs font-medium rounded hover:bg-blue-200 transition duration-200">
                    ✏️ Modifier
                  </a>
                  <a href="/dupliquer_sequence/<?= $seq['id'] ?>" class="inline-flex items-center px-3 py-1.5 bg-green-100 text-green-700 text-xs font-medium rounded hover:bg-green-200 transition duration-200">
                    🧬 Dupliquer
                  </a>
                  <a href="/export_sequence/<?= $seq['id'] ?>" class="inline-flex items-center px-3 py-1.5 bg-purple-100 text-purple-700 text-xs font-medium rounded hover:bg-purple-200 transition duration-200">
                    📄 Exporter
                  </a>
                  <a href="/supprimer_sequence/<?= $seq['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette séquence ?');" class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 text-xs font-medium rounded hover:bg-red-200 transition duration-200">
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
</body>
</html>
