<?php
class Db {
  const DEBUG = FALSE;

  private $db;
  private $connected;

  public function __construct() {
    $this->connected = FALSE;
    try {
      $this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
      $this->connected = TRUE;
    } catch(Exception $e) {  }
    $this->db->query('SET NAMES UTF8');
  }

  public function query($query, $arguments=[]) {
    if(!$this->connected) return FALSE;
    $req = $this->db->prepare($query);
    if(self::DEBUG) {
      $ret = $req->execute($arguments);
      var_dump($req->errorInfo());
      return $ret ? $req : FALSE;
    } else
      return $req->execute($arguments) ? $req : FALSE;
  }

  public function search_forum($str) {
    $keywords = preg_split('[-\s\',"]', $str);
    foreach($keywords as $k => $v)
      if(strlen($v) <= 3) unset($keywords[$k]);
      else $keywords[$k] = '%' . $v . '%';
    $sql = str_repeat('human LIKE ? AND ', count($keywords)-1);
    return $this->query(
      'SELECT * FROM forums WHERE ' . $sql . 'human LIKE ? ',
      $keywords
    )->fetchAll(PDO::FETCH_ASSOC);
  }

  public function add_forum($forum) {
    return $this->query(
      'INSERT INTO forums (forum_id, slug, human) VALUES (:id, :slug, :human) ' .
      'ON DUPLICATE KEY UPDATE slug=:slug, human=:human',
      [
        ':id' => $forum['id'],
        ':slug' => $forum['slug'],
        ':human' => $forum['human']
      ]
    );
  }

  public function delete_forum($id) {
    return $this->query(
      'DELETE FROM forums WHERE forum_id=?',
      [ $id ]
    );
  }

  public function get_forum_cache($forum_id, $page) {
    return $this->query(
      'SELECT * FROM forums_cache WHERE forum_id=? AND page=?',
      [$forum_id, $page]
    )->fetch();
  }

  public function set_forum_cache($forum_id, $page, $vars) {
    return $this->query(
      'INSERT INTO forums_cache (forum_id, page, vars, fetched_at) ' .
      'VALUES(:forum_id, :page, :vars, :time) ' .
      'ON DUPLICATE KEY UPDATE vars=:vars, fetched_at=:time',
      [
        ':forum_id' => $forum_id,
        ':page' => $page,
        ':vars' => $vars,
        ':time' => microtime(TRUE)
      ]
    );
  }

  public function clean_forum_cache() {
    return $this->query(
      'DELETE FROM forums_cache WHERE fetched_at < (? - 60*5)',
      [ microtime(TRUE) ]
    );
  }

  public function get_topic_cache($topic_id, $page, $topic_mode, $forum_id) {
    return $this->query(
      'SELECT * FROM topics_cache WHERE topic_id=? AND page=? AND topic_mode=? AND forum_id=?',
      [$topic_id, $page, $topic_mode, $forum_id]
    )->fetch();
  }

  public function set_topic_cache($topic_id, $page, $topic_mode, $forum_id, $vars) {
    return $this->query(
      'INSERT INTO topics_cache (topic_id, page, topic_mode, forum_id, vars, fetched_at) ' .
      'VALUES(:topic_id, :page, :topic_mode, :forum_id, :vars, :time) ' .
      'ON DUPLICATE KEY UPDATE vars=:vars, fetched_at=:time',
      [
        ':topic_id' => $topic_id,
        ':page' => $page,
        ':topic_mode' => $topic_mode,
        ':forum_id' => $forum_id,
        ':vars' => $vars,
        ':time' => microtime(TRUE)
      ]
    );
  }

  public function clean_topic_cache() {
    return $this->query(
      'DELETE FROM topics_cache WHERE fetched_at < (? - 60*5)',
      [ microtime(TRUE) ]
    );
  }

  public function set_token($hash) {
    return $this->query(
      'INSERT INTO tokens (generated, token) VALUES (NOW(), ?)',
      [$hash]
    )->fetch();
  }

  public function get_token($hash) {
    return $this->query(
      'SELECT * FROM tokens WHERE token=?',
      [$hash]
    )->fetch();
  }

  public function clean_tokens() {
    return $this->query(
      'DELETE FROM tokens WHERE generated < NOW5() - INTERVAL 1 HOUR',
      []
    );
  }

  public function log_message($forum_id, $topic_mode = null, $topic_id = null) {
    $this->query(
      'INSERT INTO logs_messages2(pseudo, is_topic, forum_id, topic_mode, topic_id, ip) VALUES(?, ?, ?, ?, ?, ?)',
      [$_COOKIE['pseudo'], is_null($topic_mode), $forum_id, $topic_mode, $topic_id, $_SERVER['REMOTE_ADDR']]
    );
    return $this->db->lastInsertId();
  }
  
  public function log_message_update($id, $message_id, $topic_mode = null, $topic_id = null) {
    if (is_null($message_id)) { // Topic
      $this->query(
        'UPDATE logs_messages2 SET topic_mode = ?, topic_id = ? WHERE id = ?',
        [$topic_mode, $topic_id, $id]
      );
    }
    else { // Message
      $this->query(
        'UPDATE logs_messages2 SET message_id = ? WHERE id = ?',
        [$message_id, $id]
      );
    }
  }
}
