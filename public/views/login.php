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
elseif($nick && $pass):
  $jvc->disconnect();
  $form = $jvc->connect_req($nick, $pass);
?>
  <form action="" method="post">
    <input type="hidden" name="form" value="<?php echo urlencode(serialize($form)) ?>">
    <input type="text" name="nick" placeholder="Pseudo" value="<?= $nick?>">
    <input type="password" name="pass" placeholder="Mot de passe" value="<?= $pass?>">
    <img src="data:image/png;base64,<?php echo base64_encode(
      file_get_contents('http://www.jeuxvideo.com/captcha/ccode.php?' .
      $form['fs_signature']
      )) ?>">
    <input type="text" name="ccode" placeholder="Code de confirmation">
    <input type="submit">
  </form>
<?php else: ?>
  <form action="" method="post">
    <input type="text" name="nick" placeholder="Pseudo">
    <input type="password" name="pass" placeholder="Mot de passe">
    <input type="submit">
  </form>
<?php endif; ?>
