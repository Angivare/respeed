<?php
require 'common.php';

require '../parser.php';

arg('forum', 'topic_mode', 'topic_id_old_or_new', 'slug', 'page', 'last_page', 'liste_messages');

if (!is_array($liste_messages)) {
  exit;
}

$topic_id_url_jvf = $topic_id_old_or_new;
$topic_id_old = $topic_id_new = null;
if ($topic_mode == 1) {
  $topic_id_url_jvf = '0' . $topic_id_url_jvf;
}
$topic_id_array = compact('topic_id_url_jvf', 'topic_mode', 'topic_id_old_or_new', 'topic_id_old', 'topic_id_new');

if (!$page) {
  $page = 1;
}

$t = fetch_topic($topic_id_array, $page, $slug, $forum);


for ($i = 0; $i < count($t['messages']); $i++) {
  $t['messages'][$i]['date'] = relative_date_messages($t['messages'][$i]['dateRaw']);
  if (!in_array($t['messages'][$i]['id'], $liste_messages)) {
    $t['messages'][$i]['markup'] = generate_message_markup($t['messages'][$i], $is_mod);
  }
}

$is_mod = $t['moderators'] && in_array(strtolower($jvc->pseudo), array_map('strtolower', $t['moderators']));

$t['page'] = (int)$page; // Pour vérifier simplement qu’on a la bonne page dans app.js
if ($last_page != $t['last_page']) {
  $t['paginationMarkup'] = generate_topic_pagination_markup($page, $t['last_page'], $forum, $topic_id_array, $slug);
}

echo json_encode([
  'rep' => $t,
  'err' => 'Indéfinie',
]);
