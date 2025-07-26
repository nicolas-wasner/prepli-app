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
    $erreur = "Cet email est déjà utilisé.";
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
<?php $page_title = 'Inscription'; include __DIR__ . '/includes/head.php'; ?>
<body class="font-sans bg-gray-50 min-h-screen">
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 pt-20">
  <div class="max-w-md w-full space-y-8">
    <div>
      <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-green-100">
        <span class="text-2xl">👤</span>
      </div>
      <h1 class="mt-6 text-center text-3xl md:text-4xl font-bold text-blue-700 mb-8">Créer votre compte</h1>
      <p class="mt-2 text-center text-sm text-gray-600">
        Rejoignez PrepLi pour créer vos fiches de préparation
      </p>
    </div>
    
    <form class="mt-8 space-y-6" method="post">
      <?php if ($erreur): ?>
        <div class="rounded-md bg-red-50 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-red-700"><?= htmlspecialchars($erreur) ?></p>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <div class="space-y-4">
        <div>
          <label for="nom" class="block text-sm font-medium text-gray-700">
            Nom complet
          </label>
          <input id="nom" name="nom" type="text" required 
                 class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                 placeholder="Votre nom complet">
        </div>
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">
            Adresse email
          </label>
          <input id="email" name="email" type="email" required 
                 class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                 placeholder="votre@email.com">
        </div>
        <div>
          <label for="mot_de_passe" class="block text-sm font-medium text-gray-700">
            Mot de passe
          </label>
          <input id="mot_de_passe" name="mot_de_passe" type="password" required 
                 class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                 placeholder="Choisissez un mot de passe">
        </div>
      </div>

      <div>
        <button type="submit" 
                class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-200">
          Créer mon compte
        </button>
      </div>

      <div class="text-center">
        <p class="text-sm text-gray-600">
          Déjà inscrit ? 
          <a href="/login" class="font-medium text-blue-600 hover:text-blue-500 transition duration-200">
            Se connecter
          </a>
        </p>
        <p class="mt-2">
          <a href="/" class="text-sm text-gray-500 hover:text-gray-700 transition duration-200">
            ⬅ Retour à l'accueil
          </a>
        </p>
      </div>
    </form>
  </div>
</div>
<?php include_once 'includes/footer.php'; ?>
</body>
</html>
