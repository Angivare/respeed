<?php

require 'parser.php';
$jvc = new Jvc();
foreach(fetch_topic($topic, $page, $slug, $forum) as $k => $v)
  $$k = $v;

$pseudo = isset($_COOKIE['pseudo']) ? $_COOKIE['pseudo'] : false;

?>
<header class="site-header">
  <h2 class="site-title">
    <a href="/" class="site-title-link"><span class="site-title-spacer">JV</span>Forum</a>
  </h2>
  <div class="site-login-container">
<?php if($jvc->is_connected()): ?>
    <a href="/se_deconnecter" class="site-login-link logout">Se déconnecter</a>
<?php else: ?>
    <a href="/se_connecter" class="site-login-link">Se connecter</a>
<?php endif ?>
  </div>
</header>

<div class="sheet">
  <h2 class="forum-title"><a href="/<?= $forum ?>-<?= $forum_slug ?>"><?= $forum_name ?></a></h2>
  <a class="ouvrir-jvc" href="http://www.jeuxvideo.com/forums/<?= $topic_mode ?>-<?= $forum ?>-<?= $topic ?>-<?= $page ?>-0-1-0-<?= $slug ?>.htm" target="_blank">Ouvrir dans JVC</a>
  <h1 class="sheet-title topic-title"><a href="/<?= $forum ?>/<?= $topic_mode == 1 ? '0' : '' ?><?= $topic ?>-<?= $slug ?>"><?= $title ?></a></h1>
  <div class="content">
    <div class="pages">
      <div class="pages-container">
<?php foreach ($pages as $i): ?>
<?php if ($i == ' '): ?>
        <span class="faketable empty">
          <span class="link"></span>
        </span>
<?php continue; endif ?>
<?php
$number = $i;
if ($i == $last_page) {
  $number = '»';
}
if ($i == 1) {
  $number = '«';
}
if ($i == $page - 1) {
  $number = '‹';
}
if ($i == $page + 1) {
  $number = '›';
}
if ($i == $page) {
  $number = $i;
}
$is_sign = (int)$number != $i;
?>
        <span class="faketable">
          <a href="/<?= $forum ?>/<?= $topic_mode == 1 ? '0' : '' ?><?= $topic ?>-<?= $slug ?><?= $i > 1 ? "/{$i}" : '' ?>" class="link <?= $i == $page ? 'active' : '' ?> <?= $is_sign ? 'sign' : '' ?>"><?= $number ?></a>
        </span>
<?php endforeach ?>
      </div>
      <div class="clearfix"></div>
    </div>
    
    <div class="liste-messages">
<?php for ($i = 0; $i < count($matches['post']); $i++): ?>
<?php
$date = strip_tags(trim($matches['date'][$i]));
$message = adapt_html($matches['message'][$i], $date);
?>
      <div class="message <?= ($i % 2 == 0) ? 'odd' : 'even' ?>" id="<?= $matches['post'][$i] ?>" data-pseudo="<?= htmlspecialchars(trim($matches['pseudo'][$i])) ?>" data-date="<?= relative_date_messages($date) ?>">
        <div class="message-header">
          <div class="meta-author">
            <span class="author pseudo-<?= $matches['status'][$i] ?> desktop"><a href="http://m.jeuxvideo.com/profil/<?= strtolower(htmlspecialchars(trim($matches['pseudo'][$i]))) ?>.html" class="m-profil"><?= wbr_pseudo(trim($matches['pseudo'][$i])) ?></a></span>
<?php if ($matches['avatar'][$i] && strrpos($matches['avatar'][$i], '/default.jpg') === false): ?>
            <span class="avatar"><a href="<?= str_replace(['/avatars-sm/', '/avatar-sm/'], ['/avatars/', '/avatar/'], $matches['avatar'][$i]) ?>"><img src="<?= str_replace(['/avatars-sm/', '/avatar-sm/'], ['/avatars-md/', '/avatar-md/'], $matches['avatar'][$i]) ?>"></a></span><!--
<?php endif ?>
            <!-- --><span class="author pseudo-<?= $matches['status'][$i] ?> mobile"><a href="http://m.jeuxvideo.com/profil/<?= strtolower(htmlspecialchars(trim($matches['pseudo'][$i]))) ?>.html" class="m-profil"><?= wbr_pseudo(trim($matches['pseudo'][$i])) ?></a></span>
          </div>
          <div class="meta-actions">
            <span class="meta-permalink" title="<?= $date ?>"><a href="#<?= $matches['post'][$i] ?>"><?= relative_date_messages($date) ?></a></span>
            <span class="meta-quote">Citer</span>
<?php if (strcasecmp($pseudo, trim($matches['pseudo'][$i])) != 0): ?>
            <span class="meta-ignore">Ignorer</span>
<?php else: ?>
            <span class="meta-edit">Modifier</span>
            <span class="meta-delete">Supprimer</span>
<?php endif ?>
          </div>
        </div>
        <div class="mobile message-border"></div>
        <div class="content"><?= $message ?></div>
        <div class="clearfix"></div>
        <div class="ignored-message"><span class="meta-unignore">Ne plus ignorer</span> <?= trim($matches['pseudo'][$i]) ?> parle mais se fait ignorer.</div>
      </div>
<?php endfor ?>
    </div>

    <div class="pages">
      <div class="pages-container">
<?php foreach ($pages as $i): ?>
<?php if ($i == ' '): ?>
        <span class="faketable empty">
          <span class="link"></span>
        </span>
<?php continue; endif ?>
<?php
$number = $i;
if ($i == $last_page) {
  $number = '»';
}
if ($i == 1) {
  $number = '«';
}
if ($i == $page - 1) {
  $number = '‹';
}
if ($i == $page + 1) {
  $number = '›';
}
if ($i == $page) {
  $number = $i;
}
$is_sign = (int)$number != $i;
?>
        <span class="faketable">
          <a href="/<?= $forum ?>/<?= $topic_mode == 1 ? '0' : '' ?><?= $topic ?>-<?= $slug ?><?= $i > 1 ? "/{$i}" : '' ?>" class="link <?= $i == $page ? 'active' : '' ?> <?= $is_sign ? 'sign' : '' ?>"><?= $number ?></a>
        </span>
<?php endforeach ?>
      </div>
      <div class="clearfix"></div>
    </div>

<?php if ($locked): ?>
    <div class="form-post locked">
      <label class="titre-bloc" for="newmessage">Topic verrouillé</label>
      <div class="form-post-inner">
        <p><?= $locked_because ?>
      </div>
    </div>
<?php else: ?>
    <label class="mobile fixed-action" for="newmessage" id="floating_newmessage"></label>
<?php if($jvc->is_connected()): ?>
    <div class="form-post">
      <label class="titre-bloc" for="newmessage">Répondre sur ce sujet</label>
      <div class="form-error"><p></p></div>
      <div class="form-post-inner">
        <p><textarea class="input textarea" id="newmessage" placeholder="Postez ici votre <?= superlatif() ?> message."></textarea>
        <span id="captcha-container"></span>
        <br><input class="submit submit-main submit-big" id="post" type="submit" value="Poster"></p>
      </div>
    </div>
<?php endif; ?>
<?php endif; ?>

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
          <li><a href="/<?= $forum ?>-<?= $slug ?>"><?= $forum_name ?></a></li>
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
var url = 'http://www.jeuxvideo.com/forums/<?= $topic_mode ?>-<?= $forum ?>-<?= $topic ?>-<?= $page ?>-0-1-0-<?= $slug ?>.htm'
  , tokens = <?= json_encode($jvc->tokens()) ?>
  , tokens_last_update = <?= $jvc->tokens_last_update() ?>
</script>
