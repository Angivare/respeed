<?php
$title = 'Respeed';
$jvc = new Jvc();
?>
<div class="container">

  <div class="sheet sheet-last">
    <div class="sheet-navbar">
      <h1 class="sheet-title"><a href="/">Respeed</a></h1>
<?php if($jvc->is_connected()): ?>
      <a href="/se_deconnecter" class="login-link">Déconnexion</a>
<?php else: ?>
      <a href="/se_connecter" class="login-link">Connexion</a>
<?php endif ?>

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
