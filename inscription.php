<?php
session_start();
require_once __DIR__ . '/includes/config.php';

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nom = trim($_POST['nom']);
  $email = trim($_POST['email']);
  $mot_de_passe = $_POST['mot_de_passe'];

  // Vérifier si l'utilisateur existe déjà
  $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
  $stmt->execute([$email]);

  if ($stmt->fetch()) {
    $erreur = "❌ Cet email est déjà utilisé.";
  } else {
    // Créer le compte
    $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe) VALUES (?, ?, ?)");
    $stmt->execute([$nom, $email, $hash]);

    // Connexion immédiate
    $_SESSION['utilisateur_id'] = $pdo->lastInsertId();
    $_SESSION['utilisateur_nom'] = $nom;

    header('Location: fiches.php');
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Inscription</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

  <h1>Créer un compte 👤</h1>

  <?php if ($erreur): ?>
    <p style="color:red"><?= $erreur ?></p>
  <?php endif; ?>

  <form method="post">
    <input type="text" name="nom" placeholder="Votre nom" required>
    <input type="email" name="email" placeholder="Adresse email" required>
    <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
    <button type="submit">S’inscrire</button>
  </form>

  <p>Déjà inscrit ? <a href="/login">Se connecter</a></p>
  <p><a href="/">⬅ Retour à l'accueil</a></p>
</body>
</html>
