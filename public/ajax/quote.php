<?php
require 'common.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
  exit;
}

$jvc = new Jvc();

echo json_encode([
  'rep' => $jvc->quote($id),
  'err' => $jvc->err()
  ]);
