<?php

class Auth {
  const RAND_BYTES  = 2;
  const UID_BYTES   = 4;

  private $db;
  private $err;

  public function __construct($db) {
    $this->db = $db;
    $this->err = 'Indéfinie';
    $this->uid = isset($_COOKIE['auth-uid']) ? $_COOKIE['auth-uid'] : 0;

    if(!$this->uid)
      $this->uid = Auth::refresh_uid();
  }

  public function err() {
    return $this->err;
  }

  public static function crypto_rand_hex($bytes) {
    $ret = 0;
    while(!$ret)
      $ret = bin2hex(openssl_random_pseudo_bytes($bytes));
    return $ret;
  }

  public static function refresh_uid() {
    $uid = Auth::crypto_rand_hex(Auth::UID_BYTES);
    _setcookie('auth-uid', $uid);
    return $uid;
  }

  public function generate() {
    $ts = time();
    $rand = Auth::crypto_rand_hex(self::RAND_BYTES);
    do {
      $hash = md5($this->uid . SALT . $ts . $rand);
    } while($this->db->get_token($hash));
    $this->db->set_token($hash);
    return [
      'hash' => $hash,
      'ts' => $ts,
      'rand' => $rand
    ];
  }

  public function validate($hash, $ts, $rand) {
    $ip = $_SERVER['REMOTE_ADDR'];
    if ($this->db->query('SELECT ip FROM ip_blacklist WHERE ip = ?', [$ip])->fetch()) {
      return $this->_err('Ip blacklistée');
    }

    if (strlen($rand) % 2) {
      return $this->_err('Jeton invalide');
    }

    if(!$this->uid) {
      return $this->_err('Jeton invalide');
    }

    $recreated = md5($this->uid . SALT . $ts . $rand);
    if ($hash != $recreated) {
      return $this->_err('Jeton invalide');
    }

    $stored = $this->db->get_token($hash);
    if (!$stored) {
      return $this->_err('Jeton expiré');
    }
    elseif (strtotime($stored['generated']) < time() - 3600) {
      return $this->_err('Jeton expiré');
    }
    else {
      return true;
    }
  }

  public function discard($hash) {
    //needs validation beforehand
    $this->db->query('DELETE FROM tokens WHERE token = ?', [$hash]);
  }

  private function _err($err) {
    $this->err = $err;
    return false;
  }
}
