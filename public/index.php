<?php
$forum = isset($_GET['forum']) ? (int)$_GET['forum'] : false;
$topic = isset($_GET['topic']) ? (int)$_GET['topic'] : false;
$slug = isset($_GET['slug']) ? $_GET['slug'] : false;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$login = isset($_GET['login']) ? true : false;

require 'helpers.php';
require 'Jvc.php';

ob_start();
if ($login)
  require 'views/login.php';
elseif ($forum && $topic && $slug)
  require 'views/topic.php';
elseif ($forum && $slug)
  require 'views/forum.php';
else
  require 'views/index.php';
$body = ob_get_contents();
ob_end_clean();
require 'views/layout.php';
