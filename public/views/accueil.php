<?php
$title = 'JVForum';
?>
<div class="sheet">
  <?php include '_header.php' ?>

  <div class="content no-menu">
<?= generate_favorites_markup_index() ?>

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
