<?php
$message_id = isset($_GET['message_id']) ? (int)$_GET['message_id'] : 0;
if(!$message_id) exit;

require '../Jvc.php';
$jvc = new Jvc();

echo json_encode([
  'rep' => $jvc->blacklist_remove($message_id),
  'err' => $jvc->err()
]);
