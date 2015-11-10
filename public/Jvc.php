<?php
require_once 'helpers.php';

/**
 * Représente la session sur JVC du client.
 * 
 * Tous les appels à JVC doivent être effectués avant la moindre
 * sortie (pour pouvoir mettre à jour le header Set-Cookie)
 * @package default
 */
class Jvc {
  public function __construct() {
    $this->err = 'Indéfinie';
    $this->cookie_pre = '_JVCCOK_';
    $this->tokens_pre = '_JVCTOK_';

    $this->cookie = [];
    foreach ($_COOKIE as $k => $v) {
      if (substr($k, 0, strlen($this->cookie_pre)) === $this->cookie_pre) {
        $this->cookie[substr($k, strlen($this->cookie_pre))] = $v;
      }
    }

    $this->tk = [];
    foreach ($_COOKIE as $k => $v) {
      if (substr($k, 0, strlen($this->tokens_pre)) === $this->tokens_pre) {
        $this->tk[substr($k, strlen($this->tokens_pre))] = $v;
      }
    }

    $this->tk_update = isset($_COOKIE['tk_update']) ? $_COOKIE['tk_update'] : 0;

    if (!isset($this->cookie['dlrowolleh']) || !$this->cookie['dlrowolleh']) {
      $this->cookie['dlrowolleh'] = null;
    }

  }

  /**
   * Récupère les détails sur la dernière erreur qui a eu lieu
   * @return string erreur
   */
  public function err() {
    return $this->err;
  }

  /**
   * Vérifie si le client est connecté sur JVC
   * @return boolean TRUE si le client est connecté, FALSE sinon
   */
  public function is_connected() {
    return isset($this->cookie['coniunctio']) && isset($_COOKIE['pseudo']);
  }

  /**
   * Déconnecte le client de JVC
   */
  public function disconnect() {
    foreach ($this->cookie as $k => $v) {
      removecookie($this->cookie_pre.$k);
    }
    removecookie('pseudo');
    removecookie('blacklist');
    removecookie('auth-uid');
    $this->cookie = [];

    foreach ($this->tk as $k => $v) {
      removecookie($this->tokens_pre.$k);
    }
    removecookie('tk_update');
    $this->tk = [];
    $this->last_update = 0;

    $this->cookie['dlrowolleh'] = null;
    $this->request('/profil/angivare?mode=page_perso');
  }

  public function connect($pseudo, $password, $captcha) {
    if (!$captcha) {
      return $this->_err('Le captcha n’a pas été rempli.');
    }

    $rep = $this->request('/login');

    $form = self::parse_form($rep['body']);
    $post_data = 'login_pseudo=' . urlencode($pseudo) .
                 '&login_password=' . urlencode($password) .
                 '&g-recaptcha-response=' . urlencode($captcha) .
                 '&' . http_build_query($form);
    $rep = $this->request('/login', $post_data);

    if ($this->cookie['coniunctio']) {
      _setcookie('pseudo', $pseudo);
      Auth::refresh_uid();
      header('Location: /1000021/39674315-appli-jvforum-topic-officiel');
      exit;
    }

    if (preg_match('#<div class="bloc-erreur">\s*(?P<error>.+)\s*</div>#Us', $rep['body'], $matches)) {
      return $this->_err($matches['error']);
    }

    return $this->_err('Indéfinie');
  }

  /**
   * Prépare un formulaire pour l'envoi d'un message
   * 
   * Le formulaire contient 'fs_signature' si un captcha est présent
   * @param string $url url du topic 
   * @return mixed FALSE si une erreur a eu lieu, le formulaire
   * sinon
   */
  public function message_post_req($url) {
    $form = self::parse_form($this->request($url)['body']);
    if (count($form)) {
      return $form;
    }
    return $this->_err('Impossible de préparer le formulaire');
  }

  /**
   * Finalise l'envoi d'un message
   * @param string $url url du topic 
   * @param string $msg message à envoyer
   * @param array $form  
   * @param string $ccode code de confirmation
   * @return boolean TRUE si le message est envoyé, FALSE sinon
   */
  public function message_post_finish($url, $msg, $form, $ccode = '', &$ret_location = null) {
    $post_data = http_build_query($form) .
      '&message_topic=' . urlencode($msg) .
      '&form_alias_rang=1' .
      '&fs_ccode=' . urlencode($ccode);

    $rep = $this->request($url, $post_data);

    if ($location = self::redirects($rep['header'])) {
      if ($ret_location !== null) {
        $ret_location = $location;
      }
      return true;
    }
    elseif (preg_match('#<div class="alert-row">(.+?)</div>#si', $rep['body'], $match)) {
      return $this->_err($match[1]);
    }
    else {
      return $this->_err('Erreur lors de l\'envoi du message');
    }
  }

  /**
   * Prépare un formulaire pour la création d'un topic
   * 
   * Le formulaire contient 'fs_signature' si un captcha est présent
   * @param string $url url du forum
   * @return mixed FALSE si une erreur a eu lieu, le formulaire sinon
   */
  public function topic_post_req($url) {
    $rep = $this->request($url)['body'];
    $form = self::parse_form($rep);

    if (count($form)) {
      return $form;
    }
    elseif (null !== strpos($rep, '<div class="alert-row"> Vous ne pouvez pas créer un nouveau sujet sur ce forum car il est fermé. </div>')) {
      return $this->_err('Forum fermé');
    }
    else {
      return $this->_err('Impossible de préparer le formulaire');
    }
  }

  /**
   * Finalise la création d'un topic
   * @param string $url 
   * @param string $title 
   * @param string $msg 
   * @param array $form 
   * @param string $ccode 
   * @return boolean TRUE si le topic est créé, FALSE sinon
   */
  public function topic_post_finish($url, $title, $msg, $form, $poll_question = '', $poll_answers = [], $ccode = '', &$ret_location = null) {
    $post_data = http_build_query($form) .
      '&titre_topic=' . urlencode($title) .
      '&message_topic=' . urlencode($msg) .
      '&fs_ccode=' . urlencode($ccode) .
      '&submit_sondage=' . ($poll_question ? '1' : '0') .
      '&question_sondage=' . urlencode($poll_question) .
      '&form_alias_rang=1';
    foreach ($poll_answers as $v) {
      $post_data .= '&reponse_sondage%5B%5D=' . urlencode($v);
    }

    $rep = $this->request($url, $post_data);

    if ($location = self::redirects($rep['header'])) {
      if ($ret_location !== null) {
        $ret_location = $location;
      }
      return true;
    }
    elseif (preg_match('#<div class="alert-row">(.+?)</div>#si', $rep['body'], $match)) {
      return $this->_err($match[1]);
    }
    else {
      return $this->_err('Erreur lors de la création du topic');
    }
  }

  /**
   * Rafraîchit les tokens ajax
   * @param string $body Le contenu d'un topic
   * @return boolean TRUE s'il n'y a pas eu d'erreur, FALSE sinon
   */
  public function tokens_refresh($body) {
    $this->tk = self::parse_ajax_tk($body, '.+?', true);
    if (!$this->tk) {
      return $this->_err('Indéfinie');
    }
    $this->tk_update = time();
    foreach ($this->tk as $k => $v) {
      _setcookie($this->tokens_pre.$k, $v);
    }
    _setcookie('tk_update', $this->tk_update);
    return true;
  }

  public function tokens() {
    return $this->tk;
  }

  public function tokens_last_update() {
    return $this->tk_update;
  }

  /**
   * Prépare un formulaire pour l'édition d'un message
   * 
   * Le formulaire contient 'fs_signature' si un captcha est présent
   * @param string $url 
   * @param int $id 
   * @return mixed FALSE s'il y a eu une erreur, le formulaire à renvoyer sinon
   */
  public function edit_req($id) {
    $tk = $this->ajax_array('liste_messages');
    $get_data = http_build_query($tk) .
      '&id_message=' . urlencode($id) .
      '&action=get';

    $rep = $this->request('/forums/ajax_edit_message.php?' . $get_data);
    $rep = json_decode($rep['body']);

    if ($rep->erreur) {
      return $this->_err($rep->erreur);
    }

    return array_merge(self::parse_form($rep->html), $tk);
  }

  /**
   * Finalise l'édition d'un message
   * @param int $id 
   * @param string $msg 
   * @param array $form 
   * @param string $ccode code de confirmation
   * @return boolean TRUE s'il y n'y a pas eu d'erreur, FALSE sinon
   */
  public function edit_finish($id, $msg, $form, $ccode = '') {
    $post_data = http_build_query($form) .
      '&id_message=' . urlencode($id) .
      '&message_topic=' . urlencode($msg) .
      '&action=post';

    if ($ccode) {
      $post_data .= '&fs_ccode=' . urlencode($ccode);
    }

    $rep = $this->request('/forums/ajax_edit_message.php', $post_data);
    $rep = json_decode($rep['body']);

    if ($rep->erreur) {
      return $this->_err($rep->erreur);
    }

    return true;
  }

  /**
   * Édite le titre d'un topic
   * @param int $id id du topic
   * @param string $title nouveau titre
   * @return boolean TRUE/FALSE
   */
  public function edit_title($id, $title) {
    $tk = $this->ajax_array('liste_messages');
    $post_data = http_build_query($tk) .
      '&id_topic=' . urlencode($id) .
      '&titre_topic=' . urlencode($title);

    $rep = $this->request('/forums/ajax_edit_title.php', $post_data);
    $rep = json_decode($rep['body']);

    if ($rep->erreur) {
      return $this->_err($rep->erreur);
    }
    else {
      return true;
    }
  }

  public function get_pseudo_id($pseudo) {
    $body = $this->request('/profil/' . strtolower($pseudo) . '?mode=infos')['body'];
    if (preg_match('#<span class="picto-attention" title="Signaler le profil de [^"]+" data-modal="afficher" data-cache="false" data-selector="/profil/gta\.php\?id=(?P<id>[0-9]+)&#', $body, $matches)) {
      return $matches['id'];
    }
    if (preg_match('#<div class="dropdown reglages-profil"><a href="/sso/infos_pseudo\.php?id=(?P<id>[0-9]+)"#', $body, $matches)) {
      return $matches['id'];
    }
    return 0;
  }

  public function blacklist_add($pseudo) {
    $id = $this->get_pseudo_id($pseudo);
    $tk = $this->ajax_array('preference_user');
    $get_data = 'id_alias_msg=' . urlencode($id) .
      '&action=add' . '&' . http_build_query($tk);
    $ret = json_decode($this->request('/forums/ajax_forum_blacklist.php?' . $get_data)['body']);
    return $ret->erreur ? $this->_err($ret->erreur) : true;
  }

  public function blacklist_remove($pseudo) {
    $id = $this->get_pseudo_id($pseudo);
    $get_data = 'id_alias_unblacklist=' . urlencode($id);
    $ret = json_decode($this->request('/sso/ajax_delete_blacklist.php?' . $get_data)['body']);
    return $ret->erreur ? $this->_err($ret->erreur) : true;
  }

  /**
   * Retourne la liste des utilisateurs ignorés
   * @return mixed Tableau contenant les utilisateurs ignorés, chaque
   * utilisateur est représenté par un tableau associatif contenant
   * une valeur 'id' et 'human'. FALSE si une erreur est survenue
   */
  public function blacklist_get() {
    $rep = $this->request('/sso/blacklist.php');

    $regex =  '#<li data-id-alias="(?P<id>[0-9]+)">.+' .
              '<span>(?P<pseudo>.+)</span>.+'  .
              '</li>#Usi';

    if (!preg_match_all($regex, $rep['body'], $matches, PREG_SET_ORDER)) {
      return [];
    }

    $ret = [];
    for ($i = 0; $i < count($matches); $i++) {
      $ret[] = strtolower($matches[$i]['pseudo']);
    }
    return $ret;
  }

  /**
   * Ajoute/enlève un forum/topic aux favoris
   * @param int $id 
   * @param string $type 'forum' ou 'topic'
   * @param string $action 'add' ou 'delete'
   * @return boolean TRUE/FALSE
   */
  public function favorites_update($id, $type, $action) {
    $tk = $this->ajax_array('preference_user');
    $id_forum = $type === 'forum' ? $id : '0';
    $id_topic = $type === 'topic' ? $id : '0';
    $get_data = http_build_query($tk) .
      '&id_forum=' . urlencode($id_forum) .
      '&id_topic=' . urlencode($id_topic) .
      '&action=' . urlencode($action) .
      '&type=' . urlencode($type);
    $rep = $this->request('/forums/ajax_forum_prefere.php?' . $get_data);
    return true;
  }

  /**
   * Retourne la liste des sujets & topics préférés
   * @return array Tableau associatif contenant les sujets et topics favoris
   */
  public function favorites_get() {
    $rep = $this->request('/forums.htm');

    $lim = strpos($rep['body'], '<ul id="liste-sujet-prefere"');

    $before = substr($rep['body'], 0, $lim);
    $after = substr($rep['body'], $lim);

    $regex =  '#<li class="move line-ellipsis" data-id="(?P<id>[0-9]+)">.+' .
              '<a href="//www.jeuxvideo.com/forums/(?P<mode>[0-9]+)-(?P<forum>[0-9]+)-(?P<topic>[0-9]+)-1-0-1-0-(?P<slug>.+)\.htm" class="lien-jv">[\r\n\s]*?(?P<titre>.+)[\r\n\s]*</a>.+' .
              '</li>#Usi';

    $forums = $topics = [];

    preg_match_all($regex, $before, $matches, PREG_SET_ORDER);
    for ($i = 0; $i < count($matches); $i++) {
      $forums[$matches[$i]['id']] = [
        'lien' => '/' . $matches[$i]['forum'] . '-' . $matches[$i]['slug'],
        'id' => $matches[$i]['forum'],
        'titre' => $matches[$i]['titre'],
      ];
    }

    preg_match_all($regex, $after, $matches, PREG_SET_ORDER);
    for ($i = 0; $i < count($matches); $i++) {
      $topics[$matches[$i]['id']] = [
        'lien' => '/' . $matches[$i]['forum'] . '/' . ($matches[$i]['mode'] == '1' ? '0' : '') . $matches[$i]['topic'] . '-' . $matches[$i]['slug'],
        'id' => ($matches[$i]['mode'] == '1' ? '0' : '') . $matches[$i]['topic'],
        'titre' => $matches[$i]['titre'],
      ];
    }

    return [
      'forums' => $forums,
      'topics' => $topics,
    ];
  }

  /**
   * Supprime un message
   * @param int $id 
   * @return boolean TRUE/FALSE
   */
  public function message_delete($id) {
    $tk = self::ajax_array('moderation_forum');
    $post_data = http_build_query($tk) .
      '&type=delete' .
      '&tab_message%5B%5D=' . urlencode($id);

    $rep = $this->request('/forums/modal_del_message.php', $post_data);

    //TODO: error handling? la page ne semble renvoyer aucune réponse cependant..
    return true;
  }

  /**
   * Retourne le header "location"
   * @param string $hdr 
   * @return mixed Le header "location" ou FALSE
   */
  public static function redirects($hdr) {
    $beg = stripos($hdr, "\nLocation:");
    if ($beg === false) {
      return false;
    }
    else {
      $beg += strlen("\nLocation:");
    }
    $end = strpos($hdr, "\n", $beg);
    return trim(substr($hdr, $beg, $end-$beg));
  }

  /**
   * Transforme le lien en lien jvf
   * @param string $link Lien jvc à transformer
   * @return mixed Le lien jvf correspondant ou FALSE
   */
  public static function jvf_link($link) {
    if (!preg_match('#/forums/(?P<topic_mode>.+)-(?P<forum>.+)-(?P<topic>.+)-(?P<page>.+)-0-1-0-(?P<slug>.+).htm#U', $link, $matches)) {
      return false;
    }
    if ($matches['topic_mode'] == '1') {
      $matches['topic'] = '0' . $matches['topic'];
    }
    $link = "/{$matches['forum']}";
    if ($matches['topic'] != 0) {
      $link .= "/{$matches['topic']}";
    }
    $link .= "-{$matches['slug']}";
    if ($matches['page'] > 1) {
      $link .= "/{$matches['page']}";
    }
    return $link;
  }

  public function get_pseudo() {
    $body = $this->request('/contact.php')['body'];
    preg_match('#<div class="nom-head-avatar">([^<]+)</div>#', $body, $matches);
    if (!$matches) {
      return false;
    }
    return $matches[1];
  }

  public function request($url, $connected_or_post_data = true, $retry_id = null, $retry_count = 0) {
    $db = new Db();

    $result = $db->get_max_concurrent_request();
    if ($result && $result['started_at'] > microtime(true) - 10) { // Some requests might never be updated as done, so we ignore "concurrent" requests started over 10 seconds ago.
      if (!$retry_id) {
        $retry_id = microtime(true);
      }
      $db->log_request_retry($retry_id, $retry_count);
      usleep(100 * 1000);
      return $this->request($url, $connected_or_post_data, $retry_id, $retry_count + 1);
    }

    $connected = !!$connected_or_post_data;
    $post_data = is_string($connected_or_post_data) ? $connected_or_post_data : false;

    $coniunctio = $dlrowolleh = null;
    if ($connected) {
      if (!$this->is_connected()) {
        /*
         * When logging in, we’re doing a POST and $connected is true,
         * though we aren’t really connected. We fix that here.
         * 
         * `dlrowolleh` is needed when logging in.
         */
        $connected = false;
      }
      else {
        $coniunctio = $this->cookie['coniunctio'];
      }
      $dlrowolleh = $this->cookie['dlrowolleh'];
    }
    elseif ($this->is_connected()) {
      $coniunctio = 'fake'; // A `coniunctio` cookie is needed to bypass the cache.
    }

    if ($url[0] == '/') {
      $url = 'http://www.jeuxvideo.com' . $url;
    }

    $log_id = $db->log_request_start($url, !!$post_data, $connected);
    $start = microtime(true);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    if ($post_data) {
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, REQUEST_TIMEOUT);

    curl_setopt($ch, CURLOPT_COOKIE, $this->cookie_string(['coniunctio' => $coniunctio, 'dlrowolleh' => $dlrowolleh]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Forwarded-For: ' . $_SERVER['REMOTE_ADDR']]);

    $rep = curl_exec($ch);
    $errno = curl_errno($ch);

    $timing = (int)((microtime(true) - $start) * 1000);
    $db->log_request_update($log_id, $timing, $errno);

    if (!$rep) {
      if ($errno === CURLE_OPERATION_TIMEOUTED) {
        $this->fatal_err('Timeout.', 'La page sur jeuxvideo.com mettait plus de deux secondes à charger, elle a été arrêtée.', 504);
      }
      else {
        $this->fatal_err('Problème réseau.', 'JVForum n’a pas réussi à charger la page depuis jeuxvideo.com.', 502);
      }
    }
    $ret = [
      'header' => substr($rep, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE)),
      'body' => substr($rep, curl_getinfo($ch, CURLINFO_HEADER_SIZE)),
    ];
    curl_close($ch);

    $this->refresh_cookie($ret['header']);

    return $ret;
  }

  private function _err($err) {
    $this->err = $err;
    return false;
  }

  private function fatal_err($title, $message, $http_status_code = 200) {
    http_response_code($http_status_code);
    $body = <<<HTML
      <div class="sheet">
        <div class="timeout">
          <h3>{$title}</h3>

          <p>{$message}</p>

          <p><a href="{$_SERVER['REQUEST_URI']}" data-no-instant>Réessayer</a></p>
        </div>
      </div>
HTML;
    $jvc = new Jvc();
    $forum = $topic = $topicNew = $slug = $page = null;
    $token = [];
    $title = 'Erreur';
    include 'views/layout.php';
    exit;
  }

  private function refresh_cookie($hdr) {
    preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $hdr, $match);
    $str = '';
    foreach ($match[1] as $v) {
      $str .= $v . '; ';
    }
    $str = substr($str, 0, -2);
    $cookies = explode('; ', $str);
    foreach ($cookies as $c) {
      $pair = explode('=', $c);
      if (!isset($pair[1])) {
        continue;
      }
      $this->cookie[$pair[0]] = $pair[1];
    }

    foreach ($this->cookie as $k => $v) {
      _setcookie($this->cookie_pre.$k, $v);
    }
  }

  private function cookie_string($add) {
    $ret = '';
    foreach ($this->cookie as $k => $v) {
      if (array_key_exists($k, $add)) {
        if ($add[$k] === null) {
          continue;
        }
        $ret .= $k . '=' . $add[$k] . '; ';
        unset($add[$k]);
        continue;
      }
      $ret .= $k . '=' . $v . '; ';
    }
    foreach ($add as $k => $v) {
      if ($v !== null) {
        $ret .= $k . '=' . $v . '; ';
      }
    }
    return substr($ret, 0, -2);
  }

  private function ajax_array($type) {
    if ((!isset($this->tk["ajax_timestamp_{$type}"]) || !isset($this->tk["ajax_hash_{$type}"]))
    || (time() - $this->tokens_last_update() >= 3600 / 2)) {
      $rep = $this->request('/forums/42-1000021-38675199-1-0-1-0-a-lire-avant-de-creer-un-topic.htm');
      self::tokens_refresh($rep['body']);
    }
    return [
      'ajax_timestamp' => $this->tk["ajax_timestamp_{$type}"],
      'ajax_hash' => $this->tk["ajax_hash_{$type}"],
    ];
  }

  private static function parse_form($bdy) {
    $regex = '<input type="hidden" name="fs_(.+?)" value="(.+?)"/>';
    preg_match_all($regex, $bdy, $matches);
    $ret = [];
    for ($i = 0; $i < count($matches[0]); $i++) {
      $ret['fs_' . $matches[1][$i]] = $matches[2][$i];
    }
    return $ret;
  }

  private static function parse_ajax_tk($bdy, $type, $leave_tk_type = false) {
    $regex = '<input type="hidden" name="(.+?)_('.$type.')" .+? value="(.+?)" />';
    preg_match_all($regex, $bdy, $matches);
    $ret = [];
    for ($i = 0; $i < count($matches[0]); $i++) {
      if ($leave_tk_type) {
        $ret[$matches[1][$i] . '_' . $matches[2][$i]] = $matches[3][$i];
      }
      else {
        $ret[$matches[1][$i]] = $matches[3][$i];
      }
    }
    return $ret;
  }
}
