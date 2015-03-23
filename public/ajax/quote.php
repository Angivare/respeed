<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
  exit;
}

require '../Jvc.php';
$jvc = new Jvc();

echo json_encode([
  'rep' => $jvc->quote($id),
  'err' => $jvc->err()
  ]);
