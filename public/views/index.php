<?php
$title = 'Respeed';
$jvc = new Jvc();
?>
<header class="site-header">
  <h2 class="site-title">
    <a href="/" class="site-title-link"><span class="site-title-spacer">JV</span>Forum</a>
  </h2>
  <div class="site-login-container">
<?php if($jvc->is_connected()): ?>
    <a href="/se_deconnecter" class="site-login-link">Déconnexion</a>
<?php else: ?>
    <a href="/se_connecter" class="site-login-link">Connexion</a>
<?php endif ?>
  </div>
</header>

<div class="container">

  <div class="sheet sheet-first">
    <div class="sheet sheet-last">
      <div class="content">
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
      <aside class="aside"></aside>
    </div>
  </div>
</div>
