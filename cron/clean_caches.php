<?php
require dirname(__FILE__) . '/../config.php';
require dirname(__FILE__) . '/../public/Db.php';

$db = new Db();
$db->clean_topic_cache();
$db->clean_forum_cache();
