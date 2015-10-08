<?php
$forum = isset($_GET['forum']) ? $_GET['forum'] : false;
$topic = isset($_GET['topic']) ? $_GET['topic'] : false;
$slug = isset($_GET['slug']) && preg_match('#^[a-zA-Z0-9-]{1,200}$#', $_GET['slug']) ? $_GET['slug'] : '0';
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$deconnexion = isset($_GET['deconnexion']);
$apropos = isset($_GET['apropos']);
$recherche_forum = isset($_GET['recherche_forum']);
$smileys = isset($_GET['smileys']);
$profil = isset($_GET['profil']);

require 'helpers.php';
require 'Jvc.php';
require 'db.php';
require 'Auth.php';
require '../config.php';

$db = new Db();
$auth = new Auth($db);
$token = $auth->generate();

$view = 'accueil';
if ($forum && $topic)
  $view = 'topic';
elseif ($forum)
  $view = 'forum';
elseif ($apropos)
  $view = 'apropos';
elseif ($recherche_forum)
  $view = 'recherche_forum';
elseif ($smileys)
  $view = 'smileys';
elseif ($profil)
  $view = 'profil';

ob_start();
require 'views/' . $view . '.php';
$body = ob_get_contents();
ob_end_clean();
require 'views/layout.php';
