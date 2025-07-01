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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mes séquences</title>
  <style>
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 2rem;
    }
    th, td {
      border: 1px solid #ccc;
      padding: 0.5rem;
    }
    th {
      background: #f0f0f0;
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>

  <div class="container">
    <h1>🧩 Mes séquences</h1>

    <p><a href="/creer_sequence">➕ Créer une nouvelle séquence</a></p>

    <?php if (count($sequences) === 0): ?>
      <p>Aucune séquence enregistrée.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Titre</th>
            <th>Description</th>
            <th>Nb de séances</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($sequences as $seq): ?>
            <tr>
              <td><?= $seq['id'] ?></td>
              <td><?= htmlspecialchars($seq['titre']) ?></td>
              <td><?= nl2br(htmlspecialchars($seq['description'])) ?></td>
              <td><?= $seq['nb_fiches'] ?></td>
              <td>
                <a href="/modifier_sequence/<?= $seq['id'] ?>">✏️ Modifier</a> |
                <a href="/dupliquer_sequence/<?= $seq['id'] ?>">🧬 Dupliquer</a> |
                <a href="/export_sequence/<?= $seq['id'] ?>">📄 Exporter</a> |
                <a href="/supprimer_sequence/<?= $seq['id'] ?>" onclick="return confirm('Supprimer cette séquence ?');">🗑️ Supprimer</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</body>
</html>
