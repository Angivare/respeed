<?php
$jvc = new Jvc();
$jvc->disconnect();

$ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$location = '/';
if ($ref) {
  $ref = explode('/', $ref);
  $ref = array_slice($ref, 3);
  $ref = '/' . implode('/', $ref);
}
header('Location: ' . $ref);
exit;
