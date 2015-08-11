<?php
require 'common.php';

require '../helpers.php';
require '../parser.php';

$re = '#https?://' . $_SERVER['HTTP_HOST'] . '/(?P<forum>[0-9]+)(/(?P<mode>0)?(?P<topic>[0-9]+))?-(?P<slug>[a-z]+(-[a-z0-9]+)*)(/(?P<page>[0-9]+))?(\#(?P<message>[0-9]+))?#i';

arg('url', 'msg', 'form', 'ccode');

if ($url && $msg && $form) {
  $msg = preg_replace_callback($re, function($m) {
    $mode = '0';
    if($m['topic'] !== '')
      $mode = ($m['mode'] === '0') ? '1' : '42';
    return  'http://www.jeuxvideo.com/forums/' . $mode . '-' . $m['forum'] . '-' . ($m['topic'] === '' ? '0' : $m['topic']) . '-' .
            (isset($m['page']) ? $m['page'] : '1') . '-0-1-0-' . $m['slug'] . '.htm' . (isset($m['message']) ? ('#post_'.$m['message']) : '');
  }, $msg);

  $url_end = explode('/', $url);
  $url_end = array_pop($url_end);
  $url_end = explode('-', $url_end);

  $forum_id = $url_end[1];
  $topic_mode = $url_end[0];
  $topic_id = $url_end[2];

  $insert_id = $db->log_message($forum_id, $topic_mode, $topic_id);
  
  $location = '';
  echo json_encode([
    'rep' => $jvc->message_post_finish($url, $msg, $form, $ccode, $location),
    'err' => $jvc->err()
  ]);
  
  if ($location && preg_match('#/forums/(?P<topic_mode>[0-9]+)-(?P<forum>[0-9]+)-(?P<topic>[0-9]+)-(?P<page>[0-9]+)-0-1-0-(?P<slug>[0-9a-z-]+).htm\#post_(?P<message_id>[0-9]+)#', $location, $matches)) {
    $db->log_message_update($insert_id, $matches['message_id']);
  }
} else if ($url) {
  echo json_encode([
    'rep' => $jvc->message_post_req($url),
    'err' => $jvc->err()
  ]);
}
