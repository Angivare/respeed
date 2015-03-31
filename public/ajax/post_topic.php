<?php
require 'common.php';

$url = isset($_POST['url']) ? $_POST['url'] : FALSE;
$title = isset($_POST['title']) ? $_POST['title'] : FALSE;
$msg = isset($_POST['msg']) ? $_POST['msg'] : FALSE;
$form = isset($_POST['form']) ? $_POST['form'] : FALSE;
$ccode = isset($_POST['ccode']) ? $_POST['ccode'] : '';

require '../helpers.php';
require '../parser.php';

$jvc = new Jvc();

if($url && $msg && $form) {
  $location = '';
  echo json_encode([
    'rep' => $jvc->post_topic_finish($url, $title, $msg, $form, '', [], $ccode, $location),
    'err' => $jvc->err()
  ]);
  if(!$location) exit;

  //Logging
  preg_match('#/forums/(?P<topic_mode>.+)-(?P<forum>.+)-(?P<topic>.+)-(?P<page>.+)-0-1-0-(?P<slug>.+).htm#U', $location, $l);
  if($l['topic_mode'] === '1') $l['topic'] = '0' . $l['topic'];
  $got = $jvc->get("http://www.jeuxvideo.com{$location}");
  $m = parse_topic($got['body'])['matches'];
  $i = count($m['post'])-1;
  $db->log_message(
    $m['post'][$i],
    $l['topic'],
    $l['forum'],
    ip2long($_SERVER['REMOTE_ADDR']),
    date('Y-m-d H:i:s', time()),
    $m['pseudo'][$i]
  );

} else if($url) {
  echo json_encode([
    'rep' => $jvc->post_topic_req($url),
    'err' => $jvc->err()
  ]);
}
