<?php
require 'common.php';

echo json_encode([
  'rep' => $jvc->blacklist_get(),
  'err' => $jvc->err()
]);
