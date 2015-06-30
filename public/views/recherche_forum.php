<?php
$title = 'Rechercher un forum';
$jvc = new Jvc();

if (!$jvc->is_connected()) {
  header('Location: /');
  exit();
}

$q = isset($_GET['q']) ? $_GET['q'] : '';
?>
<header class="site-header">
  <h2 class="site-title">
    <a href="/accueil" class="site-title-link"><span class="site-title-spacer">JV</span>Forum</a>
  </h2>
  <div class="site-login-container">
    <a href="/deconnexion/<?= $token['hash'] ?>-<?= $token['ts'] ?>-<?= $token['rand'] ?>" class="site-login-link logout" data-no-instant>Se déconnecter</a>
  </div>
</header>

<div class="sheet rechercheforum">
  <form action="/recherche_forum" method="get">
    <input class="q input" type="text" autocorrect="off" placeholder="Rechercher un forum" name="q" value="<?= h($q) ?>" autofocus>
    <input type="submit" class="validate" value="Go">
  </form>
  
  <p><?= h($q) ?></p>
  
  <p>Plus de 100 résultats, veullez affiner votre recherche.
</div>
