<?php
$title = 'Connexion';

$jvc = new Jvc();

$ccode = isset($_POST['ccode']) ? $_POST['ccode'] : NULL;
$form = isset($_POST['form']) ? $_POST['form'] : NULL;
$nick = isset($_POST['nick']) ? $_POST['nick'] : NULL;
$pass = isset($_POST['pass']) ? $_POST['pass'] : NULL;

if($nick && $pass && $form && $ccode):
  $form = unserialize(urldecode($form));
  if(is_array($form) && ctype_digit($ccode)):
    $url = 'http://www.jeuxvideo.com/forums/42-1000021-38431092-949-0-1-0-actu-un-blabla-est-ne.htm';
    if(!$jvc->connect_finish($nick, $pass, $form, $ccode))
      echo 'Erreur lors de la connexion: ' . $jvc->err();
    else
      header('Location: /');
  endif;
else: ?>
<header class="site-header">
  <h2 class="site-title">
    <a href="/" class="site-title-link"><span class="site-title-spacer">JV</span>Forum</a>
  </h2>
  <div class="site-login-container">
<?php if($jvc->is_connected()): ?>
    <a href="/se_deconnecter" class="site-login-link logout" data-no-instant>Se déconnecter</a>
<?php else: ?>
    <a href="/se_connecter" class="site-login-link">Se connecter</a>
<?php endif ?>
  </div>
</header>

<div class="sheet">
  <h1 class="sheet-title"><a href="/se_connecter"><?= $title ?></a></h1>
  <div class="content no-menu login-fake-table">
    <div class="form-container">
<?php
$pour = 'poster des messages';
if (isset($_GET['pour'])) {
  if ($_GET['pour'] == 'ignorer') {
    $qui = isset($_GET['qui']) && preg_match('#^[a-zA-Z0-9-_[\]]{3,15}$#', $_GET['qui']) ? $_GET['qui'] : 'un pseudo';
    $pour = 'ignorer ' . $qui;
  }
  elseif ($_GET['pour'] == 'citer') {
    $qui = isset($_GET['qui']) && preg_match('#^[a-zA-Z0-9-_[\]]{3,15}$#', $_GET['qui']) ? $_GET['qui'] : 'un message';
    $pour = 'citer ' . $qui;
  }
}
?>
      <div class="sell">Connectez-vous pour <?= $pour ?> via Respeed.</div>
<?php if($nick && $pass):
    $jvc->disconnect();
    $form = $jvc->connect_req($nick, $pass);
?>
      <form action="/se_connecter" method="post">
        <input type="hidden" name="form" value="<?php echo urlencode(serialize($form)) ?>">
        <p><input class="input" type="text" name="nick" placeholder="Pseudo" maxlength="15" value="<?= $nick?>">
        <p><input class="input" type="password" name="pass" placeholder="Mot de passe" value="<?= $pass?>">
        <p><img src="data:image/png;base64,<?php echo base64_encode(
          $jvc->get('http://www.jeuxvideo.com/captcha/ccode.php?' .
          $form['fs_signature']
          )['body']) ?>" class="captcha">
        <br><input class="input input-captcha" type="text" name="ccode" placeholder="Code" autofocus>
        <p><input class="submit submit-center" type="submit" value="Se connecter">
      </form>
<?php else: ?>
      <form action="/se_connecter" method="post">
        <p><input class="input" type="text" name="nick" placeholder="Pseudo" maxlength="15" autofocus>
        <p><input class="input" type="password" name="pass" placeholder="Mot de passe">
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
          <li><strong><a href="https://github.com/dieulot/respeed" target="_blank">Open source</a></strong>, tout développeur web peut participer</li>
        </ul>
      </div>
    </aside>
  </div>
</div>
<?php endif;
