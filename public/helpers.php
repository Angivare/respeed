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
  'astronomique',
  'brillant',
  'épique',
  'exceptionnel',
  'extraordinaire',
  'épatant',
  'étonnant',
  'éblouissant',
  'éclatant',
  'excellent',
  'fantastique',
  'formidable',
  'fabuleux',
  'frappant',
  'glorieux',
  'héroïque',
  'homérique',
  'incroyable',
  'interloquant',
  'inimaginable',
  'inoubliable',
  'irrésistible',
  'incomparable',
  'légendaire',
  'merveilleux',
  'mirifique',
  'monumental',
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
  'transcendant',
];

function adapt_html($message, $date) {
  // Mise en forme édition
  preg_match('#</div><div class="info-edition-msg">\s+Message édité le (?P<date>.+) par\s+<a href="(//www.jeuxvideo.com/profil/(?P<pseudo>.+)\?mode=infos)?" target="_blank">[^<]*</a>#Usi', $message, $matches_edit);
  if ($matches_edit) {
    $message = str_replace($matches_edit[0], '', $message);
    $message .= '<p class="edit-mention">Modifié après ' . edit_date_difference($date, $matches_edit['date']) . '</p>';
  }

  // JVCare
  $message = jvcare($message);
  
  // Vire la signature qui apparaît parfois
  $pos_signature = strpos($message, '</div><div class="signature-msg  text-enrichi-forum ">');
  if ($pos_signature !== false) {
    $message = substr($message, 0, $pos_signature);
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

function relative_date_timestamp($timestamp) {
  global $jours, $mois;
  $diff = time() - $timestamp;
  
  // Moins d’une minute
  if ($diff < 60)
    return $diff . ' s';
  // Moins d’une heure
  if ($diff < 60 * 60)
    return floor($diff / 60) . ' m ' . ($diff % 60) . ' s';
  // Moins d’un jour
  if ($diff < 60 * 60 * 24)
    return floor($diff / 60 / 60) . ' h ' . floor(($diff % (60 * 60)) / 60) . ' m';
  // De cette année
  if (date('Y', $timestamp) == date('Y')) {
    return date('j', $timestamp) . ' ' . $mois[date('n', $timestamp) - 1];
  }
  return date('j', $timestamp) . ' ' . $mois[date('n', $timestamp) - 1] . ' ' . date('Y', $timestamp);

  return '??';
}

function relative_date_topic_list($str_date) {
  return relative_date_timestamp(date_topic_list_to_timestamp($str_date));
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

function strip_matches(&$matches) {
  foreach($matches as $k => $v)
    if(is_int($k))
      unset($matches[$k]);
}
