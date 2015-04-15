<?php
require 'common.php';

echo json_encode([
  'rep' => $jvc->favorites_get(),
  'err' => $jvc->err()
  ]);
