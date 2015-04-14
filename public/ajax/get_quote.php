<?php
require 'common.php';

arg('id');
$jvc = new Jvc();

echo json_encode([
  'rep' => $jvc->quote($id),
  'err' => $jvc->err()
  ]);
