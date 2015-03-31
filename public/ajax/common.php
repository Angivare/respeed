<?php

require '../../config.php';
require '../db.php';
require '../Jvc.php';
require '../Auth.php';

$hash = isset($_GET['hash']) ? $_GET['hash']  : '';

$db = new Db();
$auth = new Auth($db);

if(!$hash) {
  echo json_encode([ 'rep' => FALSE, 'err' => 'Pas de jeton' ]);
  exit;
} else if(!$auth->validate($hash)) {
  echo json_encode([ 'rep' => FALSE, 'err' => $auth->err() ]);
  exit;
}
