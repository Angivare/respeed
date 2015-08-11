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
      <div class="pagination-topic__action-button"><span class="js-button-go-to-form button button--raised button--cta button--scale">Poster</span></div>
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
    <div class="form-post locked">
      <label class="titre-bloc" for="newmessage">Sujet verrouillé</label>
      <div class="form-post-inner">
        <p><?= $lock_raison ?>
      </div>
    </div>
<?php else: ?>
    <form class="form-post">
      <div class="form-post__errors"><p></p></div>
      <textarea class="form-post__textarea" placeholder="Mon <?= superlatif() ?> message."></textarea>
      <span class="js-captcha-container-post"></span>
      <div class="form-post__submit-container">
        <input class="button button--raised button--cta button--large button--scale" type="submit" value="Poster">
      </div>
    </form>

<!--    <div class="form-post">
      <label class="titre-bloc" for="newmessage">Répondre sur ce sujet</label>
      <div class="form-error"><p></p></div>
      <div class="form-post-inner">
        <textarea class="input textarea" id="newmessage" placeholder="Mon <?= superlatif() ?> message."></textarea>
        <span id="captcha-container"></span>
        <div class="form-post-button"><input class="submit submit-main submit-big" id="post" type="submit" value="Poster"></div>
      </div>
    </div>-->
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
