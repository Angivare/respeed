<header class="header">
  <a class="button button--padded header__home-link" href="/">Accueil</a>
<?php if (isset($forum_slug)): ?>
  <a class="button button--padded header__forum-link" href="/<?= $forum ?>-<?= $forum_slug ?>"><?=$forum_name ?></a>
<?php endif ?>
</header>
