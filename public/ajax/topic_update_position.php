<?php
require 'common.php';

arg('topic_id', 'message_id', 'nb_answers');

if (!$topic_id || !$message_id || !$nb_answers) {
  halt('no params');
}

$db->set_topic_position($jvc->user_id, $topic_id, $message_id, $nb_answers);
echo 'ok';
