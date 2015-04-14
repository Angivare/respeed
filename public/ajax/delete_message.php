<?php
require 'common.php';

arg('id');
$jvc = new Jvc();

if($id)
  echo json_encode([
    'rep' => $jvc->delete($id),
    'err' => $jvc->err()
  ]);