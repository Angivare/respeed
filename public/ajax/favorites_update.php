<?php
require 'common.php';

arg('page', 'forum_sum', 'topic_sum');

$favorites = $db->get_favorites($jvc->user_id);

if (!$favorites || !$favorites['is_fresh']) {
  $func = $favorites ? 'update_favorites' : 'add_favorites';
  $favorites = $jvc->get_favorites();
  $db->$func($jvc->user_id, $favorites['forums'], $favorites['topics']);
}

$html = [];
$html = [
  'forums' => generate_favorites_forums_markup($favorites),
  'topics' => generate_favorites_topics_markup($favorites),
];

$html = [];
if ($forum_sum && $topic_sum) {
  if ($forum_sum != get_favorites_sum($favorites['forums'])) {
    $html['forums'] = generate_favorites_forums_markup($favorites);
    $html['forumSum'] = get_favorites_sum($favorites['forums']);
  }
  if ($topic_sum != get_favorites_sum($favorites['topics'])) {
    $html['topics'] = generate_favorites_topics_markup($favorites);
    $html['topicSum'] = get_favorites_sum($favorites['topics']);
  }
}
echo json_encode(['html' => $html]);
