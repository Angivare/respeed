<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';

require '../Jvc.php';
$jvc = new Jvc();

if($id && $type && $action)
  echo json_encode([
    'rep' => $jvc->favorites_update($id, $type, $action),
    'err' => $jvc->err()
  ]);
