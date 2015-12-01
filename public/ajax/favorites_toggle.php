<?php
require 'common.php';

arg('id', 'type', 'action', 'forum_sum', 'topic_sum');


if (!$id || !$type || !$action) {
  halt('no params');
}
if (!in_array($type, ['forum', 'topic'])) {
  halt('wrong type');
}
if (!in_array($action, ['add', 'delete'])) {
  halt('wrong action');
}

$jvc->favorites_update($id, $type, $action);

$favorites = $jvc->get_favorites();

$db->update_favorites($jvc->user_id, $favorites['forums'], $favorites['topics']);

$html = [];
if ($forum_sum != get_favorites_sum($favorites['forums'])) {
  $html['forums'] = generate_favorites_forums_markup($favorites);
  $html['forumSum'] = get_favorites_sum($favorites['forums']);
}
if ($topic_sum != get_favorites_sum($favorites['topics'])) {
  $html['topics'] = generate_favorites_topics_markup($favorites);
  $html['topicSum'] = get_favorites_sum($favorites['topics']);
}

echo json_encode(['html' => $html]);
