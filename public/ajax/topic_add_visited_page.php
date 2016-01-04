<?php
require 'common.php';

arg('topic_id_new', 'page');

if (!$topic_id_new || !$page) {
  halt('no params');
}

$is_visited = $db->is_topic_page_visited($jvc->user_id, $topic_id_new, $page);
if (!$is_visited) {
  $db->add_topic_visited_page($jvc->user_id, $topic_id_new, $page);
}
