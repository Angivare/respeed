<?php
require 'common.php';

require '../helpers.php';
require '../parser.php';

arg('url', 'title', 'msg', 'form', 'ccode');

if ($url && $msg && $form) {
  $url_end = explode('/', $url);
  $url_end = array_pop($url_end);
  $url_end = explode('-', $url_end);

  $forum_id = $url_end[1];

  $insert_id = $db->log_message($forum_id);
  
  $location = '';
  echo json_encode([
    'rep' => $jvc->topic_post_finish($url, $title, $msg, $form, '', [], $ccode, $location),
    'err' => $jvc->err()
  ]);

  if ($location && preg_match('#/forums/(?P<topic_mode>[0-9]+)-(?P<forum>[0-9]+)-(?P<topic>[0-9]+)-(?P<page>[0-9]+)-0-1-0-(?P<slug>[0-9a-z-]+).htm#', $location, $matches)) {
    $db->log_message_update($insert_id, null, $matches['topic_mode'], $matches['topic']);
  }
} else if ($url) {
  echo json_encode([
    'rep' => $jvc->topic_post_req($url),
    'err' => $jvc->err()
  ]);
}
