<?php

require_once '../php-encryption/autoload.php';
require '../config.php';
require 'helpers.php';
require 'Jvc.php';
require 'Db.php';
require 'Auth.php';

$jvc = new Jvc();
if (!$jvc->is_connected()) {
  header('Location: /');
  exit;
}

$db = new Db();
$auth = new Auth($db);

$token = $auth->generate();
$blacklist_query = get_blacklist_from_db();
if ($blacklist_query) {
  $blacklist = explode(',', $blacklist_query['blacklist']);
  $blacklist_is_fresh = (bool)$blacklist_query['is_fresh'];
}
else {
  $blacklist = [];
  $blacklist_is_fresh = false;
}

$allowed_pages = ['accueil', 'apropos', 'forum', 'kick', 'lock', 'moderation', 'recherche_forum', 'sanctionner', 'sondage', 'topic', 'smileys', 'profil'];
$param_page = isset($_GET['page']) ? $_GET['page'] : '';
$view = 'accueil';
if (in_array($param_page, $allowed_pages)) {
  $view = $param_page;
}

ob_start();
require 'pages/' . $view . '.php';
$body = ob_get_contents();
ob_end_clean();
require 'pages/layout.php';
