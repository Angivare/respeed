<?php
require 'common.php';

arg('query');

if($query)
  echo json_encode($db->serach_forum($query));
