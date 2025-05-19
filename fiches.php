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

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Liste des fiches</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
  <h1>Liste des fiches enregistrées</h1>
  <a href="/">⬅ Retour à l'accueil</a>
  <a href="ajouter" style="color:black; text-decoration:none;">➕ Ajouter une fiche</a>
  <table border="1" cellpadding="8" cellspacing="0">
    <thead>
      <tr>
        <th>ID</th><th>Domaine</th><th>Niveau</th><th>Séquence</th><th>Séance</th><th>Enseignant</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($fiches as $fiche): ?>
        <tr>
          <td><?= $fiche['id'] ?></td>
          <td><?= htmlspecialchars($fiche['domaine']) ?></td>
          <td><?= htmlspecialchars($fiche['niveau']) ?></td>
          <td><?= htmlspecialchars($fiche['sequence']) ?></td>
          <td><?= htmlspecialchars($fiche['seance']) ?></td>
          <td><?= htmlspecialchars($fiche['nom_enseignant']) ?></td>
          <td>
          <a href="/modifier/<?= $fiche['id'] ?>">✏️ Modifier</a> |
          <a href="/dupliquer/<?= $fiche['id'] ?>">🧬 Dupliquer</a> |
          <a href="/supprimer/<?= $fiche['id'] ?>" onclick="return confirm('Supprimer cette fiche ?');">🗑️ Supprimer</a> |
          <!-- <a href="export.php?id=<?= $fiche['id'] ?>&format=word">📄 Export Word</a> | -->
          <a href="/export/<?= $fiche['id'] ?>/pdf">📄 Export PDF</a>

          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>