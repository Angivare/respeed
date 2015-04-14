<?php
require 'common.php';

arg('id', 'type', 'action');
$jvc = new Jvc();

if($id && $type && $action)
  echo json_encode([
    'rep' => $jvc->favorites_update($id, $type, $action),
    'err' => $jvc->err()
  ]);
