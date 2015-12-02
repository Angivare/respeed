<?php
require 'parser.php';

foreach (fetch_topic($topic, $page, $slug, $forum) as $k => $v) {
  ${$k} = $v;
}

$pseudo = isset($_COOKIE['pseudo']) ? $_COOKIE['pseudo'] : false;

$favorites = $db->get_favorites($jvc->user_id);
$favorites_forums = isset($favorites['forums']) ? $favorites['forums'] : false;
$favorites_topics = isset($favorites['topics']) ? $favorites['topics'] : false;
?>
<body class="forum-<?= $forum ?> topic-<?= ($topic_mode == 1 ? '0' : '') . $topic ?> body--no-bottom">

<div class="sheet">
  <?php include '_header.php' ?>

  <div class="content">
    <h1 class="page-title page-title--topic">
      <?= $title ?>
      <div class="mobile-menu">
        <div class="mobile-menu__opener"><div class="mobile-menu__opener-icon"></div></div>
          <div class="mobile-menu__items">
<?php if (!is_topic_in_favorites($favorites, $forum)): ?>
            <span class="js-favorite-toggle js-favorite-toggle-label mobile-menu__item" data-action="add">Mettre en favoris</span>
<?php else: ?>
            <span class="js-favorite-toggle js-favorite-toggle-label mobile-menu__item" data-action="delete">Retirer des favoris</span>
<?php endif ?>
            <a class="mobile-menu__item" href="http://www.jeuxvideo.com/forums/<?= $topic_mode ?>-<?= $forum ?>-<?= $topic ?>-<?= $page ?>-0-1-0-<?= $slug ?>.htm" target="_blank">Ouvrir sur JVC</a>
          </div>
      </div>
    </h1>

    <div class="pagination-topic pagination-topic--top">
<?php if (!$locked): ?>
      <div class="pagination-topic__action-button pagination-topic__action-button--post"><span class="js-button-go-to-form button button--raised button--cta button--scale">Poster</span></div>
<?php endif ?>
      <div class="pagination-topic__pages">
<?= generate_topic_pagination_markup($page, $last_page, $forum, $topic, $topic_mode, $slug) ?>
      </div>
    </div>

<script>var liste_messages = []</script>
    <div class="js-listeMessages liste-messages">
<?php if ($poll): ?>
      <div class="js-poll card card--poll"><?= generate_poll_markup($poll, $topic_mode, $forum, $topic, $slug) ?></div>
<?php endif ?>
<?php foreach ($messages as $message): ?>
<?= generate_message_markup($message) ?>
<?php endforeach ?>
    </div>

    <div class="pagination-topic pagination-topic--bottom">
      <div class="pagination-topic__action-button"><a class="button button--scale" href="/<?= $forum ?>-<?= $forum_slug ?>">Retour<span class="pagination-topic__action-button-additional-text"> forum</span></a></div>
      <div class="pagination-topic__pages">
<?= generate_topic_pagination_markup($page, $last_page, $forum, $topic, $topic_mode, $slug) ?>
      </div>
    </div>

<?php if ($locked): ?>
    <div class="lock-alert">
      <div class="lock-alert__title">Sujet verrouillé</div>
      <div class="lock-alert__cause"><?= $lock_raison ?></div>
    </div>
<?php else: ?>
    <form class="js-form-post form">
      <div class="form__draft">Brouillon sauvegardé. <span class="form__draft-recover">Récupérer</span></div>
      <div class="form__errors"><p></p></div>
      <textarea class="form__textarea" placeholder="Mon <?= superlatif() ?> message." tabindex="1"></textarea>
      <span class="form__captcha-container"></span>
      <div class="form__actions">
        <div class="form__action form__action--left"><a class="smileys-link" href="/smileys"></a></div>
        <div class="form__action form__action--submit"><input class="button button--raised button--cta button--large button--scale" type="submit" value="Poster" tabindex="4"></div>
        <div class="form__action"></div>
      </div>
    </form>

    <div class="js-button-go-to-form fab-post"><div class="fab-post__inner">Poster</div></div>
<?php endif ?>

  </div>

  <aside class="aside">
    <div class="aside__top-buttons">
<?php if (!is_topic_in_favorites($favorites, $topicNew)): ?>
      <span class="js-favorite-toggle aside__top-button aside__top-button--favorite" data-action="add">
        <span class="js-favorite-toggle-label aside__top-button-label">Mettre en favoris</span>
      </span>
<?php else: ?>
  <span class="js-favorite-toggle aside__top-button aside__top-button--unfavorite" data-action="delete">
    <span class="js-favorite-toggle-label aside__top-button-label">Retirer des favoris</span>
  </span>
<?php endif ?>
      <a class="aside__top-button aside__top-button--open-jeuxvideocom" href="http://www.jeuxvideo.com/forums/<?= $topic_mode ?>-<?= $forum ?>-<?= $topic ?>-<?= $page ?>-0-1-0-<?= $slug ?>.htm" target="_blank">
        <span class="aside__top-button-label">Ouvrir sur JVC</span>
      </a>
    </div>

    <div class="js-favorites">
      <div class="js-favorites-forums aside__menu menu" data-sum="<?= get_favorites_sum($favorites_forums) ?>">
        <?= generate_favorites_forums_markup($favorites) ?>
      </div>
      <div class="js-favorites-topics js-slider aside__menu menu slider" data-sum="<?= get_favorites_sum($favorites_topics) ?>">
        <?= generate_favorites_topics_markup($favorites) ?>
      </div>
    </div>
  </aside>
</div>

<script>
var url = 'http://www.jeuxvideo.com/forums/<?= $topic_mode ?>-<?= $forum ?>-<?= $topic ?>-<?= $page ?>-0-1-0-<?= $slug ?>.htm'
  , tokens = <?= json_encode($jvc->tokens()) ?>
  , tokens_last_update = <?= $jvc->tokens_last_update() ?>
  , lastPage = <?= $last_page ?>
  , myPseudo = '<?= $pseudo ?>'
  , pollAnswers = <?= $poll ? $poll['ans_count'] : -1 ?>
</script>
