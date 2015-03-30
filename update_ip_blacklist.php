<?php

require 'config.php';
require 'public/db.php';

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, 'https://check.torproject.org/cgi-bin/TorBulkExitList.py?ip=128.199.54.192&port=80');
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
$got = curl_exec($ch);
curl_close($ch);

$lines = preg_split('/\R/', $got);

$ip = [];
foreach($lines as $l) {
  if(!$l || $l[0] == '#') continue;

  $ip[] = ip2long($l);
}

$db = new Db();

$sql = str_repeat('(?),', count($ip)-1) . '(?)';

$db->query('DELETE FROM ip_blacklist WHERE ip=ip', []);
$db->query(
  'INSERT INTO ip_blacklist (ip) VALUES ' .
  $sql,
  $ip
);
