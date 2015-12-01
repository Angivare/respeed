<?php
$title = 'JVForum';

$favorites = $db->get_favorites($jvc->user_id);
?>
<div class="sheet">
  <?php include '_header.php' ?>

  <div class="content no-menu">
    <div class="js-favorites-index favorites-index">
      <div class="favorites-forums">
        <?= generate_favorites_forums_markup_index($favorites) ?>
      </div>
      <div class="favorites-topics">
        <?= generate_favorites_topics_markup_index($favorites) ?>
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
