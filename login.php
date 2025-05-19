<?php
session_start();
require_once __DIR__ . '/includes/config.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'];
  $mot_de_passe = $_POST['mot_de_passe'];

  $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
    $_SESSION['utilisateur_id'] = $user['id'];
    $_SESSION['utilisateur_nom'] = $user['nom'];
    header('Location: fiches');
    exit;
  } else {
    $erreur = "❌ Email ou mot de passe incorrect.";
  }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Connexion</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

  <h1>Connexion 🔐</h1>

  <?php if ($erreur): ?>
    <p style="color:red"><?= $erreur ?></p>
  <?php endif; ?>

  <form method="post">
    <input type="email" name="email" placeholder="Adresse email" required>
    <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
    <button type="submit">Se connecter</button>
  </form>

  <p>Pas encore de compte ? <a href="/inscription">Créer un compte</a></p>
  <p><a href="/">⬅ Retour à l'accueil</a></p>
</body>
</html>
