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
<body class="forum-<?= $forum ?> topic-<?= ($topic_mode == 1 ? '0' : '') . $topic ?>">

<header class="site-header">
  <h2 class="site-title">
    <a href="/accueil" class="site-title-link"><span class="site-title-spacer">JV</span>Forum</a>
  </h2>
  <div class="site-login-container">
    <a href="/deconnexion/<?= $token['hash'] ?>-<?= $token['ts'] ?>-<?= $token['rand'] ?>" class="site-login-link logout" data-no-instant>Se déconnecter</a>
  </div>
</header>

<div class="sheet">
  <h2 class="forum-title"><a href="/<?= $forum ?>-<?= $forum_slug ?>"><?= $forum_name ?></a></h2>

  <a class="ouvrir-jvc" href="http://www.jeuxvideo.com/forums/<?= $topic_mode ?>-<?= $forum ?>-<?= $topic ?>-<?= $page ?>-0-1-0-<?= $slug ?>.htm" target="_blank">Ouvrir dans JVC</a>
  <h1 class="js-topicTitle sheet-title topic-title"><?= $title ?></h1>
  <div class="content">
    <div class="pages">
      <div class="pages-container">
<?= generate_topic_pagination_markup($page, $last_page, $forum, $topic, $topic_mode, $slug) ?>
      </div>
      <div class="clearfix"></div>
    </div>

<script>var liste_messages = []</script>
    <div class="js-listeMessages liste-messages">
<?php foreach ($messages as $message): ?>
<?= generate_message_markup($message) ?>
<script>liste_messages.push(<?= $message['id'] ?>)</script>
<?php endforeach ?>
    </div>

    <div class="pages">
      <div class="pages-container">
<?= generate_topic_pagination_markup($page, $last_page, $forum, $topic, $topic_mode, $slug, true) ?>
      </div>
      <div class="clearfix"></div>
    </div>

<?php if ($locked): ?>
    <div class="form-post locked">
      <label class="titre-bloc" for="newmessage">Sujet verrouillé</label>
      <div class="form-post-inner">
        <p><?= $lock_raison ?>
      </div>
    </div>
<?php else: ?>
    <div class="form-post">
      <label class="titre-bloc" for="newmessage">Répondre sur ce sujet</label>
      <div class="form-error"><p></p></div>
      <div class="form-post-inner">
        <textarea class="input textarea" id="newmessage" placeholder="Postez ici votre <?= superlatif() ?> message."></textarea>
        <span id="captcha-container"></span>
        <div class="form-post-button"><input class="submit submit-main submit-big" id="post" type="submit" value="Poster"></div>
      </div>
    </div>
<?php endif ?>

    <div class="bottom-back-buttons">
      <span>
        <a class="js-bottom-back-button submit" data-stats-label="forum" href="/<?= $forum ?>-<?= $forum_slug ?>">Retour forum</a>
      </span>
      <span class="align-right">
        <a class="js-bottom-back-button submit" data-stats-label="home" href="/accueil">Accueil</a>
      </span>
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
    <div class="menu desktop">
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
  , last_page = <?= $last_page ?> 
  , myPseudo = '<?= $pseudo ?>' 
</script>
