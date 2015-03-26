<?php
class Db {
  private $db;
  private $connected;

  public function __construct() {
    $this->connected = FALSE;
    try {
      $this->db = new PDO('mysql:host=localhost;dbname=respeed', 'respeed', DB_PASS);
      $this->connected = TRUE;
    } catch(Exception $e) { die('Erreur lors de la connexion à la bdd: '.$e->getMessage()); }
    $this->db->query('SET NAMES UTF8');
  }

  public function query($query, $arguments=[]) {
    if(!$this->connected) return FALSE;
    $req = $this->db->prepare($query);
    return $req->execute($arguments) ? $req : FALSE;
  }

  public function get_forum_cache($forum_id, $page) {
    return $this->query(
      'SELECT * FROM forums WHERE forum_id=? AND page=?',
      [$forum_id, $page]
    )->fetch();
  }

  public function set_forum_cache($forum_id, $page, $vars) {
    return $this->query(
      'INSERT INTO forums (forum_id, page, vars, fetched_at) ' .
      'VALUES(:forum_id, :page, :vars, NOW()) ' .
      'ON DUPLICATE KEY UPDATE vars=:vars, fetched_at=NOW()',
      [
        ':forum_id' => $forum_id,
        ':page' => $page,
        ':vars' => $vars
      ]
    );
  }
}
