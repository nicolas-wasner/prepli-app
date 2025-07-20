<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<header class="fixed top-0 left-0 w-full z-[9999] bg-blue-500 text-white shadow-lg">
  <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">
      <div class="flex items-center">
        <a href="/" class="text-2xl font-extrabold tracking-tight text-white hover:text-blue-200 transition duration-200 flex items-center gap-2">
          <span class="text-3xl">🏠</span> <span>PrepLi</span>
        </a>
      </div>
      <div class="hidden md:flex ml-10 items-baseline space-x-4">
        <?php if (isset($_SESSION['utilisateur_id'])): ?>
          <a href="/fiches" class="text-white hover:bg-blue-800 hover:text-white px-3 py-2 rounded-md text-base font-medium transition">📄 Fiches</a>
          <a href="/ajouter" class="text-white hover:bg-blue-800 hover:text-white px-3 py-2 rounded-md text-base font-medium transition">➕ Ajouter une fiche</a>
          <a href="/sequences" class="text-white hover:bg-blue-800 hover:text-white px-3 py-2 rounded-md text-base font-medium transition">🧩 Séquences</a>
          <a href="/profile" class="text-white hover:bg-blue-800 hover:text-white px-3 py-2 rounded-md text-base font-medium transition">👤 Profil</a>
        <?php else: ?>
          <a href="/login" class="text-white hover:bg-blue-800 hover:text-white px-3 py-2 rounded-md text-base font-medium transition">🔐 Connexion</a>
          <a href="/inscription" class="text-white hover:bg-blue-800 hover:text-white px-3 py-2 rounded-md text-base font-medium transition">🆕 Inscription</a>
        <?php endif; ?>
      </div>
      <!-- Mobile menu button -->
      <div class="md:hidden">
        <button type="button" class="bg-white rounded p-1 text-blue-700 hover:bg-blue-100 focus:outline-none" onclick="toggleMobileMenu()">
          <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="#2563eb" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>
    </div>
    <!-- Mobile menu -->
    <div class="md:hidden hidden" id="mobile-menu">
      <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
        <?php if (isset($_SESSION['utilisateur_id'])): ?>
          <a href="/fiches" class="text-white hover:bg-blue-800 hover:text-white block px-3 py-2 rounded-md text-base font-medium transition">📄 Fiches</a>
          <a href="/ajouter" class="text-white hover:bg-blue-800 hover:text-white block px-3 py-2 rounded-md text-base font-medium transition">➕ Ajouter une fiche</a>
          <a href="/sequences" class="text-white hover:bg-blue-800 hover:text-white block px-3 py-2 rounded-md text-base font-medium transition">🧩 Séquences</a>
          <a href="/profile" class="text-white hover:bg-blue-800 hover:text-white block px-3 py-2 rounded-md text-base font-medium transition">👤 Profil</a>
        <?php else: ?>
          <a href="/login" class="text-white hover:bg-blue-800 hover:text-white block px-3 py-2 rounded-md text-base font-medium transition">🔐 Connexion</a>
          <a href="/inscription" class="text-white hover:bg-blue-800 hover:text-white block px-3 py-2 rounded-md text-base font-medium transition">🆕 Inscription</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>
</header>

<script>
function toggleMobileMenu() {
  const menu = document.getElementById('mobile-menu');
  menu.classList.toggle('hidden');
}
</script>
