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
  'DomDeVill' => [
    '1ljl' => 'chevalier',
    '1ljj' => 'hop',
    '1ljm' => 'bide',
    '1ljn' => 'photo',
    '1ljo' => 'bescherelle',
    '1ljp' => '+1',
    '1ljr' => 'master',
    '1ljq' => 'thug',
  ],
  'Duracell' => [
    '1jc3-fr' => 'salut2',
    '1jc5' => 'love',
    '1li5' => 'gg',
    '1leb' => 'tchin',
    '1jcg' => 'jmec',
    '1jch' => 'hahaha2',
    '1li4' => 'star',
    '1jcl' => 'aide',
    '1leq-fr' => 'vacances',
    '1lej-fr' => 'joel',
    '1li3' => 'ba',
  ],
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
  'Grukk' => [
    '1lgd' => 'fort',
    '1lgc' => 'question2',
    '1lgf' => 'combat',
    '1lgb' => 'grogne',
    '1lgh' => 'grukk',
    '1lgg' => 'perplexe2',
    '1lga' => 'trance',
    '1lge' => 'pouce',
  ],
  'Bud' => [
    'zu6' => 'oklm',
    '1f8e' => 'oklm2',
    '1f89' => 'poker',
    'zuc' => 'bud+1',
    'zu2' => 'hahah',
    '1f8a' => 'dur',
    'zub' => 'hin',
    'zua' => 'zzz',
    'zu9' => 'grr',
    'zu8' => 'oh',
    '1f8d' => 'argh',
    '1f8b' => '5min',
    '1f8f' => 'bbq',
    '1f88' => 'sup',
    'zu7' => 'burp',
    '1f8c' => 'colis',
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
$javascripts_after_files = [];

function h($string) {
  return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5);
}

function n($number, $decimals = 0) {
  return number_format($number, $decimals, ',', ' ');
}

function adapt_html($message, $date = '', $id = 0) {
  // Signature sometimes appear (JVC bug), let's remove it
  $pos_signature = strpos($message, '</div><div class="signature-msg  text-enrichi-forum ">');
  if ($pos_signature !== false) {
    $message = substr($message, 0, $pos_signature);
  }

  $message = '<div class="message__content-text">' . $message . '</div>';

  // Format edit
  preg_match('#</div><div class="info-edition-msg">\s*Message édité le (?P<date>.+) par\s*<span class="JvCare [0-9A-F]*" target="_blank">(?P<pseudo>.*)</span>#Usi', $message, $matches_edit);
  if ($matches_edit) {
    $message = str_replace($matches_edit[0], '', $message);
    $message .= '<p class="message__content-edit-mention"><span title="' . $date . '">Modifié après ' . edit_date_difference($date, $matches_edit['date']) . '</span></p>';
  }

  // Normalize JvCare links
  $message = preg_replace_callback('#<span class="JvCare ([0-9A-F]+)"[^>]*>([^<]*(?:<i></i><span>[^<]+</span>)?[^<]+)</span>#Usi', function($matches) {
    $new_str = $matches[0];
    $new_str = str_replace(' rel="nofollow"', '', $new_str);
    $new_str = str_replace('<span class="JvCare ' . $matches[1], '<a href="' . strip_tags($matches[2]), $new_str);
    $new_str = substr($new_str, 0, -strlen('</span>'));
    $new_str .= '</a>';
    return $new_str;
  }, $message);

  // Normalize mail links, add missing "mailto:" due to JvCare normalization
  // Mail addresses are encoded using two styles of HTML entities, one style is randomly chosen
  // for each character. This must be normalized, otherwise the content checksum changes everytime
  // and it looks to JVForum like the message has been updated.
  $message = preg_replace_callback('`<a href="((&#[x0-9a-f]+;)+)">([^<]+)</a>`Usi', function ($matches) {
    $decoded = html_entity_decode($matches[1]);
    return '<a href="mailto:' . $decoded . '" target="_blank">' . $decoded . '</a>';
  }, $message);

  // Normalize cut links, and re-cut them
  $message = preg_replace_callback('#<a ([^>]+)>([^>]+)<i></i><span>([^>]+)</span>([^>]+)</a>#Usi', function($matches) {
    $url = $matches[2] . $matches[3] . $matches[4];
    if (strlen($url) > 110) {
      $url = substr($url, 0, 105) . '<span class="long-link__hidden-part">' . substr($url, 80) . '</span>';
      $new_str = '<a class="long-link" ' . $matches[1] . '>' . $url . '</a>';
    }
    else {
      $new_str = '<a ' . $matches[1] . '>' . $url . '</a>';
    }
    return $new_str;
  }, $message);

  // Normalize NoelShack thumbnails
  $message = preg_replace('#<a href="([^"]+)" data-def="NOELSHACK" target="_blank"><img class="img-shack" width="68" height="51" src="([^"]+)" alt="[^"]+"/></a>#Usi', '<a class="noelshack-link" href="$1" target="_blank"><img class="noelshack-link__thumb" src="$2" alt="$1"></a>', $message);

  // Make NoelShack links go straight to the image
  $message = preg_replace_callback('#<a class="noelshack-link" href="(?P<url>https?://www\.noelshack\.com/(?P<year>[0-9]+)-(?P<container>[0-9]+)-(?P<path>.+))"#Usi', function($matches) {
    $new_str = $matches[0];
    $path = 'http://image.noelshack.com/fichiers/' . $matches['year'] . '/' . $matches['container'] . '/' . $matches['path'];
    $new_str = str_replace($matches['url'], $path, $new_str);
    return $new_str;
  }, $message);

  // Add good NoelShack thumbnail for SWF and PSD files (JVC bug)
  $message = preg_replace('#<img class="noelshack-link__thumb" src=".+\.(swf|psd)"#Usi', '<img class="noelshack-link__thumb" src="//www.noelshack.com/pics/mini_$1.png"', $message);

  // Normalize spoils
  $message = str_replace('<span class="bloc-spoil-jv en-ligne"><span class="contenu-spoil">', '<span class="spoil spoil--inline"><span class="spoil__content">', $message);
  $message = str_replace('<span class="bloc-spoil-jv"><span class="contenu-spoil">', '<span class="spoil spoil--block"><span class="spoil__content">', $message);

  // Normalize citations
  $message = str_replace('<blockquote class="blockquote-jv">', '<blockquote class="quote">', $message);

  // Transform forums/topics links to JVForum links
  $message = preg_replace_callback('#<a href="(?P<url>https?://(www|m)\.jeuxvideo\.com/forums/(?P<mode>[0-9]+)-(?P<forum>[0-9]+)-(?P<topic>[0-9]+)-(?P<page>[0-9]+)-0-1-0-(?P<slug>[0-9a-z-]+)\.htm(\#(?P<hash>.+))?)"[^>]+>(?P<text>.+)</a>#Usi', function($matches) {
    $new_str = $matches[0];
    $path = '/' . $matches['forum'];
    if ($matches['topic']) {
      $path .= '/' . ($matches['mode'] == '1' ? '0' : '') . $matches['topic'] . '-' . $matches['slug'];
    }
    else {
      $path .= '-' . $matches['slug'];
    }
    if ($matches['page'] != 1) {
      $path .= '/' . $matches['page'];
    }
    if (substr($matches['hash'], 0, 5) == 'post_') {
      $path .= '#' . substr($matches['hash'], 5);
    }
    $new_str = str_replace('>' . $matches['text'] . '<', ' data-link-jvc="' . $matches['url'] . '">' . 'jvforum.fr' . $path . '<', $new_str);
    $new_str = str_replace('href="' . $matches['url'] . '"', 'href="' . $path . '"', $new_str);
    return $new_str;
  }, $message);

  // Transform profile links to JVForum links
  $message = preg_replace('#<a href="(http://www.jeuxvideo.com/profil/([^"]+)(?:\.html)?(?:\?mode=[a-z_]+)?)"([^>]*)>(.+)</a>#Usi', '<a data-link-jvc="$1" href="/@$2"$3>jvforum.fr/@$2</a>', $message);

  // Show YouTube thumbnails
  $message = preg_replace('#<a href="(https?://(?:[a-z]+\.)?youtube\.com/watch[^"]*(?:\?|&amp;)v=([a-zA-Z0-9-_]{11})([^"])*)"[^>]+>.+</a>#U', '<a class="youtube-link" href="$1" target="_blank"><img class="youtube-link__thumb" src="http://img.youtube.com/vi/$2/mqdefault.jpg" alt="$1"></a>', $message);
  $message = preg_replace('#<a href="(https?://youtu\.be/([a-zA-Z0-9-_]{11})([^"])*)"[^>]+>.+</a>#U', '<a class="youtube-link" href="$1" target="_blank"><img class="youtube-link__thumb" src="http://img.youtube.com/vi/$2/mqdefault.jpg" alt="$1"></a>', $message);

  // Show Dailymotion thumbnails
  $message = preg_replace('#<a href="(https?://www\.dailymotion\.com/video/([a-z0-9]+)[^"]+)"[^>]+>.+</a>#U', '<a class="youtube-link" href="$1" target="_blank"><img class="youtube-link__thumb" src="http://www.dailymotion.com/thumbnail/video/$2" alt="$1"></a>', $message);
  $message = preg_replace('#<a href="(https?://dai\.ly/([^"+]))"[^>]+>.+</a>#U', '<a class="youtube-link" href="$1" target="_blank"><img class="youtube-link__thumb)" src="http://www.dailymotion.com/thumbnail/video/$2" alt="$1"></a>', $message);

  // Format YouTube/Dailymotion thumbnails to JVF style
  $message = preg_replace('#<div class="player-contenu"><div class="embed-responsive embed-responsive-16by9"><iframe src="//www.youtube.com/embed/([^"]+)" allowfullscreen></iframe></div></div>#Usi', '<a class="youtube-link" href="http://youtu.be/$1" target="_blank"><img class="youtube-link__thumb" src="http://img.youtube.com/vi/$1/mqdefault.jpg" alt="http://youtu.be/$1"></a>', $message);
  $message = preg_replace('#<div class="player-contenu"><div class="embed-responsive embed-responsive-16by9"><iframe src="//www.dailymotion.com/embed/video/([^"]+)" allowfullscreen></iframe></div></div>#Usi', '<a class="youtube-link" href="http://dai.ly/$1" target="_blank"><img class="youtube-link__thumb" src="http://www.dailymotion.com/thumbnail/video/$1" alt="http://dai.ly/$1"></a>', $message);

  // Remove JVC's Vimeo thumbnails
  $message = preg_replace('#<div class="player-contenu"><div class="embed-responsive embed-responsive-16by9"><iframe src="//player.vimeo.com/video/([^"]+)" allowfullscreen></iframe></div></div>#Usi', '<a href="https://vimeo.com/$1" class="xXx" rel="nofollow" target="_blank">https://vimeo.com/$1</a>', $message);

  // Remove JVC's own videos thumbnails
  $message = preg_replace('#<div class="player-contenu">\s+<div class="embed-responsive embed-responsive-16by9">\s+<div class="embed-responsive-item" >\s+<div class="player-jv" id="player-jv-[0-9]+-[0-9]+" data-src="[^"]+">Chargement du lecteur vidéo...</div>\s+</div>\s+</div>\s+</div>#Usi', '<p><a href="http://www.jeuxvideo.com/___/forums/message/' . $id . '" class="xXx" target="_blank" title="http://www.jeuxvideo.com/___/forums/message/' . $id . '">Miniature vidéo sur JVC</a></p>', $message);

  // Normalize smileys
  $message = preg_replace('#<img src="//image\.jeuxvideo\.com/smileys_img/([^.]+)\.gif" alt="([^"]+)" data-def="SMILEYS" data-code="[^"]+" title="[^"]+" />#Usi', '<img class="smiley smiley--$1" src="//image.jeuxvideo.com/smileys_img/$1.gif" data-code="$2" title="$2" alt="$2">', $message);

  // Normalize stickers
  $message = preg_replace_callback('#<img class="img-stickers" src="http://jv.stkr.fr/p/(?P<id>[^"]+)"/>#Usi', function($matches) {
    global $stickers;

    $ret = $matches[0];

    $id = $matches['id'];
    $code = '';
    $shortcut = '';
    $unknown = true;
    foreach ($stickers as $stickers_category) {
      foreach ($stickers_category as $id_loop => $shortcut_loop) {
        if ($id == $id_loop) {
          $code = $shortcut_loop;
          $shortcut = ':' . $shortcut_loop . ':';
          $unknown = false;
        }
      }
    }
    if (!$shortcut) {
      $shortcut = '[[sticker:p/' . $id . ']]';
    }

    return '<img class="sticker ' . ($unknown ? 'sticker--unknown' : '') . '" src="' . ($unknown ? ('http://jv.stkr.fr/p3w/' . $id) : ('/images/stickers/small/' . $code . '.png')) . '" data-sticker-id="' . $id . '" data-code="' . $shortcut . '" title="' . $shortcut . '" alt="' . $shortcut . '">';
  }, $message);

  // Add target="_blank" to non-JVForum links
  $message = preg_replace_callback('#<a.*href="(?P<url>.*)".*>#Usi', function($matches) {
    $ret = $matches[0];
    $has_blank = (strpos($ret, 'target="_blank"') !== false) ? true : false;
    if (preg_match('#^(?:https?://' . $_SERVER['HTTP_HOST'] .')?/#Usi', $matches['url'])) {
      if ($has_blank) {
        return str_replace('target="_blank"', '', $ret);
      }
    }
    else {
      if (!$has_blank) {
        return str_replace('>', ' target="_blank">', $ret);
      }
    }
    return $ret;
  }, $message);

  // Remove some potential vulnerabilities
  $message = str_ireplace(['<style>', '<script>'], '', $message);

  return $message;
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
  while (false !== ($v = each($array))) {
    $max = $comp_func($v[1], $max) ? $v[1] : $max;
  }
  return $max;
}

function strip_matches($matches) {
  foreach ($matches as $k => $v) {
    if (is_int($k)) {
      unset($matches[$k]);
    }
  }
  return $matches;
}

function delay($f, &$t) {
  $t =  microtime(true) * 1000;
  $ret = $f();
  $t = microtime(true) * 1000 - $t;
  return $ret;
}

function generate_message_markup($message, $is_mod_active) {
  $mine = strcasecmp(isset($_COOKIE['pseudo']) ? $_COOKIE['pseudo'] : '', $message['pseudo']) == 0;
  $mine_modifier = $mine ? 'message--mine' : '';
  $pseudoLowercase = strtolower($message['pseudo']);
  $pseudoDeleted = strpos($pseudoLowercase, ' ') !== false;
  $authorLinkTag = $pseudoDeleted ? 'span' : 'a';
  $authorLinkClass = $pseudoDeleted ? 'message__byline-author-link--no-link' : 'js-profile';
  $avatar = $message['avatar'] ? $message['avatar'] : ('/images/triangles.php?s=' . $pseudoLowercase);
  $pseudo_modifier = $message['status'] != 'user' ? ('message__byline-author-pseudo--' . $message['status']) : '';
  $default_avatar_modifier = $message['avatar'] ? '' : 'message__byline-author-avatar-image--default';
  $date = relative_date_messages($message['dateRaw']);

  $actions = [];
  if ($is_mod_active && !$mine) {
    $actions[] = ['punish', 'Sanctionner', 'Sanction', '/sanctionner/' . $message['id'] . '?pseudo=' . $message['pseudo']];
  }
  if ($is_mod_active && !$mine && !$pseudoDeleted) {
    $actions[] = ['kick', 'Kicker', 'Kicker', '/kick/' . $message['pseudo'] . '?message_id=' . $message['id']];
  }
  if ($mine || $is_mod_active) {
    $actions[] = ['delete', 'Supprimer', 'Suppr.'];
  }
  if ($mine) {
    $actions[] = ['edit', 'Modifier'];
  }
  $actions[] = ['quote', 'Citer'];
  $nb_actions = count($actions);

  $markup = <<<MESSAGE
<div class="message {$mine_modifier} message-by--{$pseudoLowercase} message--nb-actions-{$nb_actions}" id="{$message['id']}" data-pseudo="{$message['pseudo']}" data-content-checksum="{$message['contentChecksum']}">
  <div class="message__actions message__actions--nb-{$nb_actions} message__ignorable">
MESSAGE;

  foreach ($actions as $action) {
    $markup .= '<' . (isset($action[3]) ? 'a href="' . $action[3] . '"' : 'span') . ' class="js-' . $action[0] . ' message__actions-action message__actions-action--' . $action[0] . '">' . $action[isset($action[2]) ? 2 : 1] . '</' . (isset($action[3]) ? 'a' : 'span') . '>';
  }

  $markup .= <<<MESSAGE
  </div>
  <div class="message__visible message__ignorable">
    <div class="message__byline">
      <div class="message__byline-author">
        <{$authorLinkTag} class="message__byline-author-link {$authorLinkClass}" href="/@{$pseudoLowercase}">
          <span class="message__byline-author-avatar"><img class="message__byline-author-avatar-image {$default_avatar_modifier}" src="{$avatar}"></span>
          <span class="message__byline-author-pseudo {$pseudo_modifier}" title="{$message['pseudo']}">{$message['pseudo']}</span>
        </{$authorLinkTag}>
      </div>
      <div class="message__byline-date">
        <span class="js-menu js-date message__byline-date-inline" title="{$message['dateRaw']}">{$date}</span>
      </div>
    </div><!--
    --><div class="js-content message__content">{$message['content']}</div>
    <div class="message__quick-actions">
MESSAGE;

  foreach ($actions as $action) {
    $markup .= ' <' . (isset($action[3]) ? 'a href="' . $action[3] . '"' : 'div') . ' class="js-' . $action[0] . ' message__quick-action message__quick-action--' . $action[0] . '" title="' . $action[1] . '"></' . (isset($action[3]) ? 'a' : 'div') . '>';
  }

  $markup .= <<<MESSAGE
    </div>
  </div>
  <div class="message__ignored-notice">{$message['pseudo']} <span class="message__ignored-notice_compact">ignoré</span><span class="message__ignored-notice_regular">parle mais se fait ignorer</span>. <strong class="message__ignored-notice_show-message-button">Voir le message</strong></div>
  <div class="message__end-anchor" id="after{$message['id']}"></div>
</div>
<script>liste_messages.push({$message['id']})</script>
MESSAGE;
  return $markup;
}

function generate_topic_pagination_markup($current_page, $last_page, $forum, $topic_id_array, $slug) {
  global $db, $jvc;

  extract($topic_id_array);

  $pages = [];

  if ($current_page > 4) {
    $pages[] = 1;
  }

  for ($i = max(1, $current_page - 3); $i < $current_page; $i++) {
    $pages[] = $i;
  }

  $pages[] = $current_page;

  if ($current_page != $last_page) {
    for ($i = $current_page + 1; $i < min($current_page + 4, $last_page); $i++) {
      $pages[] = $i;
    }

    $pages[] .= $last_page;
  }

  $visited_pages = $db->get_topic_visited_pages($jvc->user_id, $topic_id_old_or_new);

  $markup = '';
  $page_trail = $i > 1 ? "/{$i}" : '';
  foreach ($pages as $i) {
    $markup .= "        ";
    if ($i == $current_page) {
      $markup .= '<span class="pagination-topic__page"><span class="pagination-topic__page-link pagination-topic__page-link--active">' . $current_page . '</span></span>';
    }
    else {
      $markup .= '<span class="pagination-topic__page"><a class="pagination-topic__page-link ' . ($i == $current_page + 1 ? 'pagination-topic__page-link--next' : ($i == $last_page ? 'pagination-topic__page-link--last' : '')) . ' ' . (isset($visited_pages[$i]) ? 'pagination-topic__page-link--visited' : '') . '" href="/' . $forum . '/' . $topic_id_url_jvf . '-' . $slug . ($i > 1 ? ('/' . $i) : '') . '">' . $i . '</a></span>';
    }
  }

  return $markup;
}

function replace_stickers_shortcuts_to_code($message) {
  global $stickers;
  foreach ($stickers as $stickers_category) {
    foreach ($stickers_category as $id => $shortcut) {
      $message = str_ireplace(':' . $shortcut . ':', '[[sticker:p/' . $id . ']]', $message);
    }
  }
  return $message;
}

function adapt_message_to_post($message) {
  // Forum and topic links
  $message = preg_replace_callback('#https?://' . $_SERVER['HTTP_HOST'] . '/(?P<forum>[0-9]+)(/(?P<mode>0)?(?P<topic>[0-9]+))?-(?P<slug>[a-z0-9]+(-[a-z0-9]+)*)(/(?P<page>[0-9]+))?(\#(?P<message>[0-9]+))?(?:\#after[0-9]+)?#i', function($matches) {
    $mode = '0';
    if ($matches['topic'] !== '') {
      $mode = ($matches['mode'] === '0') ? '1' : '42';
    }
    return 'http://www.jeuxvideo.com/forums/' . $mode . '-' . $matches['forum'] . '-' . ($matches['topic'] === '' ? '0' : $matches['topic']) . '-'
    . (isset($matches['page']) ? $matches['page'] : '1') . '-0-1-0-' . $matches['slug'] . '.htm' . (isset($matches['message']) ? ('#post_'.$matches['message']) : '');
  }, $message);

  // Profile links
  $message = preg_replace_callback('#https?://' . $_SERVER['HTTP_HOST'] . '/@(?P<pseudo>[-_[\]0-9a-z]+)#i', function($matches) {
    $pseudo = strtolower($matches['pseudo']);
    $pseudo = str_replace(['[', ']'], ['%5B', '%5D'], $pseudo); // JVC bug: links stop being links when encountering one of these two characters
    return 'http://www.jeuxvideo.com/profil/' . $pseudo . '?mode=infos';
  }, $message);

  $message = replace_stickers_shortcuts_to_code($message);

  return $message;
}

function _setcookie($name, $value) {
  setcookie($name, $value, time() + 60 * 60 * 24 * 365 * 10, '/', null, false, true);
}

function removecookie($name) {
  setcookie($name, null, time() - 1, '/', null, false, true);
}

function generate_password($length = 32) {
  $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  $password = '';
  for ($i = 0; $i < $length; $i++) {
    $password .= $chars[mt_rand(0, strlen($chars) - 1)];
  }
  return $password;
}

function get_blacklist_person() {
  $person = isset($_COOKIE['blacklist']) ? $_COOKIE['blacklist'] : null;
  if ($person && !(ctype_alnum($person) && strlen($person) == 32)) {
    $person = null;
  }
  if (!$person) {
    $person = generate_password();
    _setcookie('blacklist', $person);
  }
  return $person;
}

function get_blacklist_from_db($person = false) {
  global $db;
  if (!$person) {
    $person = get_blacklist_person();
  }
  $person = get_blacklist_person();
  $results = $db->get_blacklist($person);
  if (!$results) {
    return false;
  }
  return [
    'blacklist' => $results['blacklist'],
    'is_fresh' => $results['is_fresh'],
  ];
}

function generate_blacklist_style($blacklist = []) {
  if (!$blacklist) {
    $results = get_blacklist_from_db();
    if ($results) {
      $blacklist = explode(',', $results['blacklist']);
    }
  }
  if (!$blacklist) {
    return '';
  }

  $hide = $show = '';
  for ($i = 0; $i < count($blacklist); $i++) {
    $pseudo = str_replace(['[', ']'], ['\\[', '\\]'], $blacklist[$i]);
    $hide .= ", .message-by--{$pseudo} .message__ignorable";
    $show .= ", .message-by--{$pseudo} .message__ignored-notice";
  }
  $hide = substr($hide, 2);
  $show = substr($show, 2);

  return "{$hide} { display: none; }\n{$show} { display: block; }";
}

function is_in_blacklist($pseudo) {
  global $blacklist;
  $pseudo = strtolower($pseudo);
  return in_array($pseudo, $blacklist);
}

function generate_favorites_forums_markup($favorites) {
  if (!$favorites) {
    return '';
  }

  $string = '';
  foreach ($favorites['forums'] as $forum) {
    $string .= '<a class="menu__item" href="/' . $forum[0] . '-' . $forum[1] . '">' . $forum[2] . '</a>';
  }
  if (!$string) {
    $string = '<span class="menu__item menu__item--blank-state">Aucun forum favori</span>';
  }
  return $string;
}

function generate_favorites_topics_markup($favorites) {
  if (!$favorites) {
    return '';
  }

  $string = '';
  foreach ($favorites['topics'] as $topic) {
    $string .= '<a class="menu__item" href="/' . $topic[1] . '/' . $topic[2] . '-' . $topic[3] . '">' . $topic[4] . '</a>';
  }
  if (!$string) {
    $string = '<span class="menu__item menu__item--blank-state">Aucun topic favori</span>';
  }
  return $string;
}

function is_forum_in_favorites($favorites, $wanted) {
  if (!$favorites) {
    return false;
  }

  foreach ($favorites['forums'] as $forum) {
    if ($forum[0] == $wanted) {
      return true;
    }
  }
  return false;
}

function is_topic_in_favorites($favorites, $wanted) {
  if (!$favorites) {
    return false;
  }

  foreach ($favorites['topics'] as $topic) {
    if ($topic[0] == $wanted) {
      return true;
    }
  }
  return false;
}

function halt($message = 0) {
  http_response_code(500);
  exit(htmlentities($message));
}

function get_favorites_sum($favorites) {
  return sha1(json_encode($favorites));
}

function set_toast_for_next_page($message) {
  setcookie('toast', base64_encode(utf8_decode($message)), time() + 60 * 60 * 24 * 365 * 10, '/');
}

function add_javascript_after_files($script) {
  global $javascripts_after_files;
  $javascripts_after_files[] = $script;
}
