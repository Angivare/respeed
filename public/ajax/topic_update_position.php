<?php
require 'common.php';

arg('topic_id_new', 'message_id', 'nb_answers', 'last_page');

if (!$topic_id_new || !$message_id) {
  halt('no params');
}

$position = $db->get_topic_position($jvc->user_id, $topic_id_new);

$page_in_db = 1 + floor($position[0] / 20);
$page_seen = 1 + floor($nb_answers / 20);
if ($last_page != $page_seen && $page_in_db > $page_seen) {
  exit('no update');
}

$db->set_topic_position($jvc->user_id, $topic_id_new, $message_id, $nb_answers);
echo 'ok';
