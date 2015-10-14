<?php

require '../../config.php';
require '../Db.php';
require '../Jvc.php';
require '../Auth.php';

function arg($varname) {
  for($i = 0; $i < func_num_args(); $i++) {
    $varname = func_get_arg($i);
    global $$varname;
    $$varname = isset($_POST[$varname]) ? $_POST[$varname] : 0;
  }
}

$hash = isset($_GET['hash']) ? $_GET['hash'] : 0;
$ts = isset($_GET['ts']) ? $_GET['ts'] : 0;
$rand = isset($_GET['rand']) ? $_GET['rand'] : 0;
$site = isset($_GET['site']) ? $_GET['site'] : 'JVC';

$db = new Db();
$auth = new Auth($db);
$jvc = new Jvc($site);

if(!$hash || !$ts || !$rand) {
  echo json_encode([ 'rep' => FALSE, 'err' => 'Paramètres invalides' ]);
  exit;
} else if(!$auth->validate($hash, $ts, $rand)) {
  echo json_encode([ 'rep' => FALSE, 'err' => $auth->err() ]);
  exit;
}
