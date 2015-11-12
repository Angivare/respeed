<?php
require 'common.php';

if (isset($_COOKIE['id'])) {
  exit('1');
}

use \Defuse\Crypto\Crypto;
require_once '../../php-encryption/autoload.php';

$pseudo = $jvc->get_pseudo();

if (!$pseudo) {
  exit;
}

if ($db->user_has_id($pseudo)) {
  exit('2');
}

$id = $db->create_user_id($pseudo);

$coniunctio_id = explode('$', $jvc->cookie['coniunctio'])[0];

$ciphertext = Crypto::encrypt($id . ' ' . $pseudo . ' ' . $coniunctio_id, base64_decode(ID_KEY));

_setcookie('id', base64_encode($ciphertext));
echo '9';
