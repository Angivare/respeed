<?php

class Auth {
  const RAND_BYTES = 2;

  private $db;
  private $err;

  public function __construct($db) {
    $this->db = $db;
    $this->err = 'Indéfinie';
  }

  public function err() {
    return $this->err;
  }

  public function generate() {
    $nick = isset($_COOKIE['pseudo']) ? strtolower($_COOKIE['pseudo']) : '';
    $ts = time();
    $rand = openssl_random_pseudo_bytes(self::RAND_BYTES);
    do {
      $hash = md5($nick . SALT . $ts . $rand);
    } while($this->db->get_token($hash));
    $this->db->set_token($hash);
    return [
      'hash' => $hash,
      'ts' => $ts,
      'rand' => bin2hex($rand)
    ];
  }

  public function validate($hash, $ts, $rand) {
    $ip = ip2long($_SERVER['REMOTE_ADDR']);
    if($this->db->query('SELECT ip FROM ip_blacklist WHERE ip=?', [$ip])->fetch())
      return $this->_err('Ip blacklistée');

    if(strlen($rand)%2) return $this->_err('Jeton invalide');
    $nick = isset($_COOKIE['pseudo']) ? strtolower($_COOKIE['pseudo']) : '';
    $recreated = md5($nick . SALT . $ts . hex2bin($rand));
    if($hash != $recreated)
      return $this->_err('Jeton invalide');

    $stored = $this->db->get_token($hash);
    if(!$stored)
      return $this->_err('Jeton expiré');
    else if(strtotime($stored['generated']) < time() - 3600)
      return $this->_err('Jeton expiré');
    else
      return TRUE;
  }

  private function _err($err) {
    $this->err = $err;
    return FALSE;
  }
}
