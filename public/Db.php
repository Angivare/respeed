<?php
class Db {
  const DEBUG = false;

  private $db;
  private $connected;

  public function __construct() {
    $this->connected = false;
    try {
      $this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
      $this->connected = true;
    }
    catch (Exception $e) {}
    $this->db->query('SET NAMES UTF8');
  }

  public function query($query, $arguments=[]) {
    if (!$this->connected) {
      return false;
    }
    $req = $this->db->prepare($query);
    if (self::DEBUG) {
      $ret = $req->execute($arguments);
      var_dump($req->errorInfo());
      return $ret ? $req : false;
    }
    else {
      return $req->execute($arguments) ? $req : false;
    }
  }

  public function search_forum($str) {
    $str = str_replace(['_', '%'], ['\\_', '\\%'], $str);
    $keywords = explode(' ', $str);
    foreach ($keywords as $k => $v) {
      $keywords[$k] = '%' . $v . '%';
    }
    $sql = str_repeat('human LIKE ? AND ', count($keywords) - 1);
    return $this->query(
      'SELECT * FROM forums WHERE ' . $sql . 'human LIKE ? ORDER BY connected DESC, forum_id DESC',
      $keywords
    )->fetchAll(PDO::FETCH_ASSOC);
  }

  public function add_forum($forum) {
    return $this->query(
      'INSERT INTO forums (forum_id, slug, human, connected, parent_human) VALUES (:id, :slug, :human, :connected, :parent_human) ' .
      'ON DUPLICATE KEY UPDATE slug=:slug, human=:human, connected=:connected, parent_human=:parent_human',
      [
        ':id' => $forum['id'],
        ':slug' => $forum['slug'],
        ':human' => $forum['human'],
        ':connected' => $forum['connected'],
        ':parent_human' => $forum['parent_human'],
      ]
    );
  }

  public function delete_forum($id) {
    return $this->query(
      'DELETE FROM forums WHERE forum_id = ?',
      [$id]
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
        ':time' => microtime(true),
      ]
    );
  }

  public function clean_forum_cache() {
    return $this->query(
      'DELETE FROM forums_cache WHERE fetched_at < (? - 60*5)',
      [microtime(true)]
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
        ':time' => microtime(true)
      ]
    );
  }

  public function clean_topic_cache() {
    return $this->query(
      'DELETE FROM topics_cache WHERE fetched_at < (? - 60*5)',
      [microtime(true)]
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
      'SELECT * FROM tokens WHERE token = ?',
      [$hash]
    )->fetch();
  }

  public function clean_tokens() {
    return $this->query(
      'DELETE FROM tokens WHERE generated < NOW() - INTERVAL 1 HOUR',
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

  public function log_concurrent_request() {
    $this->query(
      'INSERT INTO current_requests(started_at) VALUES(?)',
      [microtime(true)]
    );
    return $this->db->lastInsertId();
  }

  public function remove_concurrent_request($id) {
    $this->query(
      'DELETE FROM current_requests WHERE id = ?',
      [$id]
    );
    return $this->db->lastInsertId();
  }

  public function is_another_concurrent_request_allowed() {
    $alleged_concurrent_requests = $this->query('SELECT COUNT(*) as count FROM current_requests')->fetch()['count'];
    if ($alleged_concurrent_requests >= MAX_CONCURRENT_REQUESTS) {
      // Some requests might never be deleted, so we ignore alleged concurrent requests started over 10 seconds ago.
      $first_concurrent_request = $this->query('SELECT started_at FROM current_requests ORDER BY id DESC LIMIT ' . (MAX_CONCURRENT_REQUESTS - 1) . ', 1')->fetch();
      if ($first_concurrent_request['started_at'] > microtime(true) - 10) {
        return false;
      }
    }
    return true;
  }

  public function log_request_retry($id, $count) {
    $this->query(
      'INSERT INTO logs_requests_retries VALUES(?, ?)',
      [$id, $count]
    );
  }

  private function short_url_for_log_request($url) {
    if (strpos($url, 'http://www.jeuxvideo.com') === 0) {
      $url = substr($url, strlen('http://www.jeuxvideo.com'));
    }
    $query_pos = strpos($url, '?');
    if ($query_pos) {
      $url = substr($url, 0, $query_pos + 1);
    }
    return $url;
  }

  public function log_request_start($url, $is_post, $is_connected) {
    $url = $this->short_url_for_log_request($url);
    $this->query(
      'INSERT INTO logs_requests4(started_at, url, is_post, is_connected, errno, ip) VALUES(?, ?, ?, ?, ?, ?)',
      [microtime(true), $url, $is_post, $is_connected, -1, $_SERVER['REMOTE_ADDR']]
    );
    return $this->db->lastInsertId();
  }

  public function log_request_update($id, $timing, $errno) {
    $this->query(
      'UPDATE logs_requests4 SET timing = ?, errno = ? WHERE id = ?',
      [$timing, $errno, $id]
    );
  }

  public function get_blacklist($person) {
    return $this->query(
      'SELECT blacklist, updated_at > NOW() - INTERVAL 10 MINUTE AS is_fresh FROM blacklists WHERE person = ?',
      [$person]
    )->fetch();
  }

  public function set_blacklist($person, $blacklist) {
    $blacklist = implode(',', $blacklist);
    return $this->query(
      'INSERT INTO blacklists(person, blacklist) VALUES(?, ?)',
      [$person, $blacklist]
    );
  }

  public function update_blacklist($person, $blacklist) {
    $blacklist = implode(',', $blacklist);
    return $this->query(
      'UPDATE blacklists SET blacklist = ?, updated_at = CURRENT_TIMESTAMP WHERE person = ?',
      [$blacklist, $person]
    );
  }

  public function get_user_id($pseudo) {
    $fetched = $this->query('SELECT id FROM users WHERE pseudo = ?', [$pseudo])->fetch();
    if (!$fetched) {
      return false;
    }
    return (int)$fetched['id'];
  }

  public function create_user_id($pseudo) {
    $this->query('INSERT INTO users(pseudo) VALUES(?)', [$pseudo]);
    return $this->db->lastInsertId();
  }

  public function get_favorites($user_id) {
    $fetched = $this->query('SELECT forums, topics, updated_at > NOW() - INTERVAL 10 MINUTE as is_fresh FROM favorites WHERE user_id = ?', [$user_id])->fetch();
    if (!$fetched) {
      return false;
    }
    return [
      'forums' => json_decode($fetched['forums']),
      'topics' => json_decode($fetched['topics']),
      'is_fresh' => (bool)$fetched['is_fresh'],
    ];
  }

  public function add_favorites($user_id, $forums, $topics) {
    return $this->query(
      'INSERT INTO favorites(user_id, forums, topics) VALUES(?, ?, ?)',
      [$user_id, json_encode($forums), json_encode($topics)]
    );
  }

  public function update_favorites($user_id, $forums, $topics) {
    return $this->query(
      'UPDATE favorites SET forums = ?, topics = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?',
      [json_encode($forums), json_encode($topics), $user_id]
    );
  }
}
