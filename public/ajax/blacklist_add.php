<?php
$message_id = isset($_GET['message_id']) ? (int)$_GET['message_id'] : 0;
if (!$message_id) {
  exit;
}

require '../Jvc.php';
$jvc = new Jvc();

$rep = $jvc->get("http://www.jeuxvideo.com/respeed/forums/message/{$message_id}");
$location = Jvc::redirects($rep['header']);
if($location) {
  $rep = $jvc->get("http://www.jeuxvideo.com{$location}");

  if(preg_match('#<span class="picto-msg-tronche" title="Blacklister" data-id-alias="(?P<id>.+?)">#si', $rep, $matches))
    echo json_encode(
      $jvc->blacklist_add($matches['id']),
      $jvc->err()
    );
}
echo json_encode(['rep' => FALSE, 'err' => 'Message inexistant']);
