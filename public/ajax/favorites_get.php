<?php
require 'common.php';

arg('page');

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
echo json_encode(['html' => $html]);
