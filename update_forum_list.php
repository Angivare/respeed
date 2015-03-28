<?php
require 'config.php';
require 'public/Jvc.php';
require 'public/Db.php';

$db = new Db();

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);

function getForum($id) {
  global $ch;
  curl_setopt($ch, CURLOPT_URL, "http://www.jeuxvideo.com/forums/0-$id-0-1-0-1-0-0.htm");
  $rep = curl_exec($ch);
  $location = Jvc::redirects($rep);
  if(preg_match('#/forums/0-(?P<forum>.+)-0-1-0-1-0-(?P<slug>.+).htm#U', $location, $matches))
  {
    $forum = $matches['forum']; $slug = $matches['slug'];
    curl_setopt($ch, CURLOPT_URL, "http://www.jeuxvideo.com{$location}");
    $rep = curl_exec($ch);

    //Parse title
    preg_match('#<h1 class="highlight">Forum (.+)</h1>#Usi', $rep, $matches);
    $title = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $title = preg_replace_callback("/(&#[0-9]+;)/", function($m) {
      return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
    }, $title);
    
    return [ 'id' => $forum, 'slug' => $slug, 'human' => $title ];
  }
  else
    return FALSE;
}

function loop($start, $max_err) {
  global $ch, $db;
  $err = 0;
  $i = $start;

  while($err < $max_err) {
    $forum = getForum($i, $ch);
    if($forum === FALSE) {
      $err++;
      $db->delete_forum($i);
    } else {
      $err = 0;
      $db->add_forum($forum);
    }
    $i++;
  }
}

loop(1, 100);
loop(1000000, 100);
loop(3000000, 100);
