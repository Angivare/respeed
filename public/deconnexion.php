<?php
require '../config.php';
require 'Auth.php';
require 'Db.php';
require 'Jvc.php';

$hash = isset($_GET['hash']) ? $_GET['hash']  : '';
$ts = isset($_GET['ts']) ? (int)$_GET['ts'] : 0;
$rand = isset($_GET['rand']) ? $_GET['rand'] : '';

$auth = new Auth(new Db());

if (!$hash || !$ts || !$rand) {
  die('Paramètres invalides');
}
if (!$auth->validate($hash, $ts, $rand)) {
  die($auth->err());
}

$jvc = new Jvc();
$jvc->disconnect();

header('Location: /connexion');
exit;
