<?php

require '../../config.php';
require '../db.php';
require '../Jvc.php';
require '../Auth.php';

function arg($varname) {
  for($i = 0; $i < func_num_args(); $i++) {
    $varname = func_get_arg($i);
    global $$varname;
    $$varname = isset($_POST[$varname]) ? $_POST[$varname] : 0;
  }
}

arg('hash', 'ts', 'rand', 'site');
if(!$site) $site = 'JVC';

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
