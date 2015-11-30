<?php
require 'common.php';

$favorites = $db->get_favorites($jvc->user_id);

if (!$favorites || !$favorites['is_fresh']) {
  $func = $favorites ? 'update_favorites' : 'add_favorites';
  $favorites = $jvc->get_favorites();
  $db->$func($jvc->user_id, $favorites['forums'], $favorites['topics']);
}
