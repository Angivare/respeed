<?php

$title = 'Éditer un message';

$jvc = new Jvc();

$rep = $jvc->get("http://www.jeuxvideo.com/respeed/forums/message/{$id_message}", FALSE);
$location = Jvc::redirects($rep['header']);
if($location) {
  $rep = $jvc->get("http://www.jeuxvideo.com{$location}", FALSE);

  $re= '#<div class="bloc-message-forum " id="post_(?P<post>.+)".+>\s+<div class="conteneur-message">\s+' .
       '(<div class="bloc-avatar-msg">\s+<div class="back-img-msg">\s+<div>\s+<span[^>]+>\s+<img src="(?P<avatar>.+)"[^>]+>\s+</span>\s+</div>\s+</div>\s+</div>\s+)?' .
       '<div class="inner-head-content">.+(<span class="JvCare [0-9A-F]+ bloc-pseudo-msg text-(?P<status>.+)"|<div class="bloc-pseudo-msg").+' .
       '>\s+(?P<pseudo>.+)\s+<.+' .
       '<div class="bloc-date-msg">\s+(<span[^>]+>)?(?P<date>[0-9].+)</div>.+' .
       '<div class="txt-msg  text-enrichi-forum ">(?P<message>.*)</div>' .
       '</div>\s+</div>\s+</div>\s+</div>#Usi';
  preg_match($re, $rep['body'], $match);
}

$pseudo = isset($_COOKIE['pseudo']) ? $_COOKIE['pseudo'] : false;
if(strcasecmp($pseudo, trim($match['pseudo'])) != 0)
  exit;

?>
<div class="sheet">
  <div class="liste-messages">
<?php
$date = strip_tags(trim($match['date']));
$message = adapt_html($match['message'], $date);
?>
    <div class="message odd" id="<?= $match['post'] ?>" data-pseudo="<?= htmlspecialchars(trim($match['pseudo'])) ?>" data-date="<?= relative_date_messages($date) ?>">
      <div class="action-menu">
        <label class="action meta-quote" for="newmessage">Citer</label><!--
<?php if (strcasecmp($pseudo, trim($match['pseudo'])) != 0): ?>
        --><span class="action meta-ignore">Ignorer</span>
<?php else: ?>
        --><span class="action meta-delete">Supprimer</span>
<?php endif ?>
      </div>
      <div class="not-action-menu">
        <div class="message-header">
          <div class="meta-author">
            <span class="author pseudo-<?= $match['status'] ?> desktop"><a href="http://m.jeuxvideo.com/profil/<?= strtolower(htmlspecialchars(trim($match['pseudo']))) ?>.html" class="m-profil"><?= wbr_pseudo(trim($match['pseudo'])) ?></a></span>
<?php if ($match['avatar'] && strrpos($match['avatar'], '/default.jpg') === false): ?>
            <span class="avatar"><a href="<?= str_replace(['/avatars-sm/', '/avatar-sm/'], ['/avatars/', '/avatar/'], $match['avatar']) ?>"><img src="<?= str_replace(['/avatars-sm/', '/avatar-sm/'], ['/avatars-md/', '/avatar-md/'], $match['avatar']) ?>"></a></span><!--
<?php endif ?>
            <!-- --><span class="author pseudo-<?= $match['status'] ?> mobile"><a href="http://m.jeuxvideo.com/profil/<?= strtolower(htmlspecialchars(trim($match['pseudo']))) ?>.html" class="m-profil"><?= wbr_pseudo(trim($match['pseudo'])) ?></a></span>
          </div>
          <div class="meta-actions">
            <span class="meta-permalink" title="<?= $date ?>"><a href="#<?= $match['post'] ?>"><?= relative_date_messages($date) ?></a></span>
            <span class="meta-menu"></span>
            <span class="meta-quote">Citer</span>
            <span class="meta-delete">Supprimer</span>
          </div>
        </div>
        <div class="mobile message-border"></div>
        <div class="content"><?= $message ?></div>
        <div class="clearfix"></div>
        <div class="ignored-message"><span class="meta-unignore">Ne plus ignorer</span> <?= trim($match['pseudo']) ?> parle mais se fait ignorer.</div>
      </div>
    </div>
  </div>

  <div class="form-post">
    <label class="titre-bloc" for="newsujet">Éditer un message</label>
    <div class="form-error"><p></p></div>
    <div class="form-post-inner">
      <p><textarea class="input textarea" id="newmessage" placeholder="Éditez ici votre <?= superlatif() ?> message."></textarea>
      <span id="captcha-container"></span>
      <br><input class="submit submit-main submit-big" id="edit" type="submit" value="Poster"></p>
    </div>
  </div>
</div>
