<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_URL, "http://www.jeuxvideo.com/forums/0-{$forum}-0-1-0-1-0-{$forum_slug}.htm");
$got = curl_exec($ch);

$title = 'Communauté';

$regex = '#<tr class=".*" data-id=".+">.+' .
         '<img src="/img/forums/topic-(?P<label>.+)\.png".+' .
         '<a href="/forums/(?P<mode>.+)-.+-(?P<topic>.+)-1-0-1-0-(?P<topic_slug>.+)\.htm" title="(?P<title>.+)">.+' .
         '(?P<pseudo_span><span .+>)\s*(?P<pseudo>\S.*)\s*</span>.+' .
         '<td class="nb-reponse-topic">\s+(?P<nb_reponses>.+)\s+</td>.+' .
         '<td class="dernier-msg-topic">.+<span .+>\s+(?P<date>.+)</span>.+' .
         '.+</tr>#Usi';
preg_match_all($regex, $got, $matches);
?>
<header class="header">
  <h1>Respeed</h1>
</header>
  
<div class="container">

  <div class="sheet">
    <h2 class="sheet-title"><a href="/">Accueil</a></h2>

    <div class="sheet sheet-last">
      <h1 class="sheet-title"><a href="#"><?= $title ?></a></h1>
      <div class="content">
<table class="liste-topics">
<?php for ($i = 0; $i < count($matches[0]); $i++): ?>
  <tr>
    <td><div class="label label-<?= $matches['label'][$i] ?>"></div>
    <td><a class="title" href="?forum=<?= $forum ?>&amp;topic=<?= $matches['topic'][$i] ?><?= $matches['mode'][$i] == 1 ? '&amp;old' : '' ?>&amp;topic_slug=<?= $matches['topic_slug'][$i] ?>"><?= $matches['title'][$i] ?></a>
<?php
$pseudo_status = '';
$pos = strpos($matches['pseudo_span'][$i], ' text-');
if ($pos) {
  $pseudo_status = trim(substr($matches['pseudo_span'][$i], $pos + 6, 5), '"');
  if ($pseudo_status == 'user') {
    $pseudo_status = '';
  }
}
?>
    <td class="pseudo pseudo-<?= $pseudo_status ?>"><?= $matches['pseudo'][$i] ?>
    <td><?= $matches['nb_reponses'][$i] ?>
    <td><?= $matches['date'][$i] ?>
<?php endfor ?>
</table>
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
