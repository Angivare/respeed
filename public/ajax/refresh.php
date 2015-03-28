<?php
$forum = isset($_GET['forum']) ? (int)$_GET['forum'] : 0;
$topic = isset($_GET['topic']) ? (int)$_GET['topic'] : 0;
$slug = isset($_GET['slug']) ? $_GET['slug'] : FALSE;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

require '../../config.php';
require '../Jvc.php';
require '../Db.php';
require '../helpers.php';
require '../parser.php';

if($forum && $topic && $slug)
  echo json_encode(fetch_topic($topic, $page, $slug, $forum));
else if($forum && $slug)
  echo json_encode(fetch_forum($forum, $page, $slug));
