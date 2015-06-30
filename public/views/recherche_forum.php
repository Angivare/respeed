<?php
$title = 'Rechercher un forum';
$jvc = new Jvc();

if (!$jvc->is_connected()) {
  header('Location: /');
  exit();
}
?>
<header class="site-header">
  <h2 class="site-title">
    <a href="/accueil" class="site-title-link"><span class="site-title-spacer">JV</span>Forum</a>
  </h2>
  <div class="site-login-container">
    <a href="/deconnexion/<?= $token['hash'] ?>-<?= $token['ts'] ?>-<?= $token['rand'] ?>" class="site-login-link logout" data-no-instant>Se déconnecter</a>
  </div>
</header>

<div class="sheet">
  <h2>Recherche forum</h2>
</div>
