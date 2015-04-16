<?php
require 'common.php';

$signature = isset($_GET['signature']) ? $_GET['signature'] : 0;

if($signature) {
  header('Content-Type: image/jpeg');

  $x = $jvc->get("http://www.jeuxvideo.com/captcha/ccode.php", $signature, true);
  echo $x['body'];
}
