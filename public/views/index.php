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
        <h3>Forums préférés</h3>
        <ul>
          <li><a href="/1000021-communaute">Communauté</a>
        </ul>
      </div>
      <aside class="aside"></aside>
    </div>
  </div>
</div>
