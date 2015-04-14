<?php
require 'common.php';

arg('id_message', 'type', 'action');

if($id_message && $type && $action)
  echo json_encode([
    'rep' => $jvc->favorites_update($id_message, $type, $action),
    'err' => $jvc->err()
  ]);
