<?php

require 'parser.php';
$jvc = new Jvc();
foreach(fetch_forum($forum, $page, $slug) as $k => $v)
  $$k = $v;
echo "<!-- JVC request delay: {$t_req}ms | MySQL request delay: {$t_db}ms -->";
?>
<header class="site-header">
  <h2 class="site-title">
    <a href="/" class="site-title-link"><span class="site-title-spacer">JV</span>Forum</a>
  </h2>
  <div class="site-login-container">
<?php if($jvc->is_connected()): ?>
    <a href="/se_deconnecter/<?= $token['hash'] ?>-<?= $token['ts'] ?>-<?= $token['rand'] ?>" class="site-login-link logout" data-no-instant>Se déconnecter</a>
<?php else: ?>
    <a href="/se_connecter" class="site-login-link">Se connecter</a>
<?php endif ?>
  </div>
</header>

<div class="sheet">
  <a class="ouvrir-jvc" href="http://www.jeuxvideo.com/forums/0-<?= $forum ?>-0-1-0-1-0-<?= $slug ?>.htm" target="_blank">Ouvrir dans JVC</a>
  <h1 class="sheet-title forum-title"><a href="/<?= $forum ?>-<?= $slug ?>"><?= $title ?> <span class="reload-sign">↻</span></a></h1>
  <div class="content">

<?php
if ($page > 1) {
  include 'forum_pagination.php';
}
?>

    <div class="liste-topics">
<?php for ($i = 0; $i < count($matches['topic']); $i++): ?>
      <div class="topic label-<?= $matches['label'][$i] ?> <?= ($i % 2 == 0) ? 'odd' : 'even' ?>" data-pseudo="<?= $matches['pseudo'][$i] ?>">
        <div class="label"></div>
        <a class="topic-main-link" href="/<?= $forum ?>/<?= $matches['mode'][$i] == 1 ? '0' : '' ?><?= $matches['topic'][$i] ?>-<?= $matches['slug'][$i] ?>">
          <div class="title"><?= $matches['title'][$i] ?></div>
<?php
$pseudo_status = '';
if ($pos = strpos($matches['pseudo_span'][$i], ' text-')) {
  $pseudo_status = trim(substr($matches['pseudo_span'][$i], $pos + 6, 5), '"');
}
?>
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

<?php if($jvc->is_connected()): ?>
    <div class="form-post">
      <label class="titre-bloc" for="newsujet">Créer un nouveau sujet</label>
      <div class="form-error"><p></p></div>
      <div class="form-post-inner">
        <p><input class="input newsujet" type="text" name="newsujet" id="newsujet" maxlength="100" placeholder="Titre">
        <p><textarea class="input textarea" id="newmessage" placeholder="Postez ici votre <?= superlatif() ?> message."></textarea>
        <span id="captcha-container"></span>
        <br><input class="submit submit-main submit-big" id="post" type="submit" value="Poster"></p>
      </div>
    </div>
<?php endif ?>
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
