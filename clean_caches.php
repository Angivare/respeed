<?php
require 'config.php';
require 'public/db.php';

$db = new Db();
$db->clean_topic_cache();
$db->clean_forum_cache();
