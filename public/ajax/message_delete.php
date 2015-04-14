<?php
require 'common.php';

arg('id_message');
$jvc = new Jvc();

if($id_message)
  echo json_encode([
    'rep' => $jvc->message_delete($id_message),
    'err' => $jvc->err()
  ]);