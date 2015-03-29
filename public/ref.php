<?php
$ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

$re = '#^https?://(www|m)\.jeuxvideo\.com/forums/' .
      '(?P<mode>[0-9]+)-' .
      '(?P<forum>[0-9]+)-' .
      '(?P<topic>[0-9]+)-' .
      '(?P<page>[0-9]+)-' .
      '0-1-0-' .
      '(?P<slug>[0-9a-z-]+)' .
      '\.htm$#Usi';

$url = '/';
if(preg_match($re, $ref, $match)) {
  foreach($match as $k => $v) $$k = $v;
  $mode = $mode == 1 ? '0' : '';
  $url = "/{$forum}/{$mode}{$topic}-{$slug}";
  if ($page != 1) {
    $url .= "/{$page}";
  }
}
header('Location: ' . $url);
