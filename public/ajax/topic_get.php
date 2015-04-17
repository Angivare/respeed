<?php
require 'common.php';

require '../helpers.php';
require '../parser.php';

arg('forum', 'topic', 'slug', 'page', 'liste_messages');
if(!$page) $page = 1;

if($forum && $topic && $slug) {
  $t = fetch_topic($topic, $page, $slug, $forum);
  for ($i = 0; $i < count($t['messages']); $i++) {
    if (!in_array($t['messages'][$i]['id'], $liste_messages)) {
      $t['messages'][$i]['markup'] = generate_message_markup($t['messages'][$i]);
    }
  }
  $t['page'] = (int)$page; // Pour vérifier simplement qu’on a la bonne page dans app.js
  
  echo json_encode($t);
} else if($forum && $slug) {
  echo json_encode(fetch_forum($forum, $page, $slug));
}
