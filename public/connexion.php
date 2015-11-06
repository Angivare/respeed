<?php
require '../config.php';
require 'helpers.php';
require 'Db.php';
require 'Jvc.php';
require 'Auth.php';

$jvc = new Jvc();
if ($jvc->is_connected()) {
  header('Location: /accueil');
  exit;
}

$nick = isset($_POST['nick']) ? trim($_POST['nick']) : null;
$pass = isset($_POST['pass']) ? trim($_POST['pass']) : null;
$ccode = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : null;
if (isset($nick, $pass, $ccode)) {
  $jvc->connect($nick, $pass, $ccode);
  $error = $jvc->err();
}
if ($has_captcha) {
  $captcha_url = 'data:image/png;base64,' . base64_encode($jvc->request('/captcha/ccode.php?' . $form['fs_signature'])['body']);
}
?>
<!doctype html>
<meta charset="utf-8">
<title>Connexion</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link class="js-favicon" rel="icon" href="/images/favicon.png">
<meta name="theme-color" content="#4FC3F7">

<style><?= file_get_contents('style_notconnected.css') ?></style>

<body class="body--connexion">

<div class="connexion">
  <div class="connexion-bloc">
    <h1 class="connexion-bloc__title">Connexion</h1>

<?php if (!isset($error)): ?>
    <p class="login-instructions">Utilisez votre pseudo jeuxvideo.com pour profiter de JVForum.</p>
<?php else: ?>
    <p class="error"><?= $error ?></p>
<?php endif ?>

    <form class="connect-form" action="/connexion" method="post">
      <input class="connect-form__input" type="text" name="nick" placeholder="Pseudo" maxlength="15" value="<?= $nick ?>" autofocus autocorrect="off">
      <input class="connect-form__input" type="password" name="pass" placeholder="Mot de passe" value="<?= $pass ?>">
      <div class="connect-form__captcha"><div class="g-recaptcha" data-sitekey="6Lelbg8TAAAAAMwha8p0BZK5LdpgzISjsD_bSuyx"></div></div>
      <input class="connect-form__submit" type="submit" value="Me connecter">
    </form>

<?php if (!isset($form)): ?>
    <p class="login-disclaimer">Votre identifiant sera transmis au serveur de JVForum, sans y être stocké.</p>
<?php endif ?>
  </div>

<?php if (!isset($form)): ?>
  <div class="connexion-disclaimer">
    <h2 class="connexion-disclaimer__title">Pourquoi dois-je donner mon identifiant ?</h2>
    
    <p class="connexion-disclaimer__copy">À la base, JVForum pouvait être utilisé sans être connecté. Cette fonctionnalité a été <a class="mandatory-login-proof" href="http://www.jeuxvideo.com/nplay/forums/message/714206419" target="_blank">retirée sur demande de JVC</a>.</p>
    
    <div class="legalese">JVForum n’est pas affilié à <a class="legalese__link" href="http://www.jeuxvideo.com/">jeuxvideo.com</a>.</div>
  </div>
<?php endif ?>
</div>

<?php if (GOOGLE_ANALYTICS_ID): ?>
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
ga('create', '<?= GOOGLE_ANALYTICS_ID ?>', 'auto');
ga('set', 'dimension1', 'Guest')
ga('send', 'pageview')
</script>
<?php endif ?>
<script>
localStorage.clear()
</script>
<script src="/scripts/fastclick-<?= REVISION_NUMBER_JS_FASTCLICK ?>.js" onload="FastClick.attach(document.body)" async></script>
<script src="https://www.google.com/recaptcha/api.js"></script>
