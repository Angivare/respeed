<?php
$forum = isset($_GET['forum']) ? $_GET['forum'] : false;
$slug = isset($_GET['slug']) && preg_match('#^[a-zA-Z0-9-]{1,200}$#', $_GET['slug']) ? $_GET['slug'] : '0';
$page = isset($_GET['topic_page']) ? $_GET['topic_page'] : 1;
$topic_id_url_jvf = isset($_GET['topic']) ? $_GET['topic'] : '0';

$topic_id_old = $topic_id_new = null;
if ($topic_id_url_jvf[0] === '0') {
  $topic_id_url_jvf = '0' . (int)$topic_id_url_jvf; // Sanitizing
  $topic_id_old = (int)$topic_id_url_jvf;
  $topic_mode = 1;
  $topic_id_old_or_new = $topic_id_old;
}
else {
  $topic_id_url_jvf = (int)$topic_id_url_jvf; // Sanitizing
  $topic_id_new = $topic_id_url_jvf;
  $topic_mode = 42;
  $topic_id_old_or_new = $topic_id_new;
}
$topic_id_array = compact('topic_id_url_jvf', 'topic_mode', 'topic_id_old_or_new', 'topic_id_old', 'topic_id_new');

require 'parser.php';

extract(fetch_topic($topic_id_array, $page, $slug, $forum));

$pseudo = isset($_COOKIE['pseudo']) ? $_COOKIE['pseudo'] : false;

$favorites = $db->get_favorites($jvc->user_id);
$favorites_forums = isset($favorites['forums']) ? $favorites['forums'] : false;
$favorites_topics = isset($favorites['topics']) ? $favorites['topics'] : false;

$is_mod = $moderators && in_array(strtolower($jvc->pseudo), array_map('strtolower', $moderators));
$is_mod_active = $is_mod && $jvc->logged_into_moderation;
?>
<body class="forum-<?= $forum ?> topic-<?= $topic_id_url_jvf ?> body--no-bottom">

<div class="sheet">
  <?php include '_header.php' ?>

  <div class="content">
    <h1 class="page-title page-title--topic">
      <?= $title ?>
      <div class="mobile-menu">
        <div class="mobile-menu__opener"><div class="mobile-menu__opener-icon"></div></div>
          <div class="mobile-menu__items">
<?php if (!is_topic_in_favorites($favorites, $topic_id_new)): ?>
            <span class="js-favorite-toggle js-favorite-toggle-label mobile-menu__item" data-action="add">Mettre en favoris</span>
<?php else: ?>
            <span class="js-favorite-toggle js-favorite-toggle-label mobile-menu__item" data-action="delete">Retirer des favoris</span>
<?php endif ?>
            <a class="mobile-menu__item" href="http://www.jeuxvideo.com/forums/<?= $topic_mode ?>-<?= $forum ?>-<?= $topic_id_old_or_new ?>-<?= $page ?>-0-1-0-<?= $slug ?>.htm" target="_blank">Ouvrir sur JVC</a>
<?php if ($is_mod_active): ?>
<?php if ($locked): ?>
            <a class="mobile-menu__item" href="/lock/<?= $topic_id_new ?>?unlock">Déverrouiller</a>
<?php else: ?>
            <a class="mobile-menu__item" href="/lock/<?= $topic_id_new ?>">Verrouiller</a>
<?php endif ?>
<?php endif ?>
          </div>
      </div>
    </h1>

    <div class="pagination-topic pagination-topic--top">
<?php if (!$locked): ?>
      <div class="pagination-topic__action-button pagination-topic__action-button--post"><span class="js-button-go-to-form button button--raised button--cta button--scale">Répondre</span></div>
<?php endif ?>
      <div class="pagination-topic__pages">
<?= generate_topic_pagination_markup($page, $last_page, $forum, $topic_id_array, $slug) ?>
      </div>
    </div>

<script>var liste_messages = []</script>
    <div class="js-listeMessages liste-messages">
<?php if ($poll): ?>
      <div class="js-poll card card--poll"><?= generate_poll_markup($poll, $topic_mode, $forum, $topic_id_old_or_new, $slug) ?></div>
<?php endif ?>
<?php foreach ($messages as $message): ?>
<?= generate_message_markup($message, $is_mod_active) ?>
<?php endforeach ?>
    </div>

    <div class="pagination-topic pagination-topic--bottom">
      <div class="pagination-topic__action-button"><a class="button button--scale" href="/<?= $forum ?>-<?= $forum_slug ?>">Retour<span class="pagination-topic__action-button-additional-text"> forum</span></a></div>
      <div class="pagination-topic__pages">
<?= generate_topic_pagination_markup($page, $last_page, $forum, $topic_id_array, $slug) ?>
      </div>
    </div>

<?php if ($locked): ?>
    <div class="card">
      <div class="card__header">Sujet verrouillé</div>
      <div class="card__body"><?= $lock_rationale ?></div>
    </div>
<?php else: ?>
    <form class="js-form-post form form--touches-bottom">
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

    <div class="js-button-go-to-form fab-post"><div class="fab-post__inner">Répondre</div></div>
<?php endif ?>

  </div>

  <aside class="aside">
<?php if ($is_mod_active): ?>
    <div class="aside__moderation-actions">
<?php if ($locked): ?>
      <a class="aside__moderation-action" href="/lock/<?= $topic_id_new ?>?unlock">Déverrouiller</a>
<?php else: ?>
      <a class="aside__moderation-action" href="/lock/<?= $topic_id_new ?>">Verrouiller</a>
<?php endif ?>
    </div>
<?php endif ?>

    <div class="aside__top-buttons">
<?php if (!is_topic_in_favorites($favorites, $topic_id_new)): ?>
      <span class="js-favorite-toggle aside__top-button aside__top-button--favorite" data-action="add">
        <span class="js-favorite-toggle-label aside__top-button-label">Mettre en favoris</span>
      </span>
<?php else: ?>
  <span class="js-favorite-toggle aside__top-button aside__top-button--unfavorite" data-action="delete">
    <span class="js-favorite-toggle-label aside__top-button-label">Retirer des favoris</span>
  </span>
<?php endif ?>
      <a class="aside__top-button aside__top-button--open-jeuxvideocom" href="http://www.jeuxvideo.com/forums/<?= $topic_mode ?>-<?= $forum ?>-<?= $topic_id_old_or_new ?>-<?= $page ?>-0-1-0-<?= $slug ?>.htm" target="_blank">
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
var url = 'http://www.jeuxvideo.com/forums/<?= $topic_mode ?>-<?= $forum ?>-<?= $topic_id_old_or_new ?>-<?= $page ?>-0-1-0-<?= $slug ?>.htm'
  , tokens = <?= json_encode($jvc->tokens()) ?>
  , tokens_last_update = <?= $jvc->tokens_last_update() ?>
  , lastPage = <?= $last_page ?>
  , myPseudo = '<?= $pseudo ?>'
  , pollAnswers = <?= $poll ? $poll['answer_count'] : -1 ?>
</script>

<?php
if (!$db->is_topic_page_visited($jvc->user_id, $topic_id_new, $page)) {
  add_javascript_after_files('addTopicVisitedPage()');
}
?>
