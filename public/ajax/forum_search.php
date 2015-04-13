<?php
require 'common.php';

$query = isset($_GET['query']) ? $_GET['query'] : NULL;

if($query)
  echo json_encode($db->serach_forum($query));
