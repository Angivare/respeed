<?php
require 'common.php';

$url = isset($_POST['url']) ? $_POST['url'] : FALSE;
$msg = isset($_POST['msg']) ? $_POST['msg'] : FALSE;
$form = isset($_POST['form']) ? $_POST['form'] : FALSE;
$ccode = isset($_POST['ccode']) ? $_POST['ccode'] : '';

require '../helpers.php';
require '../parser.php';

$jvc = new Jvc();

if($url && $msg && $form) {
  $location = '';
  echo json_encode([
    'rep' => $jvc->post_msg_finish($url, $msg, $form, $ccode, $location),
    'err' => $jvc->err()
  ]);
  if(!$location) exit;

  preg_match('#/forums/(?P<topic_mode>.+)-(?P<forum>.+)-(?P<topic>.+)-(?P<page>.+)-0-1-0-(?P<slug>.+).htm#U', $location, $l);
  $got = $jvc->get("http://www.jeuxvideo.com{$location}");
  $m = parse_topic($got)['matches'];
  $i = count($m['post'])-1;
  $db->log_message(
    $m['post'][$i],
    $l['topic'],
    $l['forum'],
    $_SERVER['REMOTE_ADDR'],
    date('Y-m-d H:i:s', time()),
    $m['pseudo'][$i]
  );

} else if($url)
  echo json_encode([
    'rep' => $jvc->post_msg_req($url),
    'err' => $jvc->err()
  ]);
