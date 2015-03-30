<?php
require 'common.php';

$signature = isset($_GET['signature']) ? $_GET['signature'] : '';
if (!$signature) {
  exit;
}

header('Content-Type: image/jpeg');

$jvc = new Jvc();
$x = $jvc->get("http://www.jeuxvideo.com/captcha/ccode.php", $signature, true);
echo $x['body'];
