<?php
require 'common.php';

require '../parser.php';

arg('forum', 'topic', 'slug', 'page', 'last_page', 'liste_messages', 'poll_answers');
$topic_mode = $topic[0] == '0' ? 1 : 42;
if (!$page) {
  $page = 1;
}

$t = fetch_topic($topic, $page, $slug, $forum);

$is_mod = $t['moderators'] && in_array(strtolower($jvc->pseudo), array_map('strtolower', $t['moderators']));

if ($liste_messages) { // N’est pas là en cas de timeout
  for ($i = 0; $i < count($t['messages']); $i++) {
    if (!in_array($t['messages'][$i]['id'], $liste_messages)) {
      $t['messages'][$i]['markup'] = generate_message_markup($t['messages'][$i], $is_mod);
    }
  }
}
$t['page'] = (int)$page; // Pour vérifier simplement qu’on a la bonne page dans app.js
if ($last_page != $t['last_page']) {
  $t['paginationMarkup'] = generate_topic_pagination_markup($page, $t['last_page'], $forum, $topic, $topic_mode, $slug);
}

if ($poll_answers > -1 && $t['poll'] && $t['poll']['answer_count'] != $poll_answers) {
  $t['poll'] = generate_poll_markup($t['poll'], $topic_mode, $forum, $topic, $slug);
  $t['poll_answers'] = $t['poll']['answer_count'];
}
else {
  unset($t['poll']);
}

echo json_encode([
  'rep' => $t,
  'err' => 'Indéfinie',
]);
