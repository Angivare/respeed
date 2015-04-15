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

  preg_match('#/forums/(?P<topic_mode>.+)-(?P<forum>.+)-(?P<topic>.+)-(?P<page>.+)-0-1-0-(?P<slug>.+).htm\#post_(?P<id>[0-9]+?)#U', $location, $l);
  if($l['topic_mode'] === '1') $l['topic'] = '0' . $l['topic'];
  if($rep = $jvc->message_get($l['id'])) {
    preg_match('#(<span class="JvCare [0-9A-F]+ bloc-pseudo-msg text-(?P<status>.+)"|<div class="bloc-pseudo-msg").+>\s+?(?P<pseudo>.+)\s+<#Usi', $rep['body'], $m);
    $db->log_message(
      $l['id'],
      $l['topic'],
      $l['forum'],
      $_SERVER['REMOTE_ADDR'],
      date('Y-m-d H:i:s', time()),
      $m['pseudo']
    );
  }
} else if($url) {
  echo json_encode([
    'rep' => $jvc->topic_post_req($url),
    'err' => $jvc->err()
  ]);
}
