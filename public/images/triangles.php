<?php

if (!class_exists('Imagick')) {
  header('Content-Type: image/jpeg');
  echo file_get_contents('avatar-default.jpg');
  exit;
}

class Palette {
  public function __construct() {
    $d = [];

    $dist = 30;
    $off  = 0;

    $h_r = 360*0.33;

    $scheme = mt_rand(0, 0);

    $d[] = [ mt_rand(0, 360), 255*mt_rand(60, 100)/100, 255*mt_rand(40, 60)/100 ];

    switch($scheme) {
      /* Additional color scheme types implementable here */

      case 0: //mono
        foreach([-0.5, -0.3, 0.3, 0.5] as $r)
          $d[] = [ $d[0][0], $d[0][1], $d[0][2] + $d[0][2]*$r ];
        //Additional high lighted hue-altered color
        $d[] = [ $d[0][0] + mt_rand(-$h_r, $h_r), $d[0][1], $d[0][2] * (1+(mt_rand(70, 100))/100) ];
      break;
    }

    $this->d = [];
    foreach($d as $hsl)
      $this->d[] = self::from_hsl($hsl);
  }

  public function get() {
    return $this->d[mt_rand(0, count($this->d)-1)];
  }

  private static function from_hsl($hsl) {
    $h = array_shift($hsl);
    while($h > 360) $h -= 360;
    while($h < 0) $h += 360;
    $s = array_shift($hsl);
    if($s < 0) $s = 0;
    if($s > 255) $s = 255;
    $l = array_shift($hsl);
    if($l < 0) $l = 0;
    if($l > 255) $l = 255;

    return new ImagickPixel("hsl($h, $s, $l)");
  }
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
  $s = strtolower($s);
  $ret = 0;
  for($n = 0; $n < strlen($s); $n++)
    $ret += (1 + char_to_int($s[$n])) * pow(41, $n);
  return (int) fmod($ret, PHP_INT_MAX);
}

function draw($x, $y, $o) {
  global $_w, $_h, $imd, $pal;

  $color = $pal->get();
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

if($s !== 0)
  mt_srand(str_to_int($s));

$pal = new Palette();

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
