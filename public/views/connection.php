<?php session_start();

require_once('../Jvc.php');
$jvc = new Jvc();

$ccode = isset($_POST['ccode']) ? $_POST['ccode'] : NULL;
$form = isset($_POST['form']) ? $_POST['form'] : NULL;

if($ccode === NULL || $form === NULL):
  $jvc->disconnect();
  $form = $jvc->connect_req("code_grivois", "evRP4");
  var_dump($form);
?>
  <img src="data:image/png;base64,<?php echo base64_encode(
    file_get_contents('http://www.jeuxvideo.com/captcha/ccode.php?' .
    $form['fs_signature']
    )) ?>">
  <form action="" method="post">
    <input type="hidden" name="form" value="<?php echo urlencode(serialize($form)) ?>">
    <input type="text" name="ccode">
  </form>
<?php else:
  $form = unserialize(urldecode($form));
  if(is_array($form) && ctype_digit($ccode)) {
    $url = 'http://www.jeuxvideo.com/forums/42-1000021-38431092-949-0-1-0-actu-un-blabla-est-ne.htm';
    var_dump($jvc->connect_finish("code_grivois", "evRP4", $form, $ccode));
    var_dump($jvc->err());
    $form = $jvc->post_msg_req($url);
  //  sleep(1);
  //  var_dump($jvc->post_msg_finish($url, 'coucou', $form));
  //  var_dump($jvc->err());
  }
endif
?>
