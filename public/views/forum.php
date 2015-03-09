<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_URL, "http://www.jeuxvideo.com/forums/0-{$forum}-0-1-0-1-0-{$slug}.htm");
$got = curl_exec($ch);

$title = 'Communauté';

$regex = '#<tr class=".*" data-id=".+">.+' .
         '<img src="/img/forums/topic-(?P<label>.+)\.png".+' .
         '<a href="/forums/(?P<mode>.+)-.+-(?P<topic>.+)-1-0-1-0-(?P<slug>.+)\.htm" title="(?P<title>.+)">.+' .
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
      <h1 class="sheet-title"><a href="/<?= $forum ?>-<?= $slug ?>"><?= $title ?></a></h1>
      <div class="content">
        <div class="liste-topics">
<?php for ($i = 0; $i < count($matches[0]); $i++): ?>
          <a class="label-<?= $matches['label'][$i] ?>" href="/<?= $forum ?>/<?= $matches['mode'][$i] == 1 ? '0' : '' ?><?= $matches['topic'][$i] ?>-<?= $matches['slug'][$i] ?>">
            <div class="title"><?= $matches['title'][$i] ?></div>
<?php
$pseudo_status = '';
if ($pos = strpos($matches['pseudo_span'][$i], ' text-')) {
  $pseudo_status = trim(substr($matches['pseudo_span'][$i], $pos + 6, 5), '"');
}
?>
            <div class="author pseudo-<?= $pseudo_status ?>"><?= $matches['pseudo'][$i] ?></div>
            <div class="nb-answers"><?= number_format($matches['nb_reponses'][$i], 0, ',', ' ') ?> rép</div>
            <div class="date"><?= $matches['date'][$i] ?></div>
            <div class="border"></div>
          </a>
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
