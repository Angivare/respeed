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
$stickers = [
  'Hap' => [
    '1kki' => 'pose',
    '1kkn' => 'prof',
    '1kkh' => 'ananas',
    '1kkl' => 'plage',
    '1kkm' => 'onche',
    '1kkk' => 'pls2',
    '1kkg' => 'flaque',
    '1kkj' => 'btg2',
  ],
  'Noel' => [
    '1kks' => 'pls',
    '1kkq' => 'continue',
    '1kkt' => 'haha',
    '1kkp' => 'nudiste',
    '1kku' => 'panache',
    '1kkr' => 'bonnet',
    '1kkv' => 'btg',
    '1kko' => 'masque',
  ],
  'Bridgely' => [
    '1jnj' => 'billets',
    '1jnc' => 'hahaha',
    '1jnh' => 'perplexe',
    '1jng' => 'furie',
    '1jnf' => 'cigare',
    '1jni' => 'dehors2',
    '1jne' => 'hein',
    '1jnd' => 'malin',
  ],
  'Lamasticot' => [
    '1kgx' => 'emo',
    '1kh1' => 'laser',
    '1kgz' => 'jesus',
    '1kgv' => 'racaille',
    '1kgw' => 'btg3',
    '1kgy' => 'couronne',
    '1kgu' => 'flamme',
    '1kh0' => 'pls3',
  ],
];

function h($string) {
  return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5);
}

function n($number, $decimals = 0) {
  return number_format($number, $decimals, ',', ' ');
}

function adapt_html($message, $date = '', $id = 0) {
  $message = '<div class="message__content-text">' . $message . '</div>';

  // Mise en forme édition
  preg_match('#</div><div class="info-edition-msg">\s*Message édité le (?P<date>.+) par\s*<span class="JvCare [0-9A-F]*" target="_blank">(?P<pseudo>.*)</span>#Usi', $message, $matches_edit);
  if ($matches_edit) {
    $message = str_replace($matches_edit[0], '', $message);
    $message .= '<p class="message__content-edit-mention"><span title="' . $date . '">Modifié après ' . edit_date_difference($date, $matches_edit['date']) . '</span></p>';
  }

  // JVCare
  $message = jvcare($message);
  
  // Vire la signature qui apparaît parfois
  $pos_signature = strpos($message, '</div><div class="signature-msg  text-enrichi-forum ">');
  if ($pos_signature !== false) {
    $message = substr($message, 0, $pos_signature) . '</div>'; // </div> pour .message__content-text
  }
  
  // Fix JVC : Ajout des miniatures NoelShack pour fichiers SWF et PSD
  $message = preg_replace('#\.(swf|psd)" data-def="NOELSHACK" target="_blank"><img class="img-shack" width="68" height="51" src="[^"]+"#Usi', '.$1" data-def="NOELSHACK" target="_blank"><img class="img-shack" width="68" height="51" src="//www.noelshack.com/pics/mini_$1.png"', $message);

  // Conversion miniatures NoelShack
  $message = preg_replace('#<a href="([^"]+)" data-def="NOELSHACK" target="_blank"><img class="img-shack" width="68" height="51" src="([^"]+)" alt="[^"]+"/></a>#Usi', '<a class="noelshack-link" href="$1" target="_blank"><img class="noelshack-link__thumb" src="$2"></a>', $message);

  // Conversion spoils
  $message = str_replace('<span class="bloc-spoil-jv en-ligne"><span class="contenu-spoil">', '<span class="spoil spoil--inline"><span class="spoil__content">', $message);
  $message = str_replace('<span class="bloc-spoil-jv"><span class="contenu-spoil">', '<span class="spoil spoil--block"><span class="spoil__content">', $message);

  // Conversion citations
  $message = str_replace('<blockquote class="blockquote-jv">', '<blockquote class="quote">', $message);

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
    $new_str .= ' data-link-jvc="true"';
    return $new_str;
  }, $message);

  // Transformation des liens NoelShack en liens directs
  $message = preg_replace_callback('#<a class="noelshack-link" href="(?P<url>https?://www\.noelshack\.com/(?P<year>[0-9]+)-(?P<container>[0-9]+)-(?P<path>.+))"#Usi', function ($matches) {
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

  // Conversion smileys
  $message = preg_replace('#<img src="//image\.jeuxvideo\.com/smileys_img/([^.]+)\.gif" alt="([^"]+)" data-def="SMILEYS" data-code="[^"]+" title="[^"]+" />#Usi', '<img class="smiley smiley--$1" src="//image.jeuxvideo.com/smileys_img/$1.gif" data-code="$2" title="$2" alt="$2">', $message);

  // Conversion stickers
  $message = preg_replace_callback('#<img class="img-stickers" src="http://jv.stkr.fr/p/(?P<id>[^"]+)"/>#Usi', function ($matches) {
    global $stickers;

    $ret = $matches[0];

    $id = $matches['id'];
    $shortcut = '';
    foreach ($stickers as $stickers_category) {
      foreach ($stickers_category as $id_loop => $shortcut_loop) {
        if ($id == $id_loop) {
          $shortcut = ':' . $shortcut_loop . ':';
        }
      }
    }

    return '<img class="js-sticker sticker" src="http://jv.stkr.fr/p3w/' . $id . '" data-sticker-id="' . $id . '" data-code="' . $shortcut . '" title="' . $shortcut . '" alt="' . $shortcut . '">';
  }, $message);

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
  
  // Suppression de potentielles failles
  $message = str_ireplace(['<style>', '<script>'], '', $message);

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
  $mine = strcasecmp(isset($_COOKIE['pseudo']) ? $_COOKIE['pseudo'] : '', $message['pseudo']) == 0;
  $even_modifier = ($message['pos'] % 2 == 0) ? '' : 'message--even';
  $mine_modifier = $mine ? 'message--mine' : '';
  $pseudoLowercase = strtolower($message['pseudo']);
  $pseudoDeleted = strpos($pseudoLowercase, ' ') !== false;
  $authorLinkTag = $pseudoDeleted ? 'span' : 'a';
  $authorLinkClass = $pseudoDeleted ? 'message__byline-author-link--no-link' : 'js-profile';
  $avatar = $message['avatar'] ? $message['avatar'] : ('/images/triangles.php?s=' . $pseudoLowercase);
  $pseudo_modifier = $message['status'] != 'user' ? ('message__byline-author-pseudo--' . $message['status']) : '';
  $default_avatar_modifier = $message['avatar'] ? '' : 'message__byline-author-avatar-image--default';
  $markup = <<<MESSAGE
<div class="message {$mine_modifier} {$even_modifier}" id="{$message['id']}" data-pseudo="{$message['pseudo']}" data-content-md5="{$message['contentMd5']}">
  <div class="message__actions">
MESSAGE;

  if ($mine) {
    $markup .= '<span class="js-delete message__actions-action message__actions-action--delete">Suppr.</span>';
    $markup .= '<span class="js-quote message__actions-action message__actions-action--quote">Citer</span>';
    $markup .= '<span class="js-edit message__actions-action message__actions-action--edit">Modifier</span>';
  }
  else {
    $markup .= '<span class="js-quote message__actions-action message__actions-action--quote">Citer</span>';
  }
  $markup .= <<<MESSAGE
</div>
  <div class="message__visible">
    <div class="message__byline">
      <div class="message__byline-author">
        <{$authorLinkTag} class="message__byline-author-link {$authorLinkClass}" href="/profil/{$pseudoLowercase}">
          <span class="message__byline-author-avatar"><img class="message__byline-author-avatar-image {$default_avatar_modifier}" src="{$avatar}"></span>
          <span class="message__byline-author-pseudo {$pseudo_modifier}" title="{$message['pseudo']}">{$message['pseudo']}</span>
        </{$authorLinkTag}>
      </div>
      <div class="message__byline-date">
        <span class="js-menu js-date message__byline-date-inline" title="{$message['dateRaw']}">{$message['date']}</span>
      </div>
    </div><!--
    --><div class="js-content message__content">{$message['content']}</div>
    <div class="clearfix"></div>
    <div class="message__quick-actions">
MESSAGE;
if ($mine) {
  $markup .= <<<MESSAGE
      <div class="js-delete message__quick-action message__quick-action--delete" title="Supprimer"></div>
      <div class="js-edit message__quick-action message__quick-action--edit" title="Modifier"></div>
MESSAGE;
}
$markup .= <<<MESSAGE
      <div class="js-quote message__quick-action message__quick-action--quote" title="Citer"></div>
    </div>
  </div>
</div>
<script>liste_messages.push({$message['id']})</script>
MESSAGE;
  return $markup;
}

function generate_topic_pagination_markup($page, $last_page, $forum, $topic, $topic_mode, $slug) {
  $pages = [];

  if ($page > 4) {
    $pages[] = 1;
  }

  for ($i = max(1, $page - 3); $i < $page; $i++) {
    $pages[] = $i;
  }

  $pages[] = $page;

  if ($page != $last_page) {
    for ($i = $page + 1; $i < min($page + 4, $last_page); $i++) {
      $pages[] = $i;
    }

    $pages[] .= $last_page;
  }

  $markup = '';
  $topic_id = ($topic_mode == 1 ? '0' : '') . $topic;
  $page_trail = $i > 1 ? "/{$i}" : '';
  foreach ($pages as $i) {
    $markup .= "        ";
    if ($i == $page) {
      $markup .= '<span class="pagination-topic__page"><span class="pagination-topic__page-link pagination-topic__page-link--active">' . $page . '</span></span>';
    }
    else {
      $markup .= '<span class="pagination-topic__page"><a class="pagination-topic__page-link ' . ($i == $page + 1 ? 'pagination-topic__page-link--next' : '') . '" href="/' . $forum . '/' . $topic_id . '-' . $slug . ($i > 1 ? ('/' . $i) : '') . '">' . $i . '</a></span>';
    }
    $markup .= "\n";
  }

  return $markup;
}

function display_footer_if_not_connected() {
  global $jvc;
  if (!$jvc->is_connected()) {
    echo '<footer class="site-footer">JVForum n’est pas affilié avec <a href="http://www.jeuxvideo.com/">jeuxvideo.com</a>.</footer>';
  }
}

function convert_stickers($message) {
  global $stickers;
  foreach ($stickers as $stickers_category) {
    foreach ($stickers_category as $id => $shortcut) {
      $message = str_replace(':' . $shortcut . ':', '[[sticker:p/' . $id . ']]', $message);
    }
  }
  return $message;
}
