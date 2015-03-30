<?php
require 'common.php';

$nick = isset($_GET['nick']) ? strtolower($_GET['nick']) : 0;
if (!$nick) {
  exit;
}

$jvc = new Jvc();

$bl = $jvc->blacklist();

foreach($bl as $entry)
	if(strtolower($entry['human']) === $nick) {
		echo json_encode([
			$jvc->blacklist_remove($entry['id']),
			$jvc->err()
		]);
		exit;
	}

echo json_encode(['rep' => FALSE, 'err' => 'Message inexistant ou non blacklisté']);
