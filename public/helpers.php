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

function adapt_html($str) {
  return jvcare($str);
}

function jvcare($str) {
  return preg_replace_callback('#<span class="JvCare ([0-9A-F]+)" [^>]+>([^<]*(?:<i></i><span>[^<]+</span>)?[^<]+)</span>#Usi', function ($matches) {
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
  if (strpos($str_date, '/') !== false) {
    // Convertir en format US pour strtotime
    $array_date = explode('/', $str_date);
    $str_date = $array_date[1] . '/' . trim($array_date[0]) . '/' . $array_date[2];
  }
  return relative_date_timestamp(strtotime($str_date));
}

function relative_date_messages($str_date) {
  global $mois_jvc;
  // Convertir en format US pour strtotime
  $array_date = explode(' ', $str_date);
  $str_date = $mois_jvc[$array_date[1]] . '/' . $array_date[0] . '/' . $array_date[2] . ' ' . $array_date[4];

  return relative_date_timestamp(strtotime($str_date));
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
