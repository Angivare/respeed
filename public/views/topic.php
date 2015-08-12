<?php
require 'parser.php';
$jvc = new Jvc();

if (!$jvc->is_connected()) {
  header('Location: /');
  exit;
}

foreach(fetch_topic($topic, $page, $slug, $forum) as $k => $v) {
  $$k = $v;
}

echo "<!-- JVC request delay: {$t_req}ms | MySQL request delay: {$t_db}ms -->\n";

$pseudo = isset($_COOKIE['pseudo']) ? $_COOKIE['pseudo'] : false;
?>
<body class="forum-<?= $forum ?> topic-<?= ($topic_mode == 1 ? '0' : '') . $topic ?> body--no-bottom">

<?php include '_header.php' ?>

<div class="sheet">
  <div class="content">
    <h2 class="forum-title"><a href="/<?= $forum ?>-<?= $forum_slug ?>"><?= $forum_name ?></a></h2>

    <h1 class="js-topicTitle sheet-title topic-title"><?= $title ?></h1>

    <div class="pagination-topic">
<?php if (!$locked): ?>
      <div class="pagination-topic__action-button"><span class="js-button-go-to-form button button--raised button--cta button--scale">Poster</span></div>
<?php endif ?>
      <div class="pagination-topic__pages">
<?= generate_topic_pagination_markup($page, $last_page, $forum, $topic, $topic_mode, $slug) ?>
      </div>
    </div>

<script>var liste_messages = []</script>
    <div class="js-listeMessages liste-messages">
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
      <div class="form__errors"><p></p></div>
      <textarea class="form__textarea" placeholder="Mon <?= superlatif() ?> message." tabindex="1"></textarea>
      <span class="form__captcha-container"></span>
      <div class="form__submit-container">
        <input class="button button--raised button--cta button--large button--scale" type="submit" value="Poster" tabindex="4">
      </div>
    </form>
<?php endif ?>

  </div>

  <aside class="aside desktop">
    <div class="ouvrir-jvc">
      <a href="http://www.jeuxvideo.com/forums/<?= $topic_mode ?>-<?= $forum ?>-<?= $topic ?>-<?= $page ?>-0-1-0-<?= $slug ?>.htm" target="_blank">Ouvrir dans JVC</a>
    </div>

    <div class="menu" id="forums_pref">
      <h3 class="title"><span class="mine">Mes</span> forums préférés</h3>
        <ul class="menu-content">
        </ul>
    </div>

    <div class="js-slider slider menu" id="topics_pref">
      <h3 class="title"><span class="mine">Mes</span> topics préférés</h3>
        <ul class="menu-content">
        </ul>
    </div>
  </aside>
</div>

<script>
var url = 'http://www.jeuxvideo.com/forums/<?= $topic_mode ?>-<?= $forum ?>-<?= $topic ?>-<?= $page ?>-0-1-0-<?= $slug ?>.htm' 
  , tokens = <?= json_encode($jvc->tokens()) ?> 
  , tokens_last_update = <?= $jvc->tokens_last_update() ?> 
  , lastPage = <?= $last_page ?> 
  , myPseudo = '<?= $pseudo ?>' 
</script>
