<?php
require 'common.php';

arg('id_message');
$jvc = new Jvc();

if($id_message) {
  echo json_encode([
    'rep' => $jvc->quote($id_message),
    'err' => $jvc->err()
    ]);
}
