<?php
require 'common.php';

require '../helpers.php';
require '../parser.php';

arg('forum', 'topic', 'slug', 'page');
if(!$page) $page = 1;

if($forum && $topic && $slug) {
  $t = fetch_topic($topic, $page, $slug, $forum);
  echo json_encode(fetch_topic($topic, $page, $slug, $forum));
} else if($forum && $slug) {
  echo json_encode(fetch_forum($forum, $page, $slug));
}
