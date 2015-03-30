<?php

class Auth {
  const SALT = 'ladp_sacre';
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
    $ts = time();
    do {
      $hash = md5(self::SALT . $ts . openssl_random_pseudo_bytes(self::RAND_BYTES));
    } while($this->db->get_token($hash));
    $this->db->set_token($hash);
    return $hash;
  }

  public function validate($hash) {
    $ip = ip2long($_SERVER['REMOTE_ADDR']);
    if($this->db->query('SELECT ip FROM ip_blacklist WHERE ip=?', [$ip])->fetch())
      return $this->_err('Ip blacklistée');
    $stored = $this->db->get_token($hash);
    if(!$stored)
      return $this->_err('Jeton expiré');
    else if(strtotime($stored['generated']) < time() - 3600)
      return $this->_err('Jeton expiré');
    else if($stored['used'])
      return $this->_err('Jeton déjà utilisé');
    else {
      $this->db->discard_token($hash);
      return TRUE;
    }
  }

  private function _err($err) {
    $this->err = $err;
    return FALSE;
  }
}
