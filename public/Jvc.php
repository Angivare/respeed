<?php

/**
 * Représente la session sur JVC du client.
 * 
 * Tous les appels à JVC doivent être effectués avant la moindre
 * sortie (pour pouvoir mettre à jour le header Set-Cookie)
 * @package default
 */
class Jvc {
  /**
   * Retourne la session sur JVC du client depuis les cookies
   * @param string $site 'JVC' ou 'FJV'
   */
  public function __construct($site = 'JVC') {
    $this->err = 'Indéfinie';
    if($site === 'JVC') {
      $this->domain = 'http://www.jeuxvideo.com';
      $this->cookie_pre = '_JVCCOK_';
      $this->tokens_pre = '_JVCTOK_';
    } else if($site === 'FJV') {
      $this->domain = 'http://www.forumjv.com';
      $this->cookie_pre = '_FJVCOK_';
      $this->tokens_pre = '_FJVTOK_';
    } else {
      die('Mauvais paramètre fourni à Jvc::__construct, contacter l\'admin');
    }

    $this->cookie = [];
    foreach($_COOKIE as $k => $v)
      if(substr($k, 0, strlen($this->cookie_pre)) === $this->cookie_pre)
        $this->cookie[substr($k, strlen($this->cookie_pre))] = $v;

    $this->tk = [];
    foreach($_COOKIE as $k => $v)
      if(substr($k, 0, strlen($this->tokens_pre)) === $this->tokens_pre)
        $this->tk[substr($k, strlen($this->tokens_pre))] = $v;

    $this->tk_update = isset($_COOKIE['tk_update']) ? $_COOKIE['tk_update'] : 0;

    if(!isset($this->cookie['dlrowolleh']) || !$this->cookie['dlrowolleh']) {
      $this->cookie['dlrowolleh'] = NULL;
      $this->get($this->domain . '/login');
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
    return isset($this->cookie['coniunctio']);
  }

  /**
   * Déconnecte le client de JVC
   */
  public function disconnect() {
    foreach($this->cookie as $k => $v)
      setcookie($this->cookie_pre.$k, '', time()-1, '/', null, FALSE, TRUE);
    setcookie('pseudo', '', time()-1, '/', null, FALSE, TRUE);
    $this->cookie = [];

    foreach($this->tk as $k => $v)
      setcookie($this->tokens_pre.$k, '', time()-1, '/', null, FALSE, TRUE);
    setcookie('tk_update', '', time()-1, '/', null, FALSE, true);
    $this->tk = [];
    $this->last_update = 0;

    $this->cookie['dlrowolleh'] = NULL;
    $this->get($this->domain . '/profil/angivare?mode=page_perso');
  }

  /**
   * Effectue la première étape de la connexion
   * @param string $nick 
   * @param string $pass 
   * @return mixed FALSE si la requête a échoué, formulaire à réutiliser
   * dans connect_finish() sinon
   */
  public function connect_req($nick, $pass) {
    $url = $this->domain . '/login';

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
   * @param string $ccode 
   * @param array &$ret_form contient le formulaire à réutiliser dans
   * le cas d'une erreur
   * @return boolean TRUE si la connexion a fonctionné, FALSE sinon
   */
  public function connect_finish($nick, $pass, $form, $ccode, &$ret_form) {
    $url = $this->domain . '/login';

    $post_data = 'login_pseudo=' . urlencode($nick) .
                 '&login_password=' . urlencode($pass) .
                 '&' . http_build_query($form) .
                 '&fs_ccode=' . urlencode($ccode);

    $rep = $this->post($url, $post_data);

    if($this->is_connected()) {
      setcookie('pseudo', $nick, time() + 60 * 60 * 24 * 365, '/', null, FALSE, TRUE);
      return TRUE;
    }

    $ret_form = self::parse_form($rep['body']);

    if(preg_match('#<div class="bloc-erreur">\s*?(.+)\s*</div>#Us', $rep['body'], $match))
      return $this->_err($match[1]);

    return $this->_err('Indéfinie');
  }

  /**
   * Renvoie la page du lien permanent du post
   * @param int $id 
   * @return mixed la page, séparée en 'header' et 'body' ou FALSE
   */
  public function message_get($id) {
    $rep = $this->get($this->domain . "/respeed/forums/message/{$id}");
    $location = self::redirects($rep['header']);
    if(!$location) return $this->_err('Impossible de trouver le lien permanent');
    return $this->get($this->domain . $location);
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
    $form = self::parse_form($this->get($url)['body']);
    if(count($form)) return $form;
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
  public function message_post_finish($url, $msg, $form, $ccode='', &$ret_location=NULL) {
    $post_data = http_build_query($form) .
      '&message_topic=' . urlencode($msg) .
      '&form_alias_rang=1' .
      '&fs_ccode=' . urlencode($ccode);

    $rep = $this->post($url, $post_data);

    if($location = self::redirects($rep['header'])) {
      if($ret_location !== NULL)
        $ret_location = $location;
      return TRUE;
    }
    else if(preg_match('#<div class="alert-row">(.+?)</div>#si', $rep['body'], $match))
      return $this->_err($match[1]);
    else
      return $this->_err('Erreur lors de l\'envoi du message');
  }

  /**
   * Prépare un formulaire pour la création d'un topic
   * 
   * Le formulaire contient 'fs_signature' si un captcha est présent
   * @param string $url url du forum
   * @return mixed FALSE si une erreur a eu lieu, le formulaire sinon
   */
  public function topic_post_req($url) {
    $rep = $this->get($url)['body'];
    $form = self::parse_form($rep);
    if(count($form)) return $form;
    else if(NULL !== strpos($rep,
      '<div class="alert-row"> Vous ne pouvez pas créer un nouveau sujet sur ce forum car il est fermé. </div>'))
      return $this->_err('Forum fermé');
    else
      return $this->_err('Impossible de préparer le formulaire');
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
  public function topic_post_finish($url, $title, $msg, $form, $poll_question='', $poll_answers=[], $ccode='', &$ret_location=NULL) {
    $post_data = http_build_query($form) .
      '&titre_topic=' . urlencode($title) .
      '&message_topic=' . urlencode($msg) .
      '&fs_ccode=' . urlencode($ccode) .
      '&submit_sondage=' . ($poll_question ? '1' : '0') .
      '&question_sondage=' . urlencode($poll_question) .
      '&form_alias_rang=1';
    foreach($poll_answers as $v)
      $post_data .= '&reponse_sondage%5B%5D=' . urlencode($v);

    $rep = $this->post($url, $post_data);

    if($location = self::redirects($rep['header'])) {
      if($ret_location !== NULL)
        $ret_location = $location;
      return TRUE;
    }
    else if(preg_match('#<div class="alert-row">(.+?)</div>#si', $rep['body'], $match))
      return $this->_err($match[1]);
    else
      return $this->_err('Erreur lors de la création du topic');
  }

  /**
   * Récupère des infos sur le sondage
   * @param string $url 
   * @return mixed FALSE si une erreur a eu lieu, les infos sur le sondage sinon
   */
  public function poll_answer_req($url) {
    $rep = $this->get($url);
    $regex = '#<tr>.+<td class="reponse">.+' .
             '<a .+ data-id-sondage="(?P<question>.+)" data-id-reponse="(?P<answer>.+)".*>' .
             '(?P<human>.+)</a>.+</td>.+</tr>#Usi';
    if(preg_match_all($regex, $rep['body'], $matches, PREG_SET_ORDER))
      return $matches;
    else
      return $this->_err('Pas de formulaire');
  }

  /**
   * Répond à un sondage
   * @param int $id_topic 
   * @param int $id_question 
   * @param int $id_answer 
   * @return mixed TRUE/FALSE
   */
  public function poll_answer_finish($id_topic, $id_question, $id_answer) {
    $tk = $this->ajax_array('liste_messages');
    $post_data = http_build_query($tk) .
      '&id_topic=' . urlencode($id_topic) .
      '&id_sondage=' . urlencode($id_question) .
      '&id_sondage_reponse=' . urlencode($id_answer);

    $rep = $this->post($this->domain . '/forums/ajax_topic_sondage_vote.php', $post_data);
    $rep = json_decode($rep['body']);

    if($rep->erreur)
      return $this->_err($rep->erreur);
    else
      return TRUE;
  }

  /**
   * Rafraîchit les tokens ajax
   * @param string $body Le contenu d'un topic
   * @return boolean TRUE s'il n'y a pas eu d'erreur, FALSE sinon
   */
  public function tokens_refresh($body) {
    $this->tk = self::parse_ajax_tk($body, '.+?', TRUE);
    if(!$this->tk) return $this->_err('Indéfinie');
    $this->tk_update = time();
    foreach($this->tk as $k => $v)
      setcookie($this->tokens_pre.$k, $v, time() + 60 * 60 * 24 * 365, '/', null, FALSE, TRUE);
    setcookie('tk_update', $this->tk_update, time() + 60 * 60 * 24 * 365, '/', null, FALSE, TRUE);
    return TRUE;
  }

  public function tokens() { return $this->tk; }
  public function tokens_last_update() { return $this->tk_update; }

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

    $rep = $this->get($this->domain . '/forums/ajax_edit_message.php', $get_data);
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
   * @param int $id 
   * @param string $msg 
   * @param array $form 
   * @param string $ccode code de confirmation
   * @return boolean TRUE s'il y n'y a pas eu d'erreur, FALSE sinon
   */
  public function edit_finish($id, $msg, $form, $ccode='') {
    $post_data = http_build_query($form) .
      '&id_message=' . urlencode($id) .
      '&message_topic=' . urlencode($msg) .
      '&action=post';
    if($ccode)
      $post_data .= '&fs_ccode=' . urlencode($ccode);

    $rep = $this->post($this->domain . '/forums/ajax_edit_message.php', $post_data);
    $rep = json_decode($rep['body']);

    if($rep->erreur)
      return $this->_err($rep->erreur);

    return TRUE;
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

    $rep = $this->post($this->domain . '/forums/ajax_edit_title.php', $post_data);
    $rep = json_decode($rep['body']);

    if($rep->erreur)
      return $this->_err($rep->erreur);
    else
      return TRUE;
  }

  /**
   * Retourne la citation d'un texte
   * @param int $id id du post à citer
   * @return mixed FALSE si la citation a échoué, la citation sinon
   */
  public function quote($id) {
    $tk = $this->ajax_array('liste_messages');
    $post_data = 'id_message=' . urlencode($id) .
      '&' . http_build_query($tk);
    $ret = json_decode(self::post($this->domain . '/forums/ajax_citation.php',
      $post_data)['body']);
    return $ret->erreur ? $this->_err($ret->erreur) : $ret->txt;
  }

  /**
   * Ajoute un pseudo à la blacklist
   * @param int $id id à blacklist
   * @return boolean TRUE si le pseudo est ajouté, FALSE sinon
   */
  public function blacklist_add($id) {
    $tk = $this->ajax_array('preference_user');
    $get_data = 'id_alias_msg=' . urlencode($id) .
      '&action=add' . '&' . http_build_query($tk);
    $ret = json_decode($this->get($this->domain . '/forums/ajax_forum_blacklist.php', $get_data)['body']);
    return $ret->erreur ? $this->_err($ret->erreur) : TRUE;
  }

  /**
   * Enlève un pseudo de la blacklist
   * @param int $id id correspondant à la personne, reçu par Jvc::blacklist()
   * @return boolean TRUE si le pseudo est enlevé, FALSE sinon
   */
  public function blacklist_remove($id) {
    $get_data = 'id_alias_unblacklist=' . urlencode($id);
    $ret = json_decode($this->get($this->domain . '/sso/ajax_delete_blacklist.php', $get_data)['body']);
    return $ret->erreur ? $this->_err($ret->erreur) : TRUE;
  }

  /**
   * Retourne la liste des utilisateurs ignorés
   * @return mixed Tableau contenant les utilisateurs ignorés, chaque
   * utilisateur est représenté par un tableau associatif contenant
   * une valeur 'id' et 'human'. FALSE si une erreur est survenue
   */
  public function blacklist_get() {
    $rep = $this->get($this->domain . '/sso/blacklist.php');

    $regex =  '#<li data-id-alias="(?P<id>[0-9]+)">.+' .
              '<span>(?P<human>.+)</span>.+'  .
              '</li>#Usi';

    if(FALSE === preg_match_all($regex, $rep['body'], $matches, PREG_SET_ORDER))
      return $this->_err('Indéfinie');
    else {
      $ret = [];
      for($i = 0; $i < count($matches); $i++) {
        $ret[] = ['id' => $matches[$i]['id'], 'human' => $matches[$i]['human'] ];
      }
      return $ret;
    }
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
    $rep = $this->get($this->domain . '/forums/ajax_forum_prefere.php', $get_data);
    return TRUE;
  }

  /**
   * Retourne la liste des sujets & topics préférés
   * @return array Tableau associatif contenant les sujets et topics favoris
   */
  public function favorites_get() {
    $rep = $this->get($this->domain . '/forums.htm');

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

    return [ 'forums' => $forums, 'topics' => $topics ];
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

    $rep = $this->post($this->domain . '/forums/modal_del_message.php', $post_data);

    //TODO: error handling? la page ne semble renvoyer aucune réponse cependant..
    return TRUE;
  }

  /**
   * Restaure un message
   * @param int $id 
   * @return boolean TRUE/FALSE
   */
  public function message_restore($id) {
    $tk = self::ajax_array('moderation_forum');
    $post_data = http_build_query($tk) .
      '&type=delete' .
      '&tab_message%5B%5D=' . urlencode($id);

    $rep = json_decode($this->post($this->domain . '/forums/modal_del_message.php', $post_data)['body']);
    return $rep->erreur ? $this->_err($rep->erreur) : TRUE;
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
   * Retourne le header "location"
   * @param string $hdr 
   * @return mixed Le header "location" ou FALSE
   */
  public static function redirects($hdr) {
    $beg = stripos($hdr, "\nLocation:");
    if($beg === FALSE) return FALSE;
    else $beg += strlen("\nLocation:");
    $end = strpos($hdr, "\n", $beg);
    return trim(substr($hdr, $beg, $end-$beg));
  }

  /**
   * Effectue une requête POST
   * @param string $url 
   * @param mixed $data champ à envoyer, urlencodé ou un tableau associatif 
   * @param boolean $connected TRUE (par défaut) si la requête doit être envoyée
   * en tant qu'utilisateur connecté, FALSE sinon
   * @param boolean $cached FALSE (par défaut) si la dernière version du fichier doit être
   * renvoyée, TRUE sinon
   * @return array réponse du serveur, séparé en 'header' et 'body'
   */
  public function post($url, $data, $connected = TRUE, $cached = TRUE) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    return $this->finish_req($ch, $url, $connected, $cached);
  }

  /**
   * Effectue une requête GET
   * @param string $url 
   * @param string $query paramètres à envoyer, urlencodé 
   * @param boolean $connected TRUE (par défaut) si la requête doit être envoyée
   * en tant qu'utilisateur connecté, FALSE sinon
   * @param boolean $cached FALSE (par défaut) si la dernière version du fichier doit être
   * renvoyée, TRUE sinon
   * @return array réponse du serveur, séparé en 'header' et 'body'
   */
  public function get($url, $query = NULL, $connected = TRUE, $cached = TRUE) {
    $query = $query ? "?$query" : '';
    return $this->finish_req(curl_init(), $url . $query, $connected, $cached);
  }

  private function finish_req($ch, $url, $connected = TRUE, $cached = TRUE) {
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);

    if($this->is_connected() && $connected) {
      $coniunctio = $this->cookie['coniunctio'];
      $dlrowolleh = $this->cookie['dlrowolleh'];
    }
    else {
      $coniunctio = $cached ? NULL : '0';
      $dlrowolleh = $cached ? $this->cookie['dlrowolleh'] : NULL;
    }

    if(count($this->cookie) && ($connected !== FALSE || $cached === FALSE)) {
      curl_setopt($ch, CURLOPT_COOKIE, $this->cookie_string(['coniunctio' => $coniunctio, 'dlrowolleh' => $dlrowolleh]));
      $ip = $_SERVER['REMOTE_ADDR'];
      curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "HTTP_X_FORWARDED_FOR: $ip"
      ]);
    }

    $rep = curl_exec($ch);
    if (!$rep) {
      if(curl_errno($ch) !== CURLE_OPERATION_TIMEOUTED) {
        $this->fatal_err('Timeout.', 'La page sur jeuxvideo.com mettait plus de deux secondes à charger, elle a dû être arrêtée.', 504);
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
    return FALSE;
  }

  private function fatal_err($title, $message, $http_status_code = 200) {
    http_response_code($http_status_code);
    $body = <<<HTML
      <header class="site-header">
        <h2 class="site-title">
          <a href="/accueil" class="site-title-link"><span class="site-title-spacer">JV</span>Forum</a>
        </h2>
      </header>

      <div class="sheet">
        <div class="timeout">
          <h3>{$title}</h3>

          <p>{$message}</p>

          <p><a href="{$_SERVER['REQUEST_URI']}">Réessayer</a></p>
        </div>
      </div>
HTML;
    $jvc = new Jvc();
    $forum = $topic = $topicNew = $slug = $page = NULL;
    $token = [];
    $title = 'Erreur';
    include 'views/layout.php';
    exit;
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
      setcookie($this->cookie_pre.$k, $v, time() + 60 * 60 * 24 * 365, '/', null, FALSE, TRUE);
  }

  private function cookie_string($add) {
    $ret = '';
    foreach($this->cookie as $k => $v) {
      if(array_key_exists($k, $add)) {
        if($add[$k] === NULL) continue;
        $ret .= $k . '=' . $add[$k] . '; ';
        unset($add[$k]);
        continue;
      }
      $ret .= $k . '=' . $v . '; ';
    }
    foreach($add as $k => $v)
      if($v !== NULL)
        $ret .= $k . '=' . $v . '; ';
    return substr($ret, 0, -2);
  }

  private function ajax_array($type) {
    if(
        (!isset($this->tk["ajax_timestamp_$type"]) || !isset($this->tk["ajax_hash_$type"]))
      || (time() - $this->tokens_last_update() >= 3600/2)
    ) {
      $rep = $this->get($this->domain . '/forums/42-1000021-38675199-1-0-1-0-a-lire-avant-de-creer-un-topic.htm');
      self::tokens_refresh($rep['body']);
    }
    return [
      'ajax_timestamp' => $this->tk["ajax_timestamp_$type"],
      'ajax_hash' => $this->tk["ajax_hash_$type"]
    ];
  }

  private static function parse_form($bdy) {
    $regex = '<input type="hidden" name="fs_(.+?)" value="(.+?)"/>';
    preg_match_all($regex, $bdy, $matches);
    $ret = [];
    for($i = 0; $i < count($matches[0]); $i++)
      $ret['fs_'.$matches[1][$i]] = $matches[2][$i];
    return $ret;
  }

  private static function parse_ajax_tk($bdy, $type, $leave_tk_type = FALSE) {
    $regex = '<input type="hidden" name="(.+?)_('.$type.')" .+? value="(.+?)" />';
    preg_match_all($regex, $bdy, $matches);
    $ret = [];
    for($i = 0; $i < count($matches[0]); $i++)
      if($leave_tk_type)
        $ret[$matches[1][$i].'_'.$matches[2][$i]] = $matches[3][$i];
      else
        $ret[$matches[1][$i]] = $matches[3][$i];
    return $ret;
  }
}
