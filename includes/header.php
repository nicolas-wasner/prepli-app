<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<header style="padding: 1rem; background: #3498db; color: white;">
  <nav style="display: flex; justify-content: space-between; align-items: center; max-width: 1000px; margin: auto;">
    <div>
      <a href="index.php" style="color:white; font-weight:bold; text-decoration:none;">🏠 PrepLi</a>
    </div>
    <div style="display: flex; gap: 1rem;">
      <?php if (isset($_SESSION['utilisateur_id'])): ?>
        <a href="fiches.php" style="color:white; text-decoration:none;">📄 Fiches</a>
        <a href="ajouter.php" style="color:white; text-decoration:none;">➕ Ajouter une fiche</a>
        <a href="sequences.php" style="color:white; text-decoration:none;">🧩 Séquences</a>
        <a href="logout.php" style="color:white; text-decoration:none;">🚪 Déconnexion</a>
      <?php else: ?>
        <a href="login.php" style="color:white; text-decoration:none;">🔐 Connexion</a>
        <a href="inscription.php" style="color:white; text-decoration:none;">🆕 Inscription</a>
      <?php endif; ?>
    </div>
  </nav>
</header>
