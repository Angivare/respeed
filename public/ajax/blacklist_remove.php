<?php
require 'common.php';

arg('nick');

if($nick) {
  $nick = strtolower($nick);

  $bl = $jvc->blacklist_get();

  foreach($bl as $entry)
  	if(strtolower($entry['human']) === $nick) {
  		echo json_encode([
  			$jvc->blacklist_remove($entry['id']),
  			$jvc->err()
  		]);
  		exit;
  	}

  echo json_encode(['rep' => FALSE, 'err' => 'Message inexistant ou non blacklisté']);
}
