<header class="header <?= $_SERVER['SERVER_NAME'] != 'jvforum.fr' ? 'header--localhost' : '' ?>">
  <div class="header__home-cell">
    <a class="header__home-link <?= isset($forum_slug) ? 'header__home-link--accompanied' : '' ?>" href="/accueil">Accueil</a>
  </div>
<?php if (isset($forum_slug)): ?>
  <div class="header__forum-cell">
    <a class="header__forum-link" href="/<?= $forum ?>-<?= $forum_slug ?>"><?=$forum_name ?></a>
  </div>
<?php endif ?>
</header>
