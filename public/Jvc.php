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
      $this->get('http://www.jeuxvideo.com/profil/angivare?mode=page_perso');

    $this->err = 'Indéfinie';
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
    return isset($this->cookie['coniunctio']);
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
  public function connect_req($nick, $pass) {
    $url = 'http://www.jeuxvideo.com/login';

    $rep = $this->get($url);

    $form = self::parse_form($rep['body']);
    $post_data = 'login_pseudo=' . urlencode($nick) .
                 '&login_password=' . urlencode($pass) .
                 '&' . http_build_query($form);

    $rep = $this->post($url, $post_data);
    $ret = self::parse_form($rep['body']);

    if(count($ret))
      return $ret;

    return $this->_err('Impossible de préparer le formulaire');
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

    if($this->is_connected()) return TRUE;

    if(preg_match('#<div class="bloc-erreur">\s*?(.+)\s*</div>#Us', $rep['body'], $match))
      return $this->_err($match[1]);

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
  public function post_msg_req($url) {
    $form = self::parse_form($this->get($url)['body']);

    if(count($form))
      return $form;

    return $this->_err('Impossible de préparer le formulaire');
  }

  /**
   * Finalise l'envoi d'un message
   * @param string $url url du topic 
   * @param string $msg message à envoyer
   * @param array $form  
   * @param int $ccode code de confirmation
   * @return boolean TRUE si le message est envoyé, FALSE sinon
   */
  public function post_msg_finish($url, $msg, $form, $ccode='') {
    $post_data = http_build_query($form) .
      '&message_topic=' . urlencode($msg) .
      '&form_alias_rang=1' .
      '&ccode=' . urlencode($ccode);

    $rep = $this->post($url, $post_data);

    if(self::redirects($rep['header']))
      return TRUE;

    return $this->_err('Erreur lors de l\'envoi du message');
  }

  /**
   * Prépare un formulaire pour l'édition d'un message
   * 
   * Le formulaire contient 'fs_signature' si un captcha est présent
   * @param string $url 
   * @param int $id 
   * @return mixed FALSE s'il y a eu une erreur, le formulaire à renvoyer sinon
   */
  public function edit_req($url, $id) {
    $rep = $this->get($url);

    $tk = self::parse_ajax_tk($rep['body'], 'liste_messages');

    $get_data = http_build_query($tk) .
      '&id_message=' . urlencode($id) .
      '&action=get';

    $rep = $this->get('http://www.jeuxvideo.com/forums/ajax_edit_message.php', $get_data);
    $rep = json_decode($rep['body']);

    if($rep->erreur)
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
   * @param int $ccode code de confirmation
   * @return boolean TRUE s'il y n'y a pas eu d'erreur, FALSE sinon
   */
  public function edit_finish($url, $id, $msg, $form, $ccode='') {
    $post_data = http_build_query($form) .
      '&id_message=' . urlencode($id) .
      '&message_topic=' . urlencode($msg) .
      '&action=post';
    if($ccode)
      $post_data .= '&fs_ccode=' . urlencode($ccode);

    $rep = $this->post('http://www.jeuxvideo.com/forums/ajax_edit_message.php', $post_data);
    $rep = json_decode($rep['body']);

    if($rep->erreur)
      return $this->_err($rep->erreur);

    return TRUE;
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
    return $ret->erreur ? $this->_err($ret->erreur) : $ret->txt;
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
    return $ret->erreur ? $this->_err($ret->erreur) : TRUE;
  }

  /**
   * Retourne la liste des utilisateurs ignorés
   * @return mixed Tableau contenant les utilisateurs ignorés, chaque
   * utilisateur est représenté par un tableau associatif contenant
   * une valeur 'id' et 'human'. FALSE si une erreur est survenue
   */
  public function blacklist() {
    $rep = $this->get('http://www.jeuxvideo.com/sso/blacklist.php');

    $regex =  '#<li data-id-alias="(?P<id>[0-9]+)">.+' .
              '<span>(?P<human>.+)</span>.+'  .
              '</li>#Usi';

    preg_match_all($regex, $rep['body'], $matches, PREG_SET_ORDER);

    return count($matches) ? $matches : $this->_err('Indéfinie');
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

    $regex =  '#<li class="move line-ellipsis" data-id="(?P<id>[0-9]+)">.+' .
              '<a .+>[\r\n\s]*?(?P<human>.+)[\r\n\s]*</a>.+' .
              '</li>#Usi';

    preg_match_all($regex, $before, $forums, PREG_SET_ORDER);
    preg_match_all($regex, $after, $topics, PREG_SET_ORDER);

    return [ 'forums' => $forums, 'topics' => $topics ];
  }

  /**
   * Fait une recherche sur la liste des forums 
   * @param string $name 
   * @return array Tableau de tableaux associatifs contenant 'id', 'slug' et 'human'
   */
  public function forum_search($name) {
    $rep = $this->get(
      'http://m.jeuxvideo.com/forums/search_forum.php',
      'input_search_forum='.urlencode($name)
    );

    $regex =  '#<li>.+' .
              '<a href="//m.jeuxvideo.com/forums/0-(?P<id>[0-9]+)-0-1-0-1-0-(?P<slug>.+).htm".+>.+' .
              '<h2 class="lib">(?P<human>.+)</h2>' .
              '#Usi';

    preg_match_all($regex, $rep['body'], $matches, PREG_SET_ORDER);

    return $matches;
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

  private function redirects($hdr) {
    return FALSE !== stripos($hdr, "\nLocation:");
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
