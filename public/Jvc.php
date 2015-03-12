<?php

/**
 * Représente la session sur JVC du client.
 * @package default
 */
class Jvc {
  const CK_PREFIX = '_JVC_';

  /**
   * Retourne la session sur JVC du client depuis les cookies
   */
  public function __construct() {
    $this->cookie = array();
    var_dump($_COOKIE);
    foreach($_COOKIE as $k => $v)
      if(substr($k, 0, strlen(self::CK_PREFIX)) === self::CK_PREFIX)
        $this->cookie[substr($k, strlen(self::CK_PREFIX))] = $v;
  }

  public function __destruct() {
  }

  /**
   * A utiliser avant une requête de connexion pour avoir
   * un cookie de session
   */
  public function connect_init() {
    if(!count($this->cookie))
      $this->get('http://www.jeuxvideo.com/login');
  }

  /**
   * Effectue la première étape de la connexion
   * @param string $nick 
   * @param string $pass 
   * @return array formulaire à réutiliser dans connect_finish()
   */
  public function connect_request($nick, $pass) {
    $url = 'http://www.jeuxvideo.com/login';

    $rep = $this->get($url);

    $form = self::parse_form($rep);
    $post_data = 'login_pseudo=' . urlencode($nick) .
                 '&login_password=' . urlencode($pass) .
                 '&' . http_build_query($form);

    $rep = $this->post($url, $post_data);
    return self::parse_form($rep);
  }

  /**
   * Finalise la connexion
   * @param string $nick 
   * @param string $pass 
   * @param array $form 
   * @param int $ccode 
   * @return boolean TRUE si la connexion a fonctionné, FALSE sinon
   */
  public function connect_finish($nick, $pass, $form, $ccode = '') {
    $url = 'http://www.jeuxvideo.com/login';

    $post_data = 'login_pseudo=' . urlencode($nick) .
                 '&login_password=' . urlencode($pass) .
                 '&' . http_build_query($form) .
                 '&fs_ccode=' . urlencode($ccode);

    $rep = $this->post($url, $post_data);

    echo $rep;
    var_dump($this->cookie);

    //TODO: vérifier la réponse
  }

  /**
   * Envoie un message
   * @param string $url url du topic 
   * @param string $msg message à envoyer 
   * @return mixed TRUE si le message est envoyé, FALSE s'il y a
   * eu une erreur, un tableau représentant le formulaire si un code
   * de confirmation est demandé.
   */
  public function post_msg($url, $msg) {
    //faire cette étape avant le post?
    $form = self::parse_form($this->get($url));
    sleep(1);

    if(isset($form['fs_signature'])) {
      return $form;
    }

    $post_data = http_build_query($form) .
      '&message_topic=' . urlencode($msg) .
      '&form_alias_rang=1' .
      '&ccode=';

    $rep = $this->post($url, $post_data);
    echo $rep;

    //TODO: vérifier la réponse
    return TRUE;
  }

  /**
   * Finalise l'envoi du message dans le cas d'un code de confirmation
   * @param array $form 
   * @param string $msg 
   * @param int $ccode 
   * @return boolean TRUE si le message est envoyé, FALSE sinon
   */
  public function post_msg_captcha($form, $msg, $ccode) {
    $post_data = http_build_query($form) .
      '&message_topic=' . urlencode($msg) .
      '&form_alias_rang=1' .
      '&ccode=' . urlencode($ccode);

    $rep = $this->post($url, $post_data);

    //TODO: vérifier la réponse
    return TRUE;
  }

  /**
   * Retourne la boîte de réception
   * @param int $folder # du dossier
   * @param int $page # de la page
   * @return string la page retournée
   */
  public function get_mailbox($folder = 0, $page = 1) {
    return $this->get('http://www.jeuxvideo.com/messages-prives/boite-reception.php',
      "folder=$folder&page=$page");
  }  

  /**
   * Retourne la page d'un message privé
   * @param int $folder # du dossier 
   * @param int $id # du mp
   * @param int $offset
   * @return string la page retournée
   */
  public function get_private_message($folder = 0, $id = 0, $offset = 1)  {
    return $this->get('http://www.jeuxvideo.com/messages-prives/message.php',
      "id=$id&folder=$folder&offset=$offset");
  }

  /**
   * Ajoute un pseudo à la blacklist
   * @param int $id id d'un post appartenant à la personne
   * @param string $rep page où le post apparaît
   * @return boolean TRUE si le pseudo est ajouté, FALSE sinon
   */
  public function blacklist_add($id, $rep) {
    $tk = self::parse_ajax_tk($rep, "preference_user");
    $get_data = 'id_alias_msg=' . urlencode($id) .
      '&action=add' . '&' . http_build_query($tk);
    $ret = json_decode(self::get('http://www.jeuxvideo.com/ajax_forum_blacklist.php', $get_data));
    return count($ret->erreur) ? FALSE : TRUE;
  }

  /**
   * Retourne la citation d'un texte
   * @param int $id id du post à citer
   * @param string $rep page où le post apparaît
   * @return mixed FALSE si la citation a échoué, la citation sinon
   */
  public function quote($id, $rep) {
    $tk = self::parse_ajax_tk($rep, 'liste_messages');
    $post_data = 'id_message=' . urlencode($id) .
      '&' . http_build_query($tk);
    $ret = json_decode(self::post('http://www.jeuxvideo.com/forums/ajax_citation.php',
      $post_data));
    return count($ret->erreur) ? FALSE : $ret->txt;
  }

  /**
   * Effectue une requête POST
   * @param string $url 
   * @param mixed $data champ à envoyer, urlencodé ou un tableau associatif 
   * @param boolean $connected TRUE (par défaut) si la requête doit être envoyée
   * en tant qu'utilisateur connecté, FALSE sinon
   * @return string réponse du serveur
   */
  public function post($url, $data, $connected = TRUE) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    return $this->finish_req($ch, $url, $connected);
  }

  /**
   * Effectue une requête GET
   * @param string $url 
   * @param string $query paramètres à envoyer, urlencodé 
   * @param boolean $connected TRUE (par défaut) si la requête doit être envoyée
   * en tant qu'utilisateur connecté, FALSE sinon
   * @return string réponse du serveur
   */
  public function get($url, $query = '', $connected = TRUE) {
    $query = $query ? "?$query" : '';
    return $this->finish_req(curl_init(), $url . $query, $connected);
  }

  private function finish_req($ch, $url, $connected = TRUE) {
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    if(count($this->cookie) && $connected !== FALSE)
      curl_setopt($ch, CURLOPT_COOKIE, $this->cookie());
    echo 'cookies:'; var_dump($this->cookie);
    $ret = curl_exec($ch);
    $this->cookie = self::get_cookie($ret);
    $this->set_cookie();
    $ret = substr($ret, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
    curl_close($ch);
    return $ret;
  }

  private static function get_cookie($rep) {
    preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $rep, $match);
    $str = '';
    foreach($match[1] as $v)
      $str .= $v . '; ';
    $str = substr($str, 0, -2);
    $cookies = explode('; ', $str);
    $ret = array();
    foreach($cookies as $c) {
      $pair = explode('=', $c);
      if(!isset($pair[1])) continue;
      $ret[$pair[0]] = $pair[1];
    }
    return $ret;
  }

  private function set_cookie() {
    foreach($this->cookie as $k => $v)
      setcookie('_JVC_' . $k, $v, 0, '', '', FALSE, TRUE);
  }

  private function cookie() {
    $ret = '';
    foreach($this->cookie as $k => $v)
      $ret .= $k . '=' . $v . '; ';
    return substr($ret, 0, -2);
  }

  private static function parse_form($rep) {
    $regex = '<input type="hidden" name="fs_(.+?)" value="(.+?)"/>';
    preg_match_all($regex, $rep, $matches);
    $ret = array();
    for($i = 0; $i < count($matches[0]); $i++)
      $ret['fs_'.$matches[1][$i]] = $matches[2][$i];
    return $ret;
  }

  private static function parse_ajax_tk($rep, $type) {
    $regex = '<input type="hidden" name="(.+?)_'.$type.'" .+? value="(.+?)" />';
    preg_match_all($regex, $rep, $matches);
    $ret = array();
    for($i = 0; $i < count($matches[0]); $i++)
      $ret[$matches[1][$i]] = $matches[2][$i];
    return $ret;
  }
}
