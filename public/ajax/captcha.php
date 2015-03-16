<?php
$signature = isset($_GET['signature']) ? $_GET['signature'] : '';
if (!$signature) {
  exit;
}

header('Content-Type: image/jpeg');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://www.jeuxvideo.com/captcha/ccode.php?{$signature}");
$got = curl_exec($ch);
