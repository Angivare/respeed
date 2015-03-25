<?php
$topic_mode = $_GET['topic'][0] === '0' ? 1 : 42;
$url = "http://www.jeuxvideo.com/forums/{$topic_mode}-{$forum}-{$topic}-{$page}-0-1-0-{$slug}.htm";

$jvc = new Jvc();

$header = '';
$got = '';
if(time() - $jvc->tokens_last_update() >= 3600/2) {
  $got = $jvc->get($url);
  $jvc->refresh_tokens($got['body']);
  $header = $got['header'];
  $got = $got['header'] . $got['body'];
} else {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HEADER, true);
  curl_setopt($ch, CURLOPT_URL, $url);
  $got = curl_exec($ch);
  $header = substr($got, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
}

$location = Jvc::redirects($header);
if($location) {
  preg_match('#/forums/(?P<topic_mode>.+)-(?P<forum>.+)-(?P<topic>.+)-(?P<page>.+)-0-1-0-(?P<slug>.+).htm#U', $location, $matches);
  if($matches['topic_mode'] == '1') $matches['topic'] = '0' . $matches['topic'];
  $location = "/{$matches['forum']}/{$matches['topic']}-{$matches['slug']}";
  if ($matches['page'] > 1) {
    $location .= "/{$matches['page']}";
  }
  header("Location: {$location}");
  exit;
}

// Titre du topic
$title = 'Topic';
if (preg_match('#<span id="bloc-title-forum">(.+)</span>#Usi', $got, $matches)) {
    $title = $matches[1];
}

// Slug et nom du forum
$forum_slug = 'slug';
$forum_name = 'Forum';
if (preg_match('#<span><a href="/forums/0-' . $forum . '-0-1-0-1-0-(.+)\.htm">Forum (.+)</a></span>#Usi', $got, $matches)) {
    $forum_slug = $matches[1];
    $forum_name = $matches[2];
}

//Sondages
$question = '';
if(preg_match('#<div class="intitule-sondage">(.+?)</div>#', $got, $matches))
  $question = $matches[1];
$regex = '#<tr>.+' .
         '<td class="result-pourcent">.+' .
         '<div class="pourcent">(?P<pourcent>[0-9]{1,3})\s*%</div>.+' .
         '</td>.+<td class="reponse">(?P<human>.+)</td>.+' .
         '</tr>#Usi';
$answers = [];
if(preg_match_all($regex, $got, $matches))
  for($i = 0; $i < count($matches[0]); $i++)
    $answers[] = ['pourcent' => $matches['pourcent'][$i], 'human' => $matches['human'][$i] ];

// Messages
$regex = '#<div class="bloc-message-forum " id="post_(?P<post>.+)".+>\s+<div class="conteneur-message">\s+' .
         '(<div class="bloc-avatar-msg">\s+<div class="back-img-msg">\s+<div>\s+<span[^>]+>\s+<img src="(?P<avatar>.+)"[^>]+>\s+</span>\s+</div>\s+</div>\s+</div>\s+)?' .
         '<div class="inner-head-content">.+(<span class="JvCare [0-9A-F]+ bloc-pseudo-msg text-(?P<status>.+)"|<div class="bloc-pseudo-msg").+' .
         '>\s+(?P<pseudo>.+)\s+<.+' .
         '<div class="bloc-date-msg">\s+(<span[^>]+>)?(?P<date>[0-9].+)</div>.+' .
         '<div class="txt-msg  text-enrichi-forum ">(?P<message>.*)</div>' .
         '</div>\s+</div>\s+</div>\s+</div>#Usi';
preg_match_all($regex, $got, $matches);


// Pagination
$last_page = 1;
if (preg_match_all('#<span><a href="/forums/[0-9]+-[0-9]+-[0-9]+-[0-9]+-[0-9]+-[0-9]+-[0-9]+-[0-9a-z-]+\.htm" class="lien-jv">([0-9]+)</a></span>#Usi', $got, $matches2)) {
  $last_page = array_pop($matches2[1]);
}
if ($page > $last_page) { // Si on est sur la dernière page elle ne sera pas capturée par la regex du dessus
  $last_page = $page;
}

$pages = [];
for ($i = $page; $i < 7; $i++) {
  $pages[] = ' ';
}
if ($page != 1) {
  $pages[] = 1;
  for ($i = $page - 5; $i < $page; $i++) {
    if ($i > 1) {
      $pages[] = $i;
    }
  }
}
$pages[] = $page;
if ($page != $last_page) {
  for ($i = $page + 1; $i <= $page + 5; $i++) {
    if ($i < $last_page) {
      $pages[] = $i;
    }
  }
  $pages[] = $last_page;
}
for ($i = $last_page - $page; $i < $last_page - $last_page + 6; $i++) {
  $pages[] = ' ';
}


$pseudo = isset($_COOKIE['pseudo']) ? $_COOKIE['pseudo'] : false;


preg_match('#<span><a href="/forums/0-(?P<id>[0-9]+)-0-1-0-1-0-(?P<slug>[a-z0-9-]+).htm">Forum principal (?P<human>.+)</a></span>#Usi', $got, $has_parent);
$sous_forums = $jvc->sub_forums($got);

preg_match('#var id_topic = (?P<id_topic>[0-9]+);\s+// ]]>\s+</script>#Usi', $got, $matches_id);
if ($matches_id) {
  $topicNew = $matches_id['id_topic'];
}
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

<div class="container">

  <div class="sheet sheet-first">

      <div class="sheet sheet-last">
        <h2 class="forum-title"><a href="/<?= $forum ?>-<?= $forum_slug ?>"><?= $forum_name ?></a></h2>
        <a class="ouvrir-jvc" href="http://www.jeuxvideo.com/forums/<?= $topic_mode ?>-<?= $forum ?>-<?= $topic ?>-<?= $page ?>-0-1-0-<?= $slug ?>.htm" target="_blank">Ouvrir dans JVC</a>
        <h1 class="sheet-title"><a href="/<?= $forum ?>/<?= $topic_mode == 1 ? '0' : '' ?><?= $topic ?>-<?= $slug ?>"><?= $title ?></a></h1>
        <div class="content">
          <div class="pages">
            <div class="pages-container">
<?php foreach ($pages as $i): ?>
<?php if ($i == ' '): ?>
              <span class="faketable">
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
          
          <div class="liste-message">
<?php for ($i = 0; $i < count($matches[0]); $i++): ?>
            <div class="message" id="<?= $matches['post'][$i] ?>" data-pseudo="<?= htmlspecialchars(trim($matches['pseudo'][$i])) ?>">
              <div class="meta-author">
                <span class="author pseudo-<?= $matches['status'][$i] ?>"><?= wbr_pseudo(trim($matches['pseudo'][$i])) ?></span>
<?php if ($matches['avatar'][$i] && strrpos($matches['avatar'][$i], '/default.jpg') === false): ?>
                <span class="avatar"><a href="<?= str_replace(['/avatars-sm/', '/avatar-sm/'], ['/avatars/', '/avatar/'], $matches['avatar'][$i]) ?>"><img src="<?= str_replace(['/avatars-sm/', '/avatar-sm/'], ['/avatars-md/', '/avatar-md/'], $matches['avatar'][$i]) ?>"></a></span>
<?php endif ?>
              </div>
              <div class="meta-actions">
<?php
$date = strip_tags(trim($matches['date'][$i]));
?>
                <span class="meta-permalink" title="<?= $date ?>"><a href="#<?= $matches['post'][$i] ?>"><?= relative_date_messages($date) ?></a></span>
                <span class="meta-quote">Citer</span>
<?php if (strcasecmp($pseudo, trim($matches['pseudo'][$i])) != 0): ?>
                <span class="meta-ignore">Ignorer</span>
                <span class="meta-report">Dénoncer</span>
<?php else: ?>
                <span class="meta-edit">Modifier</span>
                <span class="meta-delete">Supprimer</span>
<?php endif ?>
              </div>
<?php
$message = $matches['message'][$i];
preg_match('#</div><div class="info-edition-msg">\s+Message édité le (?P<date>.+) par\s+<a href="(//www.jeuxvideo.com/profil/(?P<pseudo>.+)\?mode=infos)?" target="_blank">[^<]*</a>#Usi', $message, $matches_edit);
if ($matches_edit) {
  $message = str_replace($matches_edit[0], '', $message);
  $message .= '<p class="edit-mention">Modifié après ' . edit_date_difference($date, $matches_edit['date']) . '</p>';
}
$message = adapt_html($message);

$pos_signature = strpos($message, '</div><div class="signature-msg  text-enrichi-forum ">');
if ($pos_signature !== false) {
  $message = substr($message, 0, $pos_signature);
}
?>
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
              <span class="faketable">
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

<?php if (preg_match('`<span style="color: #FF6600;">(?P<raison>.+)</span></b>`Usi', $got, $matches)): ?>
          <div class="form-post locked">
            <label class="titre-bloc" for="newmessage">Topic verrouillé</label>
            <div class="form-post-inner">
              <p><?= $matches['raison'] ?>
            </div>
          </div>
<?php elseif($jvc->is_connected()): ?>
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
        <div class="clearfix"></div>
      </div>
  </div>
</div>

<script>
var url = '<?= $url ?>'
  , tokens = <?= json_encode($jvc->tokens()) ?>
  , tokens_last_update = <?= $jvc->tokens_last_update() ?>
</script>
