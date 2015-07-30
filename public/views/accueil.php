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
    <a href="/accueil" class="site-title-link"><span class="site-title-spacer">JV</span>Forum</a>
  </h2>
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

    <form class="rechercheforum-form-accueil" action="/recherche_forum" method="get">
      <input class="rechercheforum-q input" type="text" autocorrect="off" placeholder="Rechercher un forum" name="q">
      <input type="submit" class="validate" value="Go">
    </form>

    <div class="homepage-links">
      <a href="/apropos">À propos</a>
    </div>

    <div class="logout-container">
      <a class="logout2" href="/deconnexion/<?= $token['hash'] ?>-<?= $token['ts'] ?>-<?= $token['rand'] ?>" data-no-instant>Déconnexion</a>
    </div>
  </div>
</div>
