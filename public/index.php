<?php
$forum = isset($_GET['forum']) ? $_GET['forum'] : false;
$topic = isset($_GET['topic']) ? $_GET['topic'] : false;
$slug = isset($_GET['slug']) && preg_match('#^[a-zA-Z0-9-]{1,200}$#', $_GET['slug']) ? $_GET['slug'] : '0';
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$connexion = isset($_GET['connexion']);
$deconnexion = isset($_GET['deconnexion']);
$accueil = isset($_GET['accueil']);
$salespage = isset($_GET['salespage']);

require 'helpers.php';
require 'Jvc.php';
require 'db.php';
require 'Auth.php';
require '../config.php';

$db = new Db();
$auth = new Auth($db);
$token = $auth->generate();

ob_start();
if ($connexion)
  require 'views/connexion.php';
elseif ($deconnexion)
  require 'views/deconnexion.php';
elseif ($forum && $topic)
  require 'views/topic.php';
elseif ($forum)
  require 'views/forum.php';
elseif ($accueil)
  require 'views/accueil.php';
else
  require 'views/salespage.php';
$body = ob_get_contents();
ob_end_clean();
require 'views/layout.php';
