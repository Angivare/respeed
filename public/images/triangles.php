<?php

if (!class_exists('Imagick')) {
  header('Content-Type: image/jpeg');
  echo file_get_contents('avatar-default.jpg');
  exit;
}

function char_to_int($c) {
  switch($c) {
    case '[': return 0;
    case ']': return 1;
    case '-': return 2;
    case '_': return 3;
  }
  $n = ord($c);
  if(ord('a') <= $n && $n <= ord('z'))
    return $n - ord('a') + 4;
  else //is digit
    return $n - ord('0') + 26 + 4;
}

function str_to_int($s) {
  $ret = 0;
  for($n = 0; $n < strlen($s); $n++)
    $ret += (1 + char_to_int($s[$n])) * pow(41, $n);
  return $ret;
}

function random_color() {
  global $colors;
  return $colors[mt_rand(0, count($colors)-1)];
}

function draw($x, $y, $o) {
  global $_w, $_h, $imd;

  $color = random_color();
  $imd->setStrokeColor($color);
  $imd->setFillColor($color);

  if($o)
    $p = [
      ['x' => $x, 'y' => $y + $_h],
      ['x' => $x + $_w, 'y' => $y + $_h],
      ['x' => $x + $_w/2.0, 'y' => $y]
    ];
  else
    $p = [
      ['x' => $x, 'y' => $y],
      ['x' => $x + $_w, 'y' => $y],
      ['x' => $x + $_w/2.0, 'y' => $y + $_h]
    ];

  $imd->polygon($p);
}

$s = isset($_GET['s']) ? $_GET['s'] : 0;
$q = 2; //linear quality factor

$w = $q * 40;
$h = $w;

$_w = ($w/2.0) / sqrt(3.0/4.0);
$_h = $_w * sqrt(3.0/4.0);

$colors = [
  '#FA6E69',
  '#FFCE74',
  '#97D17A',
  '#4C8DA6',
  '#5B608C'
];
if($s !== 0)
  mt_srand(str_to_int(strtolower($s)));

$imd = new ImagickDraw();
$imd->setStrokeWidth(0);

for($y = 0, $a = FALSE; $y < $h; $y += $_h) {
  $a = !$a;
  for($x = -$_w/2.0-($w%($_w/2.0))/2.0, $b = $a; $x < $w; $x += $_w/2.0) {
    $b = !$b;
    draw($x, $y, $b);
  }
}

header('Content-Type: image/png');
$im = new Imagick();
$im->setFormat('png');
$im->newImage($w, $h, new ImagickPixel('white'));
$im->drawImage($imd);
echo $im->getImageBlob();