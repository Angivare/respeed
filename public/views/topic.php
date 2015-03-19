<?php
$topic_mode = $_GET['topic'][0] === '0' ? 1 : 42;
$url = "http://www.jeuxvideo.com/forums/{$topic_mode}-{$forum}-{$topic}-{$page}-0-1-0-{$slug}.htm";
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_URL, "http://www.jeuxvideo.com/forums/{$topic_mode}-{$forum}-{$topic}-{$page}-0-1-0-{$slug}.htm");
$got = curl_exec($ch);

$header = substr($got, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
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

$jvc = new Jvc();

if(time() - $jvc->tokens_last_update() >= 3600/2)
  $jvc->refresh_tokens($got);

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

// Messages
$regex = '#<div class="bloc-message-forum " id="post_(?P<post>.+)".+' .
         '<img src="(?P<avatar>.+)".+' .
         '<span class="JvCare [0-9A-F]+ bloc-pseudo-msg text-(?P<status>.+)".+' .
         '>\s+(?P<pseudo>.+)\s+<.+' .
         'lien-jv" target="_blank">(?P<date>.+)</span>.+' .
         '<div class="txt-msg  text-enrichi-forum ">(?P<message>.+)\n                                            </div>.+' .
         '.+</div>#Usi';
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
?>
<div class="container">

  <div class="sheet">
    <div class="sheet-navbar">
      <h2 class="sheet-title"><a href="/">Respeed</a></h2>
<?php if($jvc->is_connected()): ?>
      <a href="/se_deconnecter" class="login-link">Déconnexion</a>
<?php else: ?>
      <a href="/se_connecter" class="login-link">Connexion</a>
<?php endif ?>
    </div>

    <div class="sheet">
      <h2 class="sheet-title"><a href="/<?= $forum ?>-<?= $forum_slug ?>"><?= $forum_name ?></a></h2>

      <div class="sheet sheet-last">
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
<?php if (strrpos($matches['avatar'][$i], '/default.jpg') === false): ?>
                <span class="avatar"><a href="<?= str_replace(['/avatars-sm/', '/avatar-sm/'], ['/avatars/', '/avatar/'], $matches['avatar'][$i]) ?>"><img src="<?= str_replace(['/avatars-sm/', '/avatar-sm/'], ['/avatars-md/', '/avatar-md/'], $matches['avatar'][$i]) ?>"></a></span>
<?php endif ?>
              </div>
              <div class="meta-actions">
                <span class="meta-permalink" title="<?= $matches['date'][$i] ?>"><?= relative_date_messages($matches['date'][$i]) ?></span>
                <span class="meta-quote">Citer</span>
                <span class="meta-ignore">Ignorer</span>
                <span class="meta-report">Dénoncer</span>
              </div>
              <div class="content"><?= adapt_html($matches['message'][$i]) ?></div>
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
          <div class="menu">
            <h3 class="title">Menu</h3>
            <div class="menu-content">
              Un menu.
            </div>
          </div>
        </aside>
        <div class="clearfix"></div>
      </div>
    </div>
  </div>
</div>

<script>
var url = '<?= $url ?>'
  , tokens = <?= json_encode($jvc->tokens()) ?>
  , tokens_last_update = <?= $jvc->tokens_last_update() ?>
</script>
