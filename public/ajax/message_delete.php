<?php
require 'common.php';

arg('id_message');

if (!$id_message) {
  exit;
}

echo json_encode([
  'rep' => $jvc->message_delete($id_message),
  'err' => $jvc->err(),
]);
