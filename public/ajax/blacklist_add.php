<?php
$message_id = isset($_GET['message_id']) ? (int)$_GET['message_id'] : 0;
if (!$message_id) {
  exit;
}

// Todo : Faire une requête connectée vers le lien permanent du message ( http://www.jeuxvideo.com/respeed/forums/message/{$message_id} ) en suivant les redirections
// Récupérer <span class="picto-msg-tronche" title="Blacklister" data-id-alias="(?P<id)>">, et mettre à jour tokens
// $jvc->blacklist($matches['id'], $tk)  (Mais pk ne pas prendre le token direct depuis les cookies au lieu de le passer en argument ?)

require '../Jvc.php';
$jvc = new Jvc();
