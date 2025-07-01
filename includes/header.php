<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!-- Added stylesheet reference to ensure consistent access across all pages -->
<link rel="stylesheet" href="/style.css">
<header style="padding: 1rem; background: #3498db; color: white;">
  <nav style="display: flex; justify-content: space-between; align-items: center; max-width: 1000px; margin: auto;">
    <div>
      <a href="/" style="color:white; font-weight:bold; text-decoration:none;">🏠 PrepLi</a>
    </div>
    <div style="display: flex; gap: 1rem;">
      <?php if (isset($_SESSION['utilisateur_id'])): ?>
        <a href="/fiches" style="color:white; text-decoration:none;">📄 Fiches</a>
        <a href="/ajouter" style="color:white; text-decoration:none;">➕ Ajouter une fiche</a>
        <a href="/sequences" style="color:white; text-decoration:none;">🧩 Séquences</a>
        <a href="/logout" style="color:white; text-decoration:none;">🚪 Déconnexion</a>
      <?php else: ?>
        <a href="/login" style="color:white; text-decoration:none;">🔐 Connexion</a>
        <a href="/inscription" style="color:white; text-decoration:none;">🆕 Inscription</a>
      <?php endif; ?>
    </div>
  </nav>
</header>
