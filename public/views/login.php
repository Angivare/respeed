<?php
$title = 'Connexion';

$jvc = new Jvc();

$ccode = isset($_POST['ccode']) ? $_POST['ccode'] : NULL;
$form = isset($_POST['form']) ? $_POST['form'] : NULL;
$nick = isset($_POST['nick']) ? $_POST['nick'] : NULL;
$pass = isset($_POST['pass']) ? $_POST['pass'] : NULL;
$err_nick = '';
$err_pass = '';
$err = NULL;

if($nick && $pass && $form && $ccode):
  $form = unserialize(urldecode($form));
  if(is_array($form)):
    if(!$jvc->connect_finish($nick, $pass, $form, $ccode, $form)) {
      $err = $jvc->err();
      if(!isset($form['fs_signature'])) {
        $err_nick = $nick;
        $err_pass = $pass;
        $nick = NULL;
        $pass = NULL;
      }
      $form = NULL;
      $ccode = NULL;
    }
    else {
      header('Location: /1000021/39170088-jvforum-beta-privee');
      exit;
    }
  endif;
endif; ?>
<header class="site-header">
  <h2 class="site-title">
    <a href="/" class="site-title-link"><span class="site-title-spacer">JV</span>Forum</a>
  </h2>
  <div class="site-login-container">
<?php if($jvc->is_connected()): ?>
    <a href="/deconnexion" class="site-login-link logout" data-no-instant>Se déconnecter</a>
<?php else: ?>
    <a href="/connexion" class="site-login-link">Se connecter</a>
<?php endif ?>
  </div>
</header>

<div class="sheet">
  <h1 class="sheet-title"><a href="/connexion"><?= $title ?></a></h1>
  <div class="content no-menu login-fake-table">
    <div class="form-container">
<?php if($err): ?>
      <div class="connection-error"><?= $err ?></div>
<?php else: ?>
      <div class="sell">Connectez-vous pour poster des messages via JVForum.</div>
<?php endif ?>
<?php if($nick && $pass):
    $jvc->disconnect();
    if(!$form)
      $form = $jvc->connect_req($nick, $pass);
?>
      <form action="/connexion" method="post">
        <input type="hidden" name="form" value="<?= urlencode(serialize($form)) ?>">
        <p><input class="input" type="text" name="nick" placeholder="Pseudo" maxlength="15" value="<?= $nick ?>" autocorrect="off">
        <p><input class="input" type="password" name="pass" placeholder="Mot de passe" value="<?= $pass?>">
        <p><img src="data:image/png;base64,<?= base64_encode(
          $jvc->get('http://www.jeuxvideo.com/captcha/ccode.php?' .
          $form['fs_signature']
          )['body']) ?>" class="captcha">
        <br><input class="input input-captcha" type="text" name="ccode" placeholder="Code" autofocus autocomplete="off">
        <p><input class="submit submit-center" type="submit" value="Se connecter">
      </form>
<?php else: ?>
      <form action="/connexion" method="post">
        <p><input class="input" type="text" name="nick" placeholder="Pseudo" maxlength="15" value="<?= h($err_nick) ?>" <?= $err_nick ? '' : 'autofocus' ?> autocorrect="off">
        <p><input class="input" type="password" name="pass" placeholder="Mot de passe" value="<?= h($err_pass) ?>">
        <p><input class="submit submit-center" type="submit" value="Se connecter">
      </form>
<?php endif; ?>
    </div>
    <aside class="why-respeed">
      <div>
        <h2>Qu’apporte JVForum ?</h2>
        <ul>
          <li><strong>Instantanéité</strong> d’affichage des pages</li>
          <li><strong>Moins de scroll</strong> sur les grands écrans</li>
          <li><strong>Version mobile complète</strong></li>
          <li><strong>Rafraîchissement automatique</strong> des topics</li>
          <li><strong>Open source</strong>, tout développeur web peut participer</li>
        </ul>
      </div>
    </aside>
  </div>
</div>
