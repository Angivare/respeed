<?php

require '../../config.php';
require '../db.php';
require '../Jvc.php';
require '../Auth.php';

$hash = isset($_GET['hash']) ? $_GET['hash']  : '';
$ts = isset($_GET['ts']) ? (int)$_GET['ts'] : 0;
$rand = isset($_GET['rand']) ? $_GET['rand'] : '';

$db = new Db();
$auth = new Auth($db);

if(!$hash || !$ts || !$rand) {
  echo json_encode([ 'rep' => FALSE, 'err' => 'Paramètres invalides' ]);
  exit;
} else if(!$auth->validate($hash, $ts, $rand)) {
  echo json_encode([ 'rep' => FALSE, 'err' => $auth->err() ]);
  exit;
}

function arg($varname) {
  for($i = 0; $i < func_num_args(); $i++) {
    $varname = func_get_arg($i);
    global $$varname;
    $$varname = isset($_POST[$varname]) ? $_POST[$varname] : 0;
  }
}
