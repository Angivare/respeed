<?php
$id_message = isset($_GET['id_message']) ? (int)$_GET['id_message'] : 0;
if (!$id_message) {
  exit;
}

require '../Jvc.php';
$jvc = new Jvc();

$rep = $jvc->get("http://www.jeuxvideo.com/respeed/forums/message/{$id_message}");
$location = Jvc::redirects($rep['header']);
if($location) {
  $rep = $jvc->get("http://www.jeuxvideo.com{$location}");

  if(preg_match('#<span class="picto-msg-tronche" title="Blacklister" data-id-alias="(?P<id>.+?)">#si', $rep['body'], $matches)) {
    echo json_encode([
      $jvc->blacklist_add($matches['id']),
      $jvc->err()
    ]);
    exit;
  }
}
echo json_encode(['rep' => FALSE, 'err' => 'Message inexistant']);
