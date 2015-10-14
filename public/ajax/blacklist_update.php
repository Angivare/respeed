<?php
require 'common.php';

$person = get_blacklist_person();
$results = get_blacklist_from_db($person);

if ($results) {
  $blacklist = explode(',', $results['blacklist']);
  if (!$results['is_fresh']) {
    $blacklist = $jvc->blacklist_get();
    $db->update_blacklist($person, $blacklist);
  }
}
else {
  $blacklist = $jvc->blacklist_get();
  $db->set_blacklist($person, $blacklist);
}

echo json_encode([
  'rep' => [
    'array' => $blacklist,
    'style' => generate_blacklist_style($blacklist),
  ],
  'err' => $jvc->err(),
]);
