<?php
require 'parser.php';
$jvc = new Jvc();

if (!$jvc->is_connected()) {
  header('Location: /');
  exit;
}

foreach(fetch_forum($forum, $page, $slug) as $k => $v) {
  $$k = $v;
}

echo "<!-- JVC request delay: {$t_req}ms | MySQL request delay: {$t_db}ms -->\n";

$pseudo = isset($_COOKIE['pseudo']) ? $_COOKIE['pseudo'] : false;
?>
<body class="forum-<?= $forum ?>">

<header class="site-header">
  <h2 class="site-title">
    <a href="/accueil" class="site-title-link"><span class="site-title-spacer">JV</span>Forum</a>
  </h2>
  <div class="site-login-container">
    <a href="/deconnexion/<?= $token['hash'] ?>-<?= $token['ts'] ?>-<?= $token['rand'] ?>" class="site-login-link logout" data-no-instant>Se déconnecter</a>
  </div>
</header>

<div class="sheet">
  <a class="ouvrir-jvc" href="http://www.jeuxvideo.com/forums/0-<?= $forum ?>-0-1-0-1-0-<?= $slug ?>.htm" target="_blank">Ouvrir dans JVC</a>
  <h1 class="sheet-title forum-title"><a href="/<?= $forum ?>-<?= $slug ?>"><?= $title ?></a></h1>
  <div class="content">

<?php
if ($page > 1) {
  include 'forum_pagination.php';
}
?>

    <div class="liste-topics">
<?php for ($i = 0; $i < count($matches['topic']); $i++): ?>
<?php
$pseudo_status = '';
if ($pos = strpos($matches['pseudo_span'][$i], ' text-')) {
  $pseudo_status = trim(substr($matches['pseudo_span'][$i], $pos + 6, 5), '"');
}
?>
      <div class="topic label-<?= $matches['label'][$i] ?> <?= ($i % 2 == 0) ? 'odd' : 'even' ?> pseudo-status-<?= $pseudo_status ?>" data-pseudo="<?= $matches['pseudo'][$i] ?>">
        <div class="label"></div>
        <a class="topic-main-link" href="/<?= $forum ?>/<?= $matches['mode'][$i] == 1 ? '0' : '' ?><?= $matches['topic'][$i] ?>-<?= $matches['slug'][$i] ?>">
          <div class="title"><?= $matches['title'][$i] ?></div>
          <div class="author pseudo-<?= $pseudo_status ?>"><?= $matches['pseudo'][$i] ?></div>
        </a>
        <a class="topic-last-page" href="/<?= $forum ?>/<?= $matches['mode'][$i] == 1 ? '0' : '' ?><?= $matches['topic'][$i] ?>-<?= $matches['slug'][$i] ?><?= $matches['nb_reponses'][$i] >= 20 ? ('/' . (1 + floor($matches['nb_reponses'][$i] / 20))) : '' ?>">
          <div class="nb-answers"><?= number_format($matches['nb_reponses'][$i], 0, ',', ' ') ?> <span class="rep">rép</span></div>
          <div class="date" title="<?= trim($matches['date'][$i]) ?>"><?= relative_date_topic_list($matches['date'][$i]) ?></div>
        </a>
      </div>
<?php endfor ?>
    </div>

<?php include 'forum_pagination.php' ?>

    <div class="form-post">
      <label class="titre-bloc" for="newsujet">Créer un nouveau sujet</label>
      <div class="form-error"><p></p></div>
      <div class="form-post-inner">
        <input class="input newsujet" type="text" name="newsujet" id="newsujet" maxlength="100" placeholder="Titre">
        
        <textarea class="input textarea" id="newmessage" placeholder="Postez ici votre <?= superlatif() ?> message."></textarea>
        <span id="captcha-container"></span>
        <div class="form-post-button"><input class="submit submit-main submit-big" id="post" type="submit" value="Poster"></div>
      </div>
    </div>
  </div>
  <aside class="aside">
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

<?php if ($sous_forums): ?>
    <div class="menu">
      <h3 class="title">Sous-forums</h3>
      <ul class="menu-content">
<?php if ($has_parent): ?>
        <li><a href="/<?= $has_parent['id'] ?>-<?= $has_parent['slug'] ?>"><?= $has_parent['human'] ?></a></li>
<?php else: ?>
        <li><a href="/<?= $forum ?>-<?= $slug ?>"><?= $title ?></a></li>
<?php endif ?>
<?php foreach ($sous_forums as $sous_forum): ?>
        <li><a href="/<?= $sous_forum['id'] ?>-<?= $sous_forum['slug'] ?>"><?= $sous_forum['human'] ?></a></li>
<?php endforeach ?>
      </ul>
    </div>
<?php endif ?>

  </aside>
</div>

<script>
var url = 'http://www.jeuxvideo.com/forums/0-<?= $forum ?>-0-1-0-1-0-<?= $slug ?>.htm'
  , tokens = <?= json_encode($jvc->tokens()) ?>
  , tokens_last_update = <?= $jvc->tokens_last_update() ?>
</script>
