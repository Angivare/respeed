<?php
$forum = isset($_GET['forum']) ? (int)$_GET['forum'] : false;
$forum_slug = isset($_GET['forum_slug']) ? $_GET['forum_slug'] : false;

ob_start();
if ($forum && $forum_slug)
  require 'views/forum.php';
else
  require 'views/index.php';
$body = ob_get_contents();
ob_end_clean();
require 'views/layout.php';
