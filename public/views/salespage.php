<?php
$title = 'JVForum';
$jvc = new Jvc();

if ($jvc->is_connected()) {
  header('Location: /accueil');
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
    <div class="presentation">
      <h2>Bienvenue sur JVForum !</h2>
      <p>JVForum vous permet de mieux profiter des forums de jeuxvideo.com quelque soit l’appareil que vous utilisez. <a href="/connexion">Connectez-vous</a> pour retrouver vos forums et topics préférés.</p>
    </div>
  </div>
</div>

<?php if (!$jvc->is_connected()): ?>
<footer class="site-footer">JVForum n’est pas affilié avec <a href="http://www.jeuxvideo.com/">jeuxvideo.com</a>.</footer>
<?php endif ?>
