<?php
require 'common.php';

arg('id', 'type', 'action');

if($id && $type && $action)
  echo json_encode([
    'rep' => $jvc->favorites_update($id, $type, $action),
    'err' => $jvc->err()
  ]);
