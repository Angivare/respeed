<?php

$jours = ['lundi', 'mardi', 'mercr', 'jeudi', 'vendr', 'samedi', 'dim'];
$mois = ['janv', 'fév', 'mars', 'avr', 'mai', 'juin', 'juil', 'août', 'sept', 'oct', 'nov', 'déc'];
$mois_jvc = [
  'janvier' => '01',
  'février' => '02',
  'mars' => '03',
  'avril' => '04',
  'mai' => '05',
  'juin' => '06',
  'juillet' => '07',
  'août' => '08',
  'septembre' => '09',
  'octobre' => '10',
  'novembre' => '11',
  'décembre' => '12',
];
$superlatifs = [
  'brillant',
  'épique',
  'exceptionnel',
  'extraordinaire',
  'épatant',
  'éblouissant',
  'éclatant',
  'excellent',
  'fantastique',
  'formidable',
  'fabuleux',
  'glorieux',
  'héroïque',
  'incroyable',
  'interloquant',
  'inoubliable',
  'irrésistible',
  'incomparable',
  'légendaire',
  'merveilleux',
  'mythique',
  'mémorable',
  'magistral',
  'prodigieux',
  'phénoménal',
  'renversant',
  'remarquable',
  'redoutable',
  'spectaculaire',
  'stupéfiant',
  'sensationnel',
];
$new_smileys = ['cimer', 'ddb', 'pave', 'siffle', 'objection'];

function h($string) {
  return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5);
}

function adapt_html($message, $date, $id) {
  global $new_smileys;
  
  $message = '<div class="contentest">' . $message . '</div>';

  // Mise en forme édition
  preg_match('#</div><div class="info-edition-msg">\s*Message édité le (?P<date>.+) par\s*<span class="JvCare [0-9A-F]*" target="_blank">(?P<pseudo>.*)</span>#Usi', $message, $matches_edit);
  if ($matches_edit) {
    $message = str_replace($matches_edit[0], '', $message);
    $message .= '<p class="edit-mention" title="' . $date . '">Modifié après ' . edit_date_difference($date, $matches_edit['date']) . '</p>';
  }

  // JVCare
  $message = jvcare($message);
  
  // Vire la signature qui apparaît parfois
  $pos_signature = strpos($message, '</div><div class="signature-msg  text-enrichi-forum ">');
  if ($pos_signature !== false) {
    $message = substr($message, 0, $pos_signature) . '</div>'; // </div> for .contentest
  }
  
  // Fix JVC : Ajout des miniatures NoelShack pour fichiers SWF et PSD
  $message = preg_replace('#\.(swf|psd)" data-def="NOELSHACK" target="_blank"><img class="img-shack" width="68" height="51" src="[^"]+"#Usi', '.$1" data-def="NOELSHACK" target="_blank"><img class="img-shack" width="68" height="51" src="//www.noelshack.com/pics/mini_$1.png"', $message);

  // Réparation des liens en /profil/pseudo.html
  $message = preg_replace('#(<a href="https?://www\.jeuxvideo\.com/profil/.+)\.html"#Usi', '$1?mode=page_perso"', $message);

  // Transformations liens vers topics en liens internes
  $message = preg_replace_callback('#<a href="(?P<url>https?://(www|m)\.jeuxvideo\.com/forums/(?P<mode>[0-9]+)-(?P<forum>[0-9]+)-(?P<topic>[0-9]+)-(?P<page>[0-9]+)-0-1-0-(?P<slug>[0-9a-z-]+)\.htm)"#Usi', function ($matches) {
    $new_str = $matches[0];
    $path = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $matches['forum'];
    if($matches['topic'])
      $path .= '/' . ($matches['mode'] == '1' ? '0' : '') . $matches['topic'] . '-' . $matches['slug'];
    else
      $path .= '-' . $matches['slug'];
    if ($matches['page'] != 1) {
      $path .= '/' . $matches['page'];
    }
    $new_str = str_replace($matches['url'], $path, $new_str);
    return $new_str;
  }, $message);

  // Transformation des liens NoelShack en liens directs
  $message = preg_replace_callback('#<a href="(?P<url>https?://www\.noelshack\.com/(?P<year>[0-9]+)-(?P<container>[0-9]+)-(?P<path>.+))"#Usi', function ($matches) {
    $new_str = $matches[0];
    $path = 'http://image.noelshack.com/fichiers/' . $matches['year'] . '/' . $matches['container'] . '/' . $matches['path'];
    $new_str = str_replace($matches['url'], $path, $new_str);
    return $new_str;
  }, $message);
  
  // Fix liens mails, il manque le "mailto:" car on prend le contenu de l’élement a (pour outrepasser JvCare) et pas son attribut href
  $message = preg_replace('`<a href="((&#[x0-9a-f]+;)+)"`Usi', '<a href="mailto:$1"', $message);
  
  // Transformation des miniatures YouTube en lien
  $message = preg_replace('#<div class="player-contenu"><div class="embed-responsive embed-responsive-16by9"><iframe src="//www.youtube.com/embed/([^"]+)" allowfullscreen></iframe></div></div>#Usi', '<a href="http://youtu.be/$1" class="xXx" target="_blank" title="http://youtu.be/$1">http://youtu.be/$1</a>', $message);
  
  // Transformation des miniatures vidéos jeuxvideo.com en lien
  $message = preg_replace('#<div class="player-contenu">\s+<div class="embed-responsive embed-responsive-16by9">\s+<div class="embed-responsive-item" >\s+<div class="player-jv" id="player-jv-[0-9]+-[0-9]+" data-src="/contenu/medias/video.php\?q=config&amp;id=[0-9]+">Chargement du lecteur vidéo...</div>\s+</div>\s+</div>\s+</div>#Usi', '<p><a href="http://www.jeuxvideo.com/___/forums/message/' . $id . '" class="xXx" target="_blank" title="http://www.jeuxvideo.com/___/forums/message/' . $id . '">Miniature vidéo sur jeuxvideo.com</a></p>', $message);

  // Smileys à venir
  foreach ($new_smileys as $code) {
    $message = preg_replace('#([^=][^"])?:' . $code . ':([^"])?#Usi', '$1<img src="//image.jeuxvideo.com/smileys_img/' . $code . '.gif" alt=":' . $code . ':" data-def="SMILEYS" data-code=":' . $code . ':" title=":' . $code . ':">$2', $message);
  }

  // Rajout de target="_blank" aux liens externes
  $message = preg_replace_callback('#<a.*href="(?P<url>.*)".*>#Usi', function($matches) {
    $ret = $matches[0];
    $has_blank = (strpos($ret, 'target="_blank"') !== false) ? true : false;
    if(preg_match('#^https?://' . $_SERVER['HTTP_HOST'] .'#Usi', $matches['url'])) {
      if($has_blank) return str_replace('target="_blank"', '', $ret);
    } else {
      if(!$has_blank) return str_replace('>', ' target="_blank">', $ret);
    }
    return $ret;
  }, $message);

  return $message;
}

function jvcare($str) {
  return preg_replace_callback('#<span class="JvCare ([0-9A-F]+)"[^>]*>([^<]*(?:<i></i><span>[^<]+</span>)?[^<]+)</span>#Usi', function ($matches) {
    $new_str = $matches[0];
    $new_str = str_replace('<span class="JvCare ' . $matches[1], '<a href="' . strip_tags($matches[2]) . '" class="xXx', $new_str);
    $new_str = substr($new_str, 0, -strlen('</span>'));
    $new_str .= '</a>';
    return $new_str;
  }, $str);
}

function relative_date_timestamp($timestamp, $topicList = false) {
  global $jours, $mois;
  $now = time();
  $diff = $now - $timestamp;
  
  // Moins d’une minute
  if ($diff < 60) {
    return $diff . ' s';
  }

  // Moins d’une heure
  if ($diff < 60 * 60) {
    return floor($diff / 60) . ' m ' . ($diff % 60) . ' s';
  }

  // Aujourd’hui
  if (date('dmy', $timestamp) == date('dmy', $now)) {
    return date('H:i', $timestamp);
  }

  // Hier
  if (date('dmy', $timestamp) == date('dmy', $now - 60 * 60 * 24)) {
    if ($topicList) {
      return 'hier';
    }
    return 'hier, ' . date('H:i', $timestamp);
  }

  // De cette année
  if (date('Y', $timestamp) == date('Y')) {
    if ($topicList) {
      return date('j', $timestamp) . ' ' . $mois[date('n', $timestamp) - 1];
    }
    return date('j', $timestamp) . ' ' . $mois[date('n', $timestamp) - 1] . ', ' . date('H:i', $timestamp);
  }

  if ($topicList) {
    return date('j', $timestamp) . ' ' . $mois[date('n', $timestamp) - 1] . ' ' . date('Y', $timestamp);
  }
  return date('j', $timestamp) . ' ' . $mois[date('n', $timestamp) - 1] . ' ' . date('Y', $timestamp) . ', ' . date('H:i', $timestamp);
}

function relative_date_topic_list($str_date) {
  return relative_date_timestamp(date_topic_list_to_timestamp($str_date), true);
}

function date_topic_list_to_timestamp($str_date) {
  if (strpos($str_date, '/') !== false) {
    // Convertir en format US pour strtotime
    $array_date = explode('/', $str_date);
    $str_date = $array_date[1] . '/' . trim($array_date[0]) . '/' . $array_date[2];
  }
  return strtotime($str_date);
}

function relative_date_messages($str_date) {
  return relative_date_timestamp(date_messages_to_timestamp($str_date));
}

function date_messages_to_timestamp($str_date) {
  global $mois_jvc;
  // Convertir en format US pour strtotime
  $array_date = explode(' ', $str_date);
  $str_date = $mois_jvc[$array_date[1]] . '/' . $array_date[0] . '/' . $array_date[2] . ' ' . $array_date[4];
  return strtotime($str_date);
}

function edit_date_difference($post_date, $edit_date) {
  global $mois_jvc;
  // Convertir en format US pour strtotime
  $array_date = explode(' ', $post_date);
  $post_date = $mois_jvc[$array_date[1]] . '/' . $array_date[0] . '/' . $array_date[2] . ' ' . $array_date[4];
  $array_date = explode(' ', $edit_date);
  $edit_date = $mois_jvc[$array_date[1]] . '/' . $array_date[0] . '/' . $array_date[2] . ' ' . $array_date[4];

  return relative_date_timestamp(time() - (strtotime($edit_date) - strtotime($post_date)));
}

function superlatif() {
  global $superlatifs;
  return $superlatifs[mt_rand(0, count($superlatifs) - 1)];
}

function wbr_pseudo($pseudo) {
  $wbr_pseudo = '<wbr>';
  for ($i = 0; $i < strlen($pseudo); $i++) {
    $char = $pseudo[$i];
    if ($i > 0) {
      $old_char = $pseudo[$i - 1];
      if (
        (($char >= 'a' && $char <= 'z') && !($old_char >= 'a' && $old_char <= 'z') && !($old_char >= 'A' && $old_char <= 'Z')) ||
        (($char >= 'A' && $char <= 'Z') && !($old_char >= 'A' && $old_char <= 'Z')) ||
        (($char >= '0' && $char <= '9') && !($old_char >= '0' && $old_char <= '9')) ||
        (strpos('-_[]', $char) !== false && strpos('-_[]', $old_char) === false)) {
        $wbr_pseudo .= '</wbr><wbr>';
      }
    }
    $wbr_pseudo .= $char;
  }
  $wbr_pseudo .= '</wbr>';
  return $wbr_pseudo;
}

function array_max($array, $comp_func) {
  reset($array);
  $max = each($array)[1];
  while( FALSE !== ($v = each($array)) )
    $max = $comp_func($v[1], $max) ? $v[1] : $max;
  return $max;
}

function strip_matches($matches) {
  foreach($matches as $k => $v)
    if(is_int($k))
      unset($matches[$k]);
  return $matches;
}

function delay($f, &$t) {
  $t =  microtime(TRUE)*1000;
  $ret = $f();
  $t = microtime(TRUE)*1000 - $t;
  return $ret;
}

function generate_message_markup($message) {
  $is_ours = strcasecmp(isset($_COOKIE['pseudo']) ? $_COOKIE['pseudo'] : '', $message['pseudo']) == 0;
  $odd_or_even = ($message['pos'] % 2 == 0) ? 'odd' : 'even';
  $is_ours_text = $is_ours ? 'mine' : '';
  $pseudoLowercase = strtolower($message['pseudo']);
  $pseudoWbr = wbr_pseudo($message['pseudo']);
  $markup = <<<MESSAGE
<div class="message {$odd_or_even} {$is_ours_text}" id="{$message['id']}" data-pseudo="{$message['pseudo']}" data-content-md5="{$message['contentMd5']}">
  <div class="action-menu">
MESSAGE;

  if (!$is_ours) {
    $markup .= '<span class="action meta-ignore">Ignorer</span>';
  }
  else {
    $markup .= '<span class="action meta-delete">Supprimer</span>';
    $markup .= '<span class="action meta-edit">Modifier</span>';
  }
  $markup .= <<<MESSAGE
<span class="action meta-quote">Citer</span>
  </div>
  <div class="not-action-menu">
    <div class="message-header">
      <div class="meta-author">
        <span class="author pseudo-{$message['status']} desktop"><a href="http://m.jeuxvideo.com/profil/{$pseudoLowercase}.html" target="_blank" class="m-profil">{$pseudoWbr}</a></span>
        <span class="avatar">
MESSAGE;
  if ($message['avatar']) {
    $markup .= '<a href="' . $message['avatarBig'] . '"><img src="' . $message['avatar'] . '"></a><!--';
  } else {
    $markup .= '<span class="default-avatar"><img src="/images/triangles.php?s=' . $message['pseudo'] . '"></span><!--';
  }
  $markup .= <<<MESSAGE
        --></span><!--
        --><span class="author pseudo-{$message['status']} mobile"><a href="http://m.jeuxvideo.com/profil/{$pseudoLowercase}.html" class="m-profil">{$pseudoWbr}</a></span>
      </div>
      <div class="meta-actions">
        <span class="meta-permalink meta-menu js-date" title="{$message['dateRaw']}">{$message['date']}</span>
      </div>
      <div class="actions-desktop">
        <div class="action-desktop action-desktop-quote meta-quote" title="Citer"></div>
MESSAGE;
  if ($is_ours) {
    $markup .= '<div class="action-desktop action-desktop-edit meta-edit" title="Modifier"></div>';
  }
  $markup .= <<<MESSAGE
      </div>
    </div>
    <div class="mobile message-border"></div>
    <div class="js-content content">{$message['content']}</div>
    <div class="clearfix"></div>
    <div class="ignored-message"><span class="meta-unignore">Ne plus ignorer</span> {$message['pseudo']} parle mais se fait ignorer.</div>
  </div>
</div>
<script>liste_messages.push({$message['id']})</script>
MESSAGE;
  return $markup;
}

function generate_topic_pagination_markup($page, $last_page, $forum, $topic, $topic_mode, $slug) {
  $pages = [];
  for ($i = $page; $i < 7; $i++) {
    $pages[] = ' ';
  }
  if ($page != 1) {
    $pages[] = 1;
    for ($i = $page - 5; $i < $page; $i++) {
      if ($i > 1) {
        $pages[] = $i;
      }
    }
  }
  $pages[] = $page;
  if ($page != $last_page) {
    for ($i = $page + 1; $i <= $page + 5; $i++) {
      if ($i < $last_page) {
        $pages[] = $i;
      }
    }
    $pages[] = $last_page;
  }
  for ($i = $last_page - $page; $i < $last_page - $last_page + 6; $i++) {
    $pages[] = ' ';
  }

  $markup = '';
  foreach ($pages as $i) {
    if ($i == ' ') {
      $markup .= <<<MARKUP
        <span class="faketable empty">
          <span class="link"></span>
        </span>

MARKUP;
      continue;
    }
    $number = $i;
    $is_sign = (int)$number != $i;
    $topic_id = ($topic_mode == 1 ? '0' : '') . $topic;
    $page_trail = $i > 1 ? "/{$i}" : '';
    $markup .= '        <span class="faketable">' . "\n          ";
    if ($i != $page) {
      if ($i == $page + 1) {
        $markup .= "<a href='/{$forum}/{$topic_id}-{$slug}{$page_trail}' class='link next-page'>{$number}</a>";
      }
      else {
        $markup .= "<a href='/{$forum}/{$topic_id}-{$slug}{$page_trail}' class='link'>{$number}</a>";
      }
    }
    else {
      $markup .= "<span class='link active'>{$number}</span>";
    }
    $markup .= "\n" . '        </span>' . "\n";
  }

  return $markup;
}

function display_footer_if_not_connected() {
  global $jvc;
  if (!$jvc->is_connected()) {
    echo '<footer class="site-footer">JVForum n’est pas affilié avec <a href="http://www.jeuxvideo.com/">jeuxvideo.com</a>.</footer>';
  }
}
