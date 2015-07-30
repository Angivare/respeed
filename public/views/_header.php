<header class="site-header">
  <h2 class="site-title">
    <a href="<?= $jvc->is_connected() ? '/accueil' : '/' ?>" class="site-title-link"><span class="site-title-spacer">JV</span>Forum</a>
  </h2>
<?php if (!$jvc->is_connected()): ?>
  <div class="site-login-container">
    <a href="/connexion" class="site-login-link">Se connecter</a>
  </div>
<?php endif ?>
</header>
