<?php
require 'parser.php';

foreach (fetch_forum($forum, $page, $slug) as $k => $v) {
  ${$k} = $v;
}

$favorites = $db->get_favorites($jvc->user_id);
$favorites_forums = isset($favorites['forums']) ? $favorites['forums'] : false;
$favorites_topics = isset($favorites['topics']) ? $favorites['topics'] : false;

$is_mod = $moderators && in_array(strtolower($jvc->pseudo), array_map('strtolower', $moderators));
$is_mod_active = $is_mod && $jvc->logged_into_moderation;
?>
<body class="forum-<?= $forum ?>">

<div class="sheet">
  <?php include '_header.php' ?>

  <div class="content">

    <h1 class="page-title page-title--larger">
      <a class="page-title__link" href="/<?= $forum ?>-<?= $slug ?>"><?= $title ?></a>
      <div class="mobile-menu">
        <div class="mobile-menu__opener"><div class="mobile-menu__opener-icon"></div></div>
          <div class="mobile-menu__items">
<?php if (!is_forum_in_favorites($favorites, $forum)): ?>
            <span class="js-favorite-toggle js-favorite-toggle-label mobile-menu__item" data-action="add">Mettre en favoris</span>
<?php else: ?>
            <span class="js-favorite-toggle js-favorite-toggle-label mobile-menu__item" data-action="delete">Retirer des favoris</span>
<?php endif ?>
            <a class="mobile-menu__item" href="http://www.jeuxvideo.com/forums/0-<?= $forum ?>-0-1-0-1-0-<?= $slug ?>.htm" target="_blank">Ouvrir sur JVC</a>
          </div>
      </div>
    </h1>

<?php if (!is_forum_in_favorites($favorites, $forum)): ?>
    <div class="js-add-to-favorite-mobile-shortcut centered-button-container mobile"><span class="js-favorite-toggle button button--raised button--large" data-action="add">Mettre en favoris</span></div>
<?php endif ?>

<?php if ($is_mod && !$jvc->logged_into_moderation): ?>
  <div class="centered-button-container"><a href="/moderation" class="button button--raised button--large">Modérer</a></div>
<?php endif ?>

<?php if ($db->get_poll_vote(1, $jvc->user_id) == -1): ?>
  <div class="centered-button-container"><a href="/sondage" style="color: #2196f3">&bull; Sondage sur le design de JVForum &bull;</a></div>
<?php endif ?>

<?php
if ($page > 1) {
  include 'forum_pagination.php';
}
?>

    <div class="liste-topics">
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
  $topic_modifier = 'topic--small topic--extra-small';
}
$is_in_blacklist = is_in_blacklist($matches['pseudo'][$i]);
if ($is_in_blacklist) {
  $topic_modifier = 'topic--blacklisted';
}

list($nb_answers, $message_id) = $db->get_topic_position($jvc->user_id, $matches['id'][$i]);
$topic_page = 1 + floor($nb_answers / 20);

$last_page = 1 + floor($matches['nb_reponses'][$i] / 20);

$link = "/{$forum}/";
if ($matches['mode'][$i] == 1) {
  $link .= "0";
}
$link .= $matches['topic'][$i] . '-' . $matches['slug'][$i];
if ($topic_page > 1) {
  $link .= '/' . min($topic_page, $last_page);
}
if ($message_id) {
  $link .= "#{$message_id}";
}

if ($message_id) {
  if ($nb_answers < $matches['nb_reponses'][$i]) {
    $topic_modifier .= ' topic--new-messages';
  }
  else {
    $topic_modifier .= ' topic--visited';
  }
}

$label = $matches['label'][$i];
if (is_topic_in_favorites($favorites, $matches['id'][$i])) {
  $label = 'favorite';
}
?>
      <a class="topic <?= $topic_modifier ?>" href="<?= $link ?>">
        <div class="topic__label-container">
          <div class="topic__label topic__label--<?= $label ?>"></div>
        </div>
<?php if ($is_in_blacklist): ?>
        <div class="topic__blacklist">Topic ignoré de <?= $matches['pseudo'][$i] ?></div>
<?php else: ?>
        <div class="topic__text-container">
          <div class="topic__title"><?= $matches['title'][$i] ?></div>
          <div class="topic__infos">
            <div class="topic__author <?= $pseudo_modifier ?>"><?= $matches['pseudo'][$i] ?></div>
            <div class="topic__date-and-nb-answers-container">
              <span class="topic__nb-answers"><?= number_format($matches['nb_reponses'][$i], 0, ',', ' ') ?></span>
              <span class="topic__date"><?= relative_date_topic_list($matches['date'][$i]) ?></span>
            </div>
          </div>
        </div>
        <div class="topic__title topic__title--desktop"><?= $matches['title'][$i] ?></div>
        <div class="topic__author <?= $pseudo_modifier ?> topic__author--desktop"><?= $matches['pseudo'][$i] ?></div>
        <div class="topic__nb-answers topic__nb-answers--desktop"><?= number_format($matches['nb_reponses'][$i], 0, ',', ' ') ?></div>
        <div class="topic__date topic__date--desktop"><?= relative_date_topic_list($matches['date'][$i]) ?></div>
<?php endif ?>
<!--        <a class="topic__main-link" href="<?= $link ?>">
          <div class="topic__label topic__label--<?= $matches['label'][$i] ?>"></div>
          <div class="topic__main-info">
<?php if (!$is_in_blacklist): ?>
            <div class="topic__title"><?= $matches['title'][$i] ?></div>
            <div class="topic__pseudo <?= $pseudo_modifier ?>"><?= $matches['pseudo'][$i] ?></div>
<?php else: ?>
            <div class="topic__ignored-notice"><span title="<?= $matches['title'][$i] ?>">Topic ignoré de <?= $matches['pseudo'][$i] ?></span></div>
<?php endif ?>
          </div>
        </a>
        <a class="topic__last-page-link" href="/<?= $forum ?>/<?= $matches['mode'][$i] == 1 ? '0' : '' ?><?= $matches['topic'][$i] ?>-<?= $matches['slug'][$i] ?><?= $matches['nb_reponses'][$i] >= 20 ? ('/' . (1 + floor($matches['nb_reponses'][$i] / 20))) : '' ?>">
<?php if (!$is_in_blacklist): ?>
          <div class="topic__last-page-info">
            <div class="topic__answers"><?= number_format($matches['nb_reponses'][$i], 0, ',', ' ') ?></div>
            <div class="topic__date"><?= relative_date_topic_list($matches['date'][$i]) ?></div>
          </div>
<?php endif ?>
        </a>-->
      </a>
<?php endfor ?>
    </div>

<?php include 'forum_pagination.php' ?>

    <form class="js-form-topic form">
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

<?php if ($sous_forums): ?>
  <div class="menu mobile">
<?php if ($has_parent): ?>
    <a class="menu__item" href="/<?= $has_parent['id'] ?>-<?= $has_parent['slug'] ?>"><?= $has_parent['human'] ?></a>
<?php else: ?>
    <a class="menu__item" href="/<?= $forum ?>-<?= $slug ?>"><?= $title ?></a>
<?php endif ?>
<?php foreach ($sous_forums as $sous_forum): ?>
    <a class="menu__item" href="/<?= $sous_forum['id'] ?>-<?= $sous_forum['slug'] ?>"><?= $sous_forum['human'] ?></a>
<?php endforeach ?>
  </div>
<?php endif ?>

  <aside class="aside">
    <div class="aside__top-buttons">
<?php if (!is_forum_in_favorites($favorites, $forum)): ?>
      <span class="js-favorite-toggle aside__top-button aside__top-button--favorite" data-action="add">
        <span class="js-favorite-toggle-label aside__top-button-label">Mettre en favoris</span>
      </span>
<?php else: ?>
  <span class="js-favorite-toggle aside__top-button aside__top-button--unfavorite" data-action="delete">
    <span class="js-favorite-toggle-label aside__top-button-label">Retirer des favoris</span>
  </span>
<?php endif ?>
      <a class="aside__top-button aside__top-button--open-jeuxvideocom" href="http://www.jeuxvideo.com/forums/0-<?= $forum ?>-0-1-0-1-0-<?= $slug ?>.htm" target="_blank">
        <span class="aside__top-button-label">Ouvrir sur JVC</span>
      </a>
    </div>

    <div class="js-favorites">
      <div class="js-favorites-forums aside__menu menu" data-sum="<?= get_favorites_sum($favorites_forums) ?>">
        <?= generate_favorites_forums_markup($favorites) ?>
      </div>
      <div class="js-favorites-topics aside__menu menu" data-sum="<?= get_favorites_sum($favorites_topics) ?>">
        <?= generate_favorites_topics_markup($favorites) ?>
      </div>
    </div>

<?php if ($sous_forums): ?>
    <div class="menu aside__menu">
<?php if ($has_parent): ?>
      <a class="menu__item" href="/<?= $has_parent['id'] ?>-<?= $has_parent['slug'] ?>"><?= $has_parent['human'] ?></a>
<?php else: ?>
      <a class="menu__item" href="/<?= $forum ?>-<?= $slug ?>"><?= $title ?></a>
<?php endif ?>
<?php foreach ($sous_forums as $sous_forum): ?>
      <a class="menu__item" href="/<?= $sous_forum['id'] ?>-<?= $sous_forum['slug'] ?>"><?= $sous_forum['human'] ?></a>
<?php endforeach ?>
    </div>
<?php endif ?>

  </aside>
</div>

<script>
var url = 'http://www.jeuxvideo.com/forums/0-<?= $forum ?>-0-1-0-1-0-<?= $slug ?>.htm'
  , tokens = <?= json_encode($jvc->tokens()) ?>
  , tokens_last_update = <?= $jvc->tokens_last_update() ?>
</script>
