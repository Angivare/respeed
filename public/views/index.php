<?php
$title = 'JVForum';
$jvc = new Jvc();
?>
<header class="site-header">
  <h2 class="site-title">
    <a href="/" class="site-title-link"><span class="site-title-spacer">JV</span>Forum</a>
  </h2>
  <div class="site-login-container">
<?php if($jvc->is_connected()): ?>
    <a href="/se_deconnecter/<?= $token['hash'] ?>-<?= $token['ts'] ?>-<?= $token['rand'] ?>" class="site-login-link logout">Se déconnecter</a>
<?php else: ?>
    <a href="/se_connecter" class="site-login-link">Se connecter</a>
<?php endif ?>
  </div>
</header>

<div class="sheet">
  <div class="content">
<?php if (!$jvc->is_connected()): ?>
    <div class="presentation">
      <h2>Bienvenue sur JVForum !</h2>
      <p>JVForum vous permet de mieux profiter des forums de jeuxvideo.com quelque soit l’appareil que vous utilisez. <a href="/se_connecter">Connectez-vous</a> pour retrouver ici vos forums et topics préférés.</p>
    </div>
<?php endif ?>
    <div class="menu" id="forums_pref">
      <h3 class="title">Mes forums préférés</h3>
        <ul class="menu-content">
        </ul>
    </div>

    <div class="menu" id="topics_pref">
      <h3 class="title">Mes topics préférés</h3>
        <ul class="menu-content">
        </ul>
    </div>
    
    <div>
      <h3>Fonctionnalités de JVForum</h3>
      <ul>
        <li>Transformation des liens NoelShack en liens direct</li>
        <li>Cliquez/appuyez sur un avatar pour l’agrandir</li>
      </ul>
    </div>
</div>
