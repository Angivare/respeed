<?php
$title = 'JVForum';
$jvc = new Jvc();

if (!$jvc->is_connected()) {
  header('Location: /');
  exit;
}
?>
<header class="site-header">
  <h2 class="site-title">
    <a href="/" class="site-title-link"><span class="site-title-spacer">JV</span>Forum</a>
  </h2>
  <div class="site-login-container">
<?php if($jvc->is_connected()): ?>
    <a href="/deconnexion/<?= $token['hash'] ?>-<?= $token['ts'] ?>-<?= $token['rand'] ?>" class="site-login-link logout" data-no-instant>Se déconnecter</a>
<?php else: ?>
    <a href="/connexion" class="site-login-link">Se connecter</a>
<?php endif ?>
  </div>
</header>

<div class="sheet">
  <div class="content no-menu">
    <div class="favorites-index">
      <div class="favorites-forums">
        <h3>Forums préférés</h3>
      </div>
      
      <div class="favorites-topics">
        <h3>Topics préférés</h3>
      </div>
    </div>
  </div>
</div>
