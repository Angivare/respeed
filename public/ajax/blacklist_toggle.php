<?php
require 'common.php';

arg('pseudo', 'action');

if (!$pseudo || !$action) {
  exit;
}
$pseudo = strtolower($pseudo);

$person = get_blacklist_person();

$results = get_blacklist_from_db($person);

if (!$results) {
  echo json_encode([
    'rep' => false,
    'err' => 'Pas de blacklist dans la base de données.',
  ]);
  exit;
}

$blacklist = explode(',', $results['blacklist']);
if (!$results['is_fresh']) {
  $blacklist = $jvc->blacklist_get();
  $db->update_blacklist($person, $blacklist);
}

if (is_in_blacklist($pseudo) && $action == 'add') {
  echo json_encode([
    'rep' => false,
    'err' => 'Déjà dans la liste.',
  ]);
  exit;
}

if (!is_in_blacklist($pseudo) && $action == 'remove') {
  echo json_encode([
    'rep' => false,
    'err' => 'Déjà pas dans la liste.',
  ]);
  exit;
}

if ($action == 'add') {
  $blacklist[] = strtolower($pseudo);
  $db->update_blacklist($person, $blacklist);
  $jvc->blacklist_add($pseudo);
}
elseif ($action == 'remove') {
  $newBlacklist = [];
  foreach ($blacklist as $blacklisted) {
    if ($blacklisted != $pseudo) {
      $newBlacklist[] = $blacklisted;
    }
  }
  $db->update_blacklist($person, $newBlacklist);
  $jvc->blacklist_remove($pseudo);
}

echo json_encode([
  'rep' => $action,
  'err' => $jvc->err(),
]);
