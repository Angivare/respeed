<?php

require '../../config.php';
require '../Db.php';
require '../Auth.php';
require '../Jvc.php';

$db = new Db();
$auth = new Auth($db);
$jvc = new Jvc();

function arg() {
  for ($i = 0; $i < func_num_args(); $i++) {
    $varname = func_get_arg($i);
    global ${$varname};
    ${$varname} = isset($_POST[$varname]) ? $_POST[$varname] : 0;
  }
}

$hash = isset($_GET['hash']) ? $_GET['hash'] : 0;
$ts = isset($_GET['ts']) ? $_GET['ts'] : 0;
$rand = isset($_GET['rand']) ? $_GET['rand'] : 0;

if (!$hash || !$ts || !$rand) {
  echo json_encode([
    'rep' => false,
    'err' => 'Paramètres invalides',
  ]);
  exit;
}
if (!$auth->validate($hash, $ts, $rand)) {
  echo json_encode([
    'rep' => false,
    'err' => $auth->err(),
  ]);
  exit;
}
