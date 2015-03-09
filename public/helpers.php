<?php

$jours = ['lundi', 'mardi', 'mercr', 'jeudi', 'vendr', 'samedi', 'dim'];
$mois = ['janv', 'fév', 'mars', 'avr', 'mai', 'juin', 'juil', 'août', 'sept', 'oct', 'nov', 'déc'];

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
  // Moins de dix minutes
  if ($diff < 60 * 10)
    return floor($diff / 60) . ' m ' . ($diff % 60) . ' s';
  // Moins d’une heure
  if ($diff < 60 * 60)
    return floor($diff / 60) . ' m';
  // Moins d’un jour
  if ($diff < 60 * 60 * 24)
    return floor($diff / 60 / 60) . ' h ' . floor(($diff % (60 * 60)) / 60) . ' m';
  // De cette année
  if (date('Y', $timestamp) == date('Y')) {
    return $jours[date('N', $timestamp)] . ' ' . date('j', $timestamp) . ' ' . $mois[date('n', $timestamp) + 1];
  }
  return date('j', $timestamp) . ' ' . $mois[date('n', $timestamp) - 1] . ' ' . date('Y', $timestamp);
  
  return '??';
}

function relative_date_topic($str_date) {
  if (strpos($str_date, '/') !== false) {
    // Convertir en format US pour strtotime
    $array_date = explode('/', $str_date);
    $str_date = $array_date[1] . '/' . trim($array_date[0]) . '/' . $array_date[2];
  }
  return relative_date_timestamp(strtotime($str_date));
}
