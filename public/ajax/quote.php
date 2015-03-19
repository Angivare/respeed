<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
  exit;
}

require '../Jvc.php';
$jvc = new Jvc();
$tokens = $jvc->tokens();
$tk = 'ajax_timestamp=' . $tokens['ajax_timestamp_liste_messages'] . '&ajax_hash=' . $tokens['ajax_hash_liste_messages'];


usleep(mt_rand(100000, 200000));#debug, simulation de latence
echo json_encode([
  'rep' => "Faux message\r\nTotalement faux",
  //'rep' => $jvc->quote($id, $tk),// marche pas pour le moment
  'err' => $jvc->err()
  ]);
