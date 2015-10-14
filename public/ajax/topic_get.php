<?php
require 'common.php';

require '../parser.php';

arg('forum', 'topic', 'slug', 'page', 'last_page', 'liste_messages');
$topic_mode = $topic[0] == '0' ? 1 : 42;
if (!$page) {
  $page = 1;
}

if ($forum && $topic && $slug) {
  $t = fetch_topic($topic, $page, $slug, $forum);
  if ($liste_messages) { // N’est pas là en cas de timeout
    for ($i = 0; $i < count($t['messages']); $i++) {
      if (!in_array($t['messages'][$i]['id'], $liste_messages)) {
        $t['messages'][$i]['markup'] = generate_message_markup($t['messages'][$i]);
      }
    }
  }
  $t['page'] = (int)$page; // Pour vérifier simplement qu’on a la bonne page dans app.js
  if ($last_page != $t['last_page']) {
    $t['paginationMarkup'] = generate_topic_pagination_markup($page, $t['last_page'], $forum, $topic, $topic_mode, $slug);
  }

  echo json_encode([
    'rep' => $t,
    'err' => 'Indéfinie',
  ]);
}
elseif ($forum && $slug) {
  echo json_encode([
    'rep' => fetch_forum($forum, $page, $slug),
    'err' => 'Indéfinie',
  ]);
}
