<?
header('HTTP/1.1 204 No Content');

include('../config.php');

try {
  $dbh = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
}
catch (PDOException $e) {
  exit("Probleme avec MySQL.");
}

if (isset($_POST['clicks_minus_touchstart'])) {
  $query = 'INSERT INTO icstats2 VALUES(NULL, :ts, :clicks_minus_touchstart, :ip_address)';

  $ts = time();
  $clicks_minus_touchstart = $_POST['clicks_minus_touchstart'];
  $ip_address = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

  $stmt = $dbh->prepare($query);
  $stmt->bindParam(':ts', $ts);
  $stmt->bindParam(':clicks_minus_touchstart', $clicks_minus_touchstart);
  $stmt->bindParam(':ip_address', $ip_address);
  $stmt->execute();
}
