<?php
require '../Jvc.php';

$id = isset($_GET['id']) ? $_GET['id'] : FALSE;
$jvc = new Jvc();

if($id)
  echo json_encode([
    'rep' => $jvc->delete($id),
    'err' => $jvc->err()
  ]);