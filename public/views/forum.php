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
# 
#print_r($matches);
?>
<body class="liste-topics">
<table>
<?php for ($i = 0; $i < count($matches[0]); $i++): ?>
  <tr>
    <td><?= $matches['label'][$i] ?>
    <td><a href="?forum=<?= $forum ?>&amp;topic=<?= $matches['topic'][$i] ?><?= $matches['mode'][$i] == 1 ? '&amp;old' : '' ?>&amp;topic_slug=<?= $matches['topic_slug'][$i] ?>"><?= $matches['title'][$i] ?></a>
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
    <td><?= $matches['pseudo'][$i] ?> <?= $pseudo_status ? "<sup>{$pseudo_status}</sup>" : '' ?>
    <td><?= $matches['nb_reponses'][$i] ?>
    <td><?= $matches['date'][$i] ?>
<?php endfor ?>
</table>
