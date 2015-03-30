<?php

require '../../config.php';
require '../db.php';
require '../Jvc.php';
require '../Auth.php';

$hash = isset($_GET['hash']) ? $_GET['hash']  : '';

$db = new Db();
$auth = new Auth($db);

if(!$hash || !$auth->validate($hash)) {
  echo json_encode([ 'rep' => FALSE, 'err' => $auth->err() ]);
  exit;
}
