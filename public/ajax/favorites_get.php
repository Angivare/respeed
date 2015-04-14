<?php
require 'common.php';

$jvc = new Jvc();

echo json_encode([
  'rep' => $jvc->favorites_get(),
  'err' => $jvc->err()
  ]);
