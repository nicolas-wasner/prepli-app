<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Accueil – Application PrepLi</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .container {
      text-align: center;
      margin-top: 4rem;
    }
    .container h1 {
      font-size: 2.2rem;
      margin-bottom: 2rem;
    }
    .menu {
      display: flex;
      flex-direction: column;
      gap: 1rem;
      max-width: 320px;
      margin: 0 auto;
    }
    .menu a {
      display: block;
      background-color: #3498db;
      color: white;
      padding: 1rem;
      text-decoration: none;
      font-weight: bold;
      border-radius: 8px;
      transition: background 0.3s;
    }
    .menu a:hover {
      background-color: #287bb5;
    }
    .user-box {
      margin-top: 2rem;
      font-size: 1rem;
      color: #555;
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
  <div class="container">
    <h1>Bienvenue dans l'application PrepLi 📚</h1>

    <div class="menu">
      <?php if (isset($_SESSION['utilisateur_id'])): ?>
        <a href="fiches.php">📄 Mes fiches</a>
        <a href="sequences.php">🧩 Mes séquences</a>
        <a href="logout.php">🚪 Se déconnecter</a>
      <?php else: ?>
        <a href="login.php">🔐 Connexion</a>
        <a href="inscription.php">🆕 Créer un compte</a>
      <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['utilisateur_nom'])): ?>
      <div class="user-box">
        Connecté en tant que <strong><?= htmlspecialchars($_SESSION['utilisateur_nom']) ?></strong>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
