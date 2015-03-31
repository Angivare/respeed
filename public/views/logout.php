<?php

require '../Auth.php';

$hash = isset($_GET['hash']) ? $_GET['hash'] : '';

$auth = new Auth(new Db());

if(!$hash)
  die('Pas de jeton');
else if(!$auth->validate($hash))
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
