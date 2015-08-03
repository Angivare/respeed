<?php
if (!isset($jvc)) {
  $jvc = new Jvc();
}
?>
<header class="header">
  <h2 class="header-title">
    <a class="header-title__link" href="<?= $jvc->is_connected() ? '/accueil' : '/' ?>" <?= $jvc->is_connected() ? '' : 'data-no-instant' ?>><span class="spacer">JV</span>Forum</a>
  </h2>
<?php if (!$jvc->is_connected()): ?>
  <div class="header-login">
    <a href="/connexion" class="header-login__link">Se connecter</a>
  </div>
<?php endif ?>
</header>
