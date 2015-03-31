<?php

$hash = isset($_GET['hash']) ? $_GET['hash']  : '';
$ts = isset($_GET['ts']) ? (int)$_GET['ts'] : 0;
$rand = isset($_GET['rand']) ? $_GET['rand'] : '';

$auth = new Auth(new Db());

if(!$hash || !$ts || !$rand)
  die('Paramètres invalides');
else if(!$auth->validate($hash, $ts, $rand))
  die($auth->err());

$jvc = new Jvc();
$jvc->disconnect();

$ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$location = '/';
if ($ref) {
  $ref = explode('/', $ref);
  $ref = array_slice($ref, 3);
  $ref = '/' . implode('/', $ref);
}
header('Location: ' . $ref);
exit;
