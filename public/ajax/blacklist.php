<?php
require '../Jvc.php';

$jvc = new Jvc();

echo json_encode([
  'rep' => $jvc->blacklist(),
  'err' => $jvc->err()
  ]);