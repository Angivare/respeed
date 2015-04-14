<?php
require 'common.php';

$jvc = new Jvc();

echo json_encode([
  'rep' => $jvc->blacklist_get(),
  'err' => $jvc->err()
]);
