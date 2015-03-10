<?php
$topic_mode = $_GET['topic'][0] === '0' ? 1 : 42;
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_URL, "http://www.jeuxvideo.com/forums/{$topic_mode}-{$forum}-{$topic}-1-0-1-0-{$slug}.htm");
$got = curl_exec($ch);

$title = 'Topic';
if (preg_match('#<h1 class="highlight">Topic (.+)</h1>#Usi', $got, $matches)) {
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
?>
<div class="container">

  <div class="sheet">
    <h2 class="sheet-title"><a href="/">Accueil</a></h2>

    <div class="sheet">
      <h2 class="sheet-title"><a href="/<?= $forum ?>-communaute">Communauté</a></h2>

      <div class="sheet sheet-last">
        <h1 class="sheet-title"><a href="/<?= $forum ?>/<?= $topic ?>-<?= $slug ?>"><?= $title ?></a></h1>
        <div class="content">
          <div class="liste-message">
<?php for ($i = 0; $i < count($matches[0]); $i++): ?>
            <div class="message" id="<?= $matches['post'][$i] ?>">
              <div class="meta-author">
                <span class="author pseudo-<?= $matches['status'][$i] ?>"><?= $matches['pseudo'][$i] ?></span>
<? if (strrpos($matches['avatar'][$i], '/default.jpg') === false): ?>
                <span class="avatar"><a href="<?= str_replace(['/avatars-sm/', '/avatar-sm/'], ['/avatars/', '/avatar/'], $matches['avatar'][$i]) ?>"><img src="<?= str_replace(['/avatars-sm/', '/avatar-sm/'], ['/avatars-md/', '/avatar-md/'], $matches['avatar'][$i]) ?>"></a></span>
<?php endif ?>
              </div>
              <div class="meta-actions">
                <span class="meta-permalink"><?= $matches['date'][$i] ?></span>
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
      </div>
    </div>
  </div>
</div>
