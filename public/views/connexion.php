<?php
$jvc = new Jvc();
if ($jvc->is_connected()) {
  header('Location: /accueil');
  exit;
}
?>
<!doctype html>
<meta charset="utf-8">
<title>Connexion</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link class="js-favicon" rel="icon" href="/images/favicon.png">
<meta name="theme-color" content="hsl(240, 11%, 40%)">

<style><?= file_get_contents('style_notconnected.css') ?></style>

<body class="body--connexion">

<div class="connexion">
  <div class="connexion-bloc">
    <h1 class="connexion-bloc__title">Connexion</h1>
    
    <p class="login-instructions">Utilisez votre pseudo jeuxvideo.com pour profiter de JVForum.</p>

    <form class="connect-form" action="/connexion" method="post">
      <input class="connect-form__input" type="text" name="nick" placeholder="Pseudo" maxlength="15" autofocus autocorrect="off">
      <input class="connect-form__input" type="password" name="pass" placeholder="Mot de passe">
      <input class="js-captcha connect-form__input connect-form__input--center" type="number" name="captcha" placeholder="Code">
      <script>
var hasTouch = 'createTouch' in document
function getCaptchaType() {
  if (hasTouch) {
    if (navigator.userAgent.indexOf(' (iPhone; ') > -1 || navigator.userAgent.indexOf(' (iPod; ') > -1) {
      return 'tel'
    }
    return 'number'
  }
  return 'text'
}
document.getElementsByClassName('js-captcha')[0].setAttribute('type', getCaptchaType())
      </script>
      <input class="connect-form__submit" type="submit" value="Me connecter">
    </form>

    <p class="login-disclaimer">Votre identifiant sera transmis au serveur de JVForum sans y être stocké.</p>
  </div>

  <div class="connexion-disclaimer">
    <h2 class="connexion-disclaimer__title">Pourquoi dois-je donner mon identifiant ?</h2>
    
    <p class="connexion-disclaimer__copy">À la base, JVForum pouvait être utilisé sans être connecté. Cette fonctionnalité a été <a class="mandatory-login-proof" href="http://www.jeuxvideo.com/nplay/forums/message/714206419" target="_blank">retirée sur demande de JVC</a>.</p>
    
    <div class="legalese">JVForum n’est pas affilié à <a class="legalese__link" href="http://www.jeuxvideo.com/">jeuxvideo.com</a>.</div>
  </div>
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
<script src="/scripts/fastclick-<?= REVISION_NUMBER_JS_FASTCLICK ?>.js" onload="FastClick.attach(document.body)"></script>
