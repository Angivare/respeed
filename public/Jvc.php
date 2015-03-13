<?php

/**
 * Représente la session sur JVC du client.
 * 
 * Tous les appels à JVC doivent être effectués avant la moindre
 * sortie (pour pouvoir mettre à jour le header Set-Cookie)
 * @package default
 */
class Jvc {
  const CK_PREFIX = '_JVC_';

  /**
   * Retourne la session sur JVC du client depuis les cookies
   */
  public function __construct() {
    $this->cookie = array();
    foreach($_COOKIE as $k => $v)
      if(substr($k, 0, strlen(self::CK_PREFIX)) === self::CK_PREFIX)
        $this->cookie[substr($k, strlen(self::CK_PREFIX))] = $v;

    if(!isset($this->cookie['dlrowolleh']))
      $this->init();

    $this->err = NULL;
  }

  public function __destruct() {
  }

  /**
   * Requête fantôme pour obtenir un cookie de session
   * TODO: la rendre asynchrone?
   */
  private function init() {
    $this->get('http://www.jeuxvideo.com/profil/angivare?mode=page_perso');
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
    return isset($this->cookie['coniuncto']);
  }

  /**
   * Déconnecte le client de JVC
   */
  public function disconnect() {
    foreach($this->cookie as $k => $v)
      setcookie(self::CK_PREFIX.$k, '', time()-1, '', '', FALSE, TRUE);
    $this->cookie = array();
  }

  /**
   * Effectue la première étape de la connexion
   * @param string $nick 
   * @param string $pass 
   * @return mixed FALSE si la requête a échoué, formulaire à réutiliser
   * dans connect_finish() sinon
   */
  public function connect_request($nick, $pass) {
    $url = 'http://www.jeuxvideo.com/login';

    $rep = $this->get($url);

    $form = self::parse_form($rep['body']);
    $post_data = 'login_pseudo=' . urlencode($nick) .
                 '&login_password=' . urlencode($pass) .
                 '&' . http_build_query($form);

    $rep = $this->post($url, $post_data);
    return self::parse_form($rep['body']);
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

    //TODO: vérifier la réponse
    return TRUE;
  }

  /**
   * Prépare un formulaire pour l'envoi d'un message
   * @param string $url url du topic 
   * @return mixed FALSE si une erreur a eu lieu, le formulaire
   * sinon
   */
  public function post_msg_req($url) {
    $rep = $this->get($url);
    $form = self::parse_form($rep['body']);
    return $form;
  }

  /**
   * Finalise l'envoi d'un message
   * @param string $url url du topic 
   * @param string $msg message à envoyer
   * @param array $form  
   * @return boolean TRUE si le message est envoyé, FALSE sinon
   */
  public function post_msg_finish($url, $msg, $form) {
    $post_data = http_build_query($form) .
      '&message_topic=' . urlencode($msg) .
      '&form_alias_rang=1' .
      '&ccode=';

    $rep = $this->post($url, $post_data);

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
   * @param string $bdy page où le post apparaît
   * @return boolean TRUE si le pseudo est ajouté, FALSE sinon
   */
  public function blacklist_add($id, $bdy) {
    $tk = self::parse_ajax_tk($bdy, 'preference_user');
    $get_data = 'id_alias_msg=' . urlencode($id) .
      '&action=add' . '&' . http_build_query($tk);
    $ret = json_decode(self::get('http://www.jeuxvideo.com/ajax_forum_blacklist.php', $get_data));
    return count($ret->erreur) ? FALSE : TRUE;
  }

  /**
   * Retourne la citation d'un texte
   * @param int $id id du post à citer
   * @param string $bdy page où le post apparaît
   * @return mixed FALSE si la citation a échoué, la citation sinon
   */
  public function quote($id, $bdy) {
    $tk = self::parse_ajax_tk($bdy, 'liste_messages');
    $post_data = 'id_message=' . urlencode($id) .
      '&' . http_build_query($tk);
    $ret = json_decode(self::post('http://www.jeuxvideo.com/forums/ajax_citation.php',
      $post_data));
    return count($ret->erreur) ? FALSE : $ret->txt;
  }

  /**
   * Retourne la liste des sujets & topics préférés
   * @return array Tableau associatif contenant les sujets et topics favoris
   */
  public function favorites() {
    $rep = $this->get('http://www.jeuxvideo.com/forums.htm');

    $lim = strpos($rep['body'], '<ul id="liste-sujet-prefere"');

    $before = substr($rep['body'], 0, $lim);
    $after = substr($rep['body'], $lim);

    $regex =  '/<li class="move line-ellipsis" data-id="(?P<id>[0-9]+)">.+' .
              '<a .+>[\r\n\s]*?(?P<human>.+)[\r\n\s]*<\/a>.+' .
              '<\/li>/Usi';

    preg_match_all($regex, $before, $forums);
    preg_match_all($regex, $after, $topics);

    $t_forums = [];
    for($i = 0; $i < count($forums[0]); $i++)
      $t_forums[] = ['id' => $forums['id'][$i], 'human' => $forums['human'][$i]];
    $t_topics = [];
    for($i = 0; $i < count($topics[0]); $i++)
      $t_topics[] = ['id' => $topics['id'][$i], 'human' => $topics['human'][$i]];

    return [ 'forums' => $t_forums, 'topics' => $t_topics ];
  }

  /**
   * Prépare l'édition d'un message
   * @param string $url 
   * @param int $id 
   * @return mixed FALSE s'il y a eu une erreur, le formulaire à renvoyer sinon
   */
  public function edit_request($url, $id) {
    $rep = $this->get($url);

    $tk = self::parse_ajax_tk($rep['body'], 'liste_messages');

    $get_data = http_build_query($tk) .
      '&id_message=' . urlencode($id) .
      '&action=get';

    $rep = $this->get('http://www.jeuxvideo.com/forums/ajax_edit_message.php', $get_data);
    $rep = json_decode($rep['body']);

    if(!empty($rep->erreur))
      return $this->_err($rep->erreur);

    return array_merge(
      self::parse_form($rep->html),
      $tk
    );
  }

  /**
   * Finalise l'édition d'un message
   * @param string $url 
   * @param int $id 
   * @param string $msg 
   * @param array $form 
   * @return boolean TRUE s'il y n'y a pas eu d'erreur, FALSE sinon
   */
  public function edit_finish($url, $id, $msg, $form) {
    $post_data = http_build_query($form) .
      '&id_message=' . urlencode($id) .
      '&message_topic=' . urlencode($msg) .
      '&action=post';

    $rep = $this->post('http://www.jeuxvideo.com/forums/ajax_edit_message.php', $post_data);
    $rep = json_decode($rep['body']);

    if(!empty($rep->erreur))
      return $this->_err($rep->erreur);

    return TRUE;
  }

  /**
   * Effectue une requête POST
   * @param string $url 
   * @param mixed $data champ à envoyer, urlencodé ou un tableau associatif 
   * @param boolean $connected TRUE (par défaut) si la requête doit être envoyée
   * en tant qu'utilisateur connecté, FALSE sinon
   * @return array réponse du serveur, séparé en 'header' et 'body'
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
   * @return array réponse du serveur, séparé en 'header' et 'body'
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
      curl_setopt($ch, CURLOPT_COOKIE, $this->cookie_string());
    $rep = curl_exec($ch);
    $ret = array(
      'header' => substr($rep, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE)),
      'body' => substr($rep, curl_getinfo($ch, CURLINFO_HEADER_SIZE))
    );
    curl_close($ch);
    $this->refresh_cookie($ret['header']);
    return $ret;
  }

  private function _err($err) {
    $this->err = $err;
    return FALSE;
  }

  private function refresh_cookie($hdr) {
    preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $hdr, $match);
    $str = '';
    foreach($match[1] as $v)
      $str .= $v . '; ';
    $str = substr($str, 0, -2);
    $cookies = explode('; ', $str);
    foreach($cookies as $c) {
      $pair = explode('=', $c);
      if(!isset($pair[1])) continue;
      $this->cookie[$pair[0]] = $pair[1];
    }

    foreach($this->cookie as $k => $v)
      setcookie(self::CK_PREFIX.$k, $v, time()+3600*24, '/', 'respeed.dev', FALSE, TRUE);
  }

  private function cookie_string() {
    $ret = '';
    foreach($this->cookie as $k => $v)
      $ret .= $k . '=' . $v . '; ';
    return substr($ret, 0, -2);
  }

  private static function parse_form($bdy) {
    $regex = '<input type="hidden" name="fs_(.+?)" value="(.+?)"/>';
    preg_match_all($regex, $bdy, $matches);
    $ret = array();
    for($i = 0; $i < count($matches[0]); $i++)
      $ret['fs_'.$matches[1][$i]] = $matches[2][$i];
    return $ret;
  }

  private static function parse_ajax_tk($bdy, $type) {
    $regex = '<input type="hidden" name="(.+?)_'.$type.'" .+? value="(.+?)" />';
    preg_match_all($regex, $bdy, $matches);
    $ret = array();
    for($i = 0; $i < count($matches[0]); $i++)
      $ret[$matches[1][$i]] = $matches[2][$i];
    return $ret;
  }
}
