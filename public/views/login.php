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
<div class="container">

  <div class="sheet">
    <div class="sheet-navbar">
      <h2 class="sheet-title"><a href="/">Respeed</a></h2>
      <a href="/se_connecter" class="login-link">Connexion</a>
    </div>

    <div class="sheet sheet-last">
      <h1 class="sheet-title"><a href="/se_connecter"><?= $title ?></a></h1>
      <div class="content">
        <div class="form-container">
          <div class="sell">Connectez-vous pour poster des messages via Respeed.</div>
<?php if($nick && $pass):
    $jvc->disconnect();
    $form = $jvc->connect_req($nick, $pass);
?>
          <form action="/se_connecter" method="post">
            <input type="hidden" name="form" value="<?php echo urlencode(serialize($form)) ?>">
            <p><input class="input" type="text" name="nick" placeholder="Pseudo" maxlength="15" value="<?= $nick?>">
            <p><input class="input" type="password" name="pass" placeholder="Mot de passe" value="<?= $pass?>">
            <p><img src="data:image/png;base64,<?php echo base64_encode(
              file_get_contents('http://www.jeuxvideo.com/captcha/ccode.php?' .
              $form['fs_signature']
              )) ?>" class="captcha">
            <br><input class="input input-captcha" type="text" name="ccode" placeholder="Code" autofocus>
            <p><input class="submit" type="submit" value="Se connecter">
          </form>
<?php else: ?>
          <form action="/se_connecter" method="post">
            <p><input class="input" type="text" name="nick" placeholder="Pseudo" maxlength="15" autofocus>
            <p><input class="input" type="password" name="pass" placeholder="Mot de passe">
            <p><input class="submit" type="submit" value="Se connecter">
          </form>
<?php endif; ?>
        </div>
        <aside class="aside-form">
          <h2>Qu’apporte Respeed ?</h2>
          <ul>
            <li><strong>Instantanéité</strong> d’affichage des pages</li>
            <li><strong>Moins de scroll</strong> sur les grands écrans</li>
            <li><strong>Version mobile complète</strong></li>
            <li><strong>Rafraîchissement automatique</strong> des topics</li>
            <li><strong><a href="https://github.com/dieulot/respeed" target="_blank">Open source</a></strong>, tout développeur web peut participer</li>
          </ul>
        </aside>
      </div>
      <div class="clearfix"></div>
    </div>
  </div>
</div>
<?php endif;