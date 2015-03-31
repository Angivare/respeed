<?php
require 'common.php';

$forum = isset($_GET['forum']) ? (int)$_GET['forum'] : 0;
$topic = isset($_GET['topic']) ? (int)$_GET['topic'] : 0;
$slug = isset($_GET['slug']) ? $_GET['slug'] : FALSE;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

require '../helpers.php';
require '../parser.php';

if($forum && $topic && $slug) {
  $t = fetch_topic($topic, $page, $slug, $forum);
  foreach($t['matches']['message'] as $k => $v)
    $t['matches']['message'][$k] = adapt_html($v, strip_tags(trim($t['matches']['date'][$k])));
  echo json_encode(fetch_topic($topic, $page, $slug, $forum));
} else if($forum && $slug) {
  echo json_encode(fetch_forum($forum, $page, $slug));
}
