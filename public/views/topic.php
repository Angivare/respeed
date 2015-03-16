<?php
$topic_mode = $_GET['topic'][0] === '0' ? 1 : 42;
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_URL, "http://www.jeuxvideo.com/forums/{$topic_mode}-{$forum}-{$topic}-{$page}-0-1-0-{$slug}.htm");
$got = curl_exec($ch);

$jvc = new Jvc();

$title = 'Topic';
if (preg_match('#<span id="bloc-title-forum">(.+)</span>#Usi', $got, $matches)) {
    $title = $matches[1];
}

$regex = '#<div class="bloc-message-forum " id="post_(?P<post>.+)".+' .
         '<img src="(?P<avatar>.+)".+' .
         '<span class="JvCare [0-9A-F]+ bloc-pseudo-msg text-(?P<status>.+)".+' .
         '>\s+(?P<pseudo>.+)\s+<.+' .
         'lien-jv" target="_blank">(?P<date>.+)</span>.+' .
         '<div class="txt-msg  text-enrichi-forum ">(?P<message>.+)\n                                            </div>.+' .
         '.+</div>#Usi';
preg_match_all($regex, $got, $matches);

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
      <a href="/se_connecter" class="login-link">Connexion</a>
    </div>

    <div class="sheet">
      <h2 class="sheet-title"><a href="/<?= $forum ?>-communaute">Communauté</a></h2>

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
            <div class="message" id="<?= $matches['post'][$i] ?>">
              <div class="meta-author">
                <span class="author pseudo-<?= $matches['status'][$i] ?>"><?= $matches['pseudo'][$i] ?></span>
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
            </div>
<?php endfor ?>
          </div>
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
<?php if($jvc->is_connected()): ?>
        <div class="post-form">
          <form>
            <textarea id="message"></textarea>
            <input type="submit" id="send-message">
          </form>
        </div>
<?php endif; ?>
      </div>
    </div>
  </div>
</div>
