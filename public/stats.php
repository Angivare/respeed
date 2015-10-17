<?php
require('../config.php');
if (!defined('STATS_PASS') || !STATS_PASS) {
  exit('No STATS_PASS.');
}
?>
<!doctype html>
<meta charset="utf-8">
<title>Stats Respeed</title>
<meta name="robots" content="noindex, nofollow">
<style>body { font: 17px/1.4 sans-serif; }</style>
<?php
$pass = isset($_POST['pass']) ? $_POST['pass'] : '';
$pass = isset($_COOKIE['stats_pass']) ? $_COOKIE['stats_pass'] : $pass;
if ($pass != STATS_PASS) { ?>
<form action="/stats.php" method="post" style="transform: scale(3); transform-origin: top left;">
  <input name="pass" type="password" autofocus>
  <input type="submit">
</form>
<?php
  exit;
}

setcookie('stats_pass', STATS_PASS, time() + 60 * 60 * 24 * 365 * 10, '/', null, false, true);

try {
  $dbh = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
}
catch (PDOException $e) {
  exit("Probleme avec MySQL.");
  #echo($e->getMessage());
}

function n($number) {
  return number_format($number, 0, ',', ' ');
}

$time = time();

$days = [];
$start_date = '';

$stmt = $dbh->prepare("SELECT DATE(posted_at) AS jour, COUNT(*) AS messages FROM logs_messages2 GROUP BY DATE(posted_at)");
$stmt->execute();
while ($row = $stmt->fetch()) {
  $days[$row['jour']] = (int)$row['messages'];
  if (!$start_date) {
    $start_date = strtotime($row['jour']);
  }
}

$jours = ['lundi', 'mardi', 'mercr', 'jeudi', 'vendr', 'samedi', 'dim'];
$mois = ['janv', 'fév', 'mars', 'avr', 'mai', 'juin', 'juil', 'août', 'sept', 'oct', 'nov', 'déc'];

?>

<table>
  <tr>
    <th>Jour</th>
    <th>Messages</th>
  </tr>
<?php for ($i = $start_date; $i < time(); $i += 60 * 60 * 24): ?>
  <tr>
    <td style="color: #333"><?= $jours[date('w', $i)] . ' ' . date('d', $i) . ' ' . $mois[date('n', $i) - 1] ?></td>
    <td style="text-align: right;"><a href="?day=<?= date('Y-m-d', $i) ?>" style="font-weight: bold; text-decoration: none;"><?= isset($days[date('Y-m-d', $i)]) ? n($days[date('Y-m-d', $i)]) : 0 ?></a></td>
  </tr>
<?php endfor ?>
</table>
