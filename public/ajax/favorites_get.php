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
if ($page == 'forum_or_topic') {
  $html = [
    'forums' => generate_favorites_forums_markup($favorites),
    'topics' => generate_favorites_topics_markup($favorites),
  ];
}
elseif ($page == 'index') {
  $html = [
    'forums' => generate_favorites_forums_markup_index($favorites),
    'topics' => generate_favorites_topics_markup_index($favorites),
  ];
}

$html = [];
if (in_array($page, ['forum_or_topic', 'index'])) {
  $func_forum = $page == 'index' ? 'generate_favorites_forums_markup_index' : 'generate_favorites_forums_markup';
  $func_topic = $page == 'index' ? 'generate_favorites_topics_markup_index' : 'generate_favorites_topics_markup';
  if ($forum_sum != get_favorites_sum($favorites['forums'])) {
    $html['forums'] = $func_forum($favorites);
    $html['forumSum'] = get_favorites_sum($favorites['forums']);
  }
  if ($topic_sum != get_favorites_sum($favorites['topics'])) {
    $html['topics'] = $func_topic($favorites);
    $html['topicSum'] = get_favorites_sum($favorites['topics']);
  }
}
echo json_encode(['html' => $html]);
