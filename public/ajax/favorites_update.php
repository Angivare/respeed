<?php
require 'common.php';

arg('id', 'type', 'action');


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

$html = [
  'forums' => generate_favorites_forums_markup($favorites),
  'topics' => generate_favorites_topics_markup($favorites),
];
echo json_encode(['html' => $html]);
