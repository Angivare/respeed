<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$url = "http://www.jeuxvideo.com/forums/0-{$forum}-0-1-0-1-0-{$slug}.htm";
curl_setopt($ch, CURLOPT_URL, $url);
$got = curl_exec($ch);

$header = substr($got, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
$location = JVc::redirects($header);
if($location) {
  preg_match('#/forums/0-(?P<forum>.+)-0-1-0-1-0-(?P<slug>.+).htm#U', $location, $matches);
  header("Location: /{$matches['forum']}-{$matches['slug']}");
  exit;
}

$jvc = new Jvc();

// Nom du forum
$title = 'Communauté';
if (preg_match('#<h1 class="highlight">Forum (.+)</h1>#Usi', $got, $matches)) {
    $title = $matches[1];
}

// Topics
$regex = '#<tr class=".*" data-id=".+">.+' .
         '<img src="/img/forums/topic-(?P<label>.+)\.png".+' .
         '<a href="/forums/(?P<mode>.+)-.+-(?P<topic>.+)-1-0-1-0-(?P<slug>.+)\.htm" title="(?P<title>.+)">.+' .
         '(?P<pseudo_span><span .+>)\s*(?P<pseudo>\S.*)\s*</span>.+' .
         '<td class="nb-reponse-topic">\s+(?P<nb_reponses>.+)\s+</td>.+' .
         '<td class="dernier-msg-topic">.+<span .+>\s+(?P<date>.+)</span>.+' .
         '.+</tr>#Usi';
preg_match_all($regex, $got, $matches);

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

    <div class="sheet sheet-last">
      <a class="ouvrir-jvc" href="http://www.jeuxvideo.com/forums/0-<?= $forum ?>-0-1-0-1-0-<?= $slug ?>.htm" target="_blank">Ouvrir dans JVC</a>
      <h1 class="sheet-title"><a href="/<?= $forum ?>-<?= $slug ?>"><?= $title ?> <span class="reload-sign">↻</span></a></h1>
      <div class="content">
        <div class="liste-topics">
<?php for ($i = 0; $i < count($matches[0]); $i++): ?>
          <div class="topic label-<?= $matches['label'][$i] ?>" data-pseudo="<?= $matches['pseudo'][$i] ?>">
            <a class="topic-main-link" href="/<?= $forum ?>/<?= $matches['mode'][$i] == 1 ? '0' : '' ?><?= $matches['topic'][$i] ?>-<?= $matches['slug'][$i] ?>">
              <div class="title"><?= $matches['title'][$i] ?></div>
<?php
$pseudo_status = '';
if ($pos = strpos($matches['pseudo_span'][$i], ' text-')) {
  $pseudo_status = trim(substr($matches['pseudo_span'][$i], $pos + 6, 5), '"');
}
?>
              <div class="author pseudo-<?= $pseudo_status ?>"><?= $matches['pseudo'][$i] ?></div>
            </a>
            <a class="topic-last-page" href="/<?= $forum ?>/<?= $matches['mode'][$i] == 1 ? '0' : '' ?><?= $matches['topic'][$i] ?>-<?= $matches['slug'][$i] ?><?= $matches['nb_reponses'][$i] >= 20 ? ('/' . (1 + floor($matches['nb_reponses'][$i] / 20))) : '' ?>">
              <div class="nb-answers"><?= number_format($matches['nb_reponses'][$i], 0, ',', ' ') ?> rép</div>
              <div class="date" title="<?= trim($matches['date'][$i]) ?>"><?= relative_date_topic_list($matches['date'][$i]) ?></div>
            </a>
          </div>
<?php endfor ?>
        </div>
<?php if($jvc->is_connected()): ?>
        <div class="form-post">
          <label class="titre-bloc" for="newsujet">Créer un nouveau sujet</label>
          <div class="form-error"><p></p></div>
          <div class="form-post-inner">
            <p><input class="input newsujet" type="text" name="newsujet" id="newsujet" maxlength="100" placeholder="Votre <?= superlatif() ?> titre.">
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

<script>
var url = '<?= $url ?>'
  , tokens = <?= json_encode($jvc->tokens()) ?>
  , tokens_last_update = <?= $jvc->tokens_last_update() ?>
</script>
