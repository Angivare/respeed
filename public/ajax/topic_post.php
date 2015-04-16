<?php
require 'common.php';

require '../helpers.php';
require '../parser.php';

arg('url', 'title', 'msg', 'form', 'ccode');

if($url && $msg && $form) {
  $location = '';
  echo json_encode([
    'rep' => $jvc->topic_post_finish($url, $title, $msg, $form, '', [], $ccode, $location),
    'err' => $jvc->err()
  ]);
  if(!$location) exit;

  //Logging
  preg_match('#/forums/(?P<topic_mode>.+)-(?P<forum>.+)-(?P<topic>.+)-(?P<page>.+)-0-1-0-(?P<slug>.+).htm#U', $location, $l);
  if($l['topic_mode'] === '1') $l['topic'] = '0' . $l['topic'];
  $got = $jvc->get("http://www.jeuxvideo.com{$location}");
  $m = parse_topic($got['body'])['messages'];
  $i = count($m)-1;
  $db->log_message(
    $m[$i]['id'],
    $l['topic'],
    $l['forum'],
    ip2long($_SERVER['REMOTE_ADDR']),
    date('Y-m-d H:i:s', time()),
    $m[$i]['pseudo']
  );

} else if($url) {
  echo json_encode([
    'rep' => $jvc->topic_post_req($url),
    'err' => $jvc->err()
  ]);
}
