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

$pseudo = isset($_COOKIE['pseudo']) ? $_COOKIE['pseudo'] : false;
?>
<body class="forum-<?= $forum ?>">

<?php include '_header.php' ?>

<div class="sheet">
  <div class="content">
    <h1 class="sheet-title forum-title"><a href="/<?= $forum ?>-<?= $slug ?>"><?= $title ?></a></h1>

<?php
if ($page > 1) {
  include 'forum_pagination.php';
}
?>

    <div class="liste-topics">
      <div class="liste-topics__container">
<?php for ($i = 0; $i < count($matches['topic']); $i++): ?>
<?php
$pseudo_modifier = '';
if ($pos = strpos($matches['pseudo_span'][$i], ' text-')) {
  $start = substr($matches['pseudo_span'][$i], $pos + 6);
  $status = substr($start, 0, strpos($start, '"'));
  $pseudo_modifier = 'topic__pseudo--' . $status;
}

$topic_modifier = '';
if (in_array($matches['label'][$i], ['marque-on', 'marque-off', 'ghost', 'lock'])) {
  $topic_modifier = 'topic--small';
}
if (looks_spammy($matches['title'][$i])) {
  $topic_modifier .= 'topic--small topic--extra-small';
}
?>
        <div class="topic <?= $topic_modifier ?>">
          <a class="topic__main-link" href="/<?= $forum ?>/<?= $matches['mode'][$i] == 1 ? '0' : '' ?><?= $matches['topic'][$i] ?>-<?= $matches['slug'][$i] ?>">
            <div class="topic__label topic__label--<?= $matches['label'][$i] ?>"></div>
            <div class="topic__main-info">
              <div class="topic__title"><?= $matches['title'][$i] ?></div>
              <div class="topic__pseudo <?= $pseudo_modifier ?>"><?= $matches['pseudo'][$i] ?></div>
            </div>
          </a>
          <a class="topic__last-page-link" href="/<?= $forum ?>/<?= $matches['mode'][$i] == 1 ? '0' : '' ?><?= $matches['topic'][$i] ?>-<?= $matches['slug'][$i] ?><?= $matches['nb_reponses'][$i] >= 20 ? ('/' . (1 + floor($matches['nb_reponses'][$i] / 20))) : '' ?>">
            <div class="topic__last-page-info">
              <div class="topic__answers"><?= number_format($matches['nb_reponses'][$i], 0, ',', ' ') ?></div>
              <div class="topic__date"><?= relative_date_topic_list($matches['date'][$i]) ?></div>
            </div>
          </a>
        </div>
<?php endfor ?>
      </div>
    </div>

<?php include 'forum_pagination.php' ?>

    <form class="js-form-topic form form--topic">
      <div class="form__draft">Brouillon sauvegardé. <span class="form__draft-recover">Récupérer</span></div>
      <div class="form__errors"><p></p></div>
      <input class="form__topic" maxlength="100" placeholder="Mon sujet" tabindex="1">
      <textarea class="form__textarea" placeholder="Mon <?= superlatif() ?> message." tabindex="2"></textarea>
      <span class="form__captcha-container"></span>
      <div class="form__actions">
        <div class="form__action form__action--left"><a class="smileys-link" href="/smileys"></a></div>
        <div class="form__action form__action--submit"><input class="button button--raised button--cta button--large button--scale" type="submit" value="Poster" tabindex="4"></div>
        <div class="form__action"></div>
      </div>
    </form>
  </div>

  <aside class="aside">
    <div class="ouvrir-jvc">
      <a href="http://www.jeuxvideo.com/forums/0-<?= $forum ?>-0-1-0-1-0-<?= $slug ?>.htm" target="_blank">Ouvrir dans JVC</a>
    </div>

    <div class="menu" id="forums_pref">
      <h3 class="title"><span class="mine">Mes</span> forums préférés</h3>
      <ul class="menu-content">
      </ul>
    </div>

    <div class="menu" id="topics_pref">
      <h3 class="title"><span class="mine">Mes</span> topics préférés</h3>
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
