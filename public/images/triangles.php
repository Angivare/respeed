<?php

if (!class_exists('Imagick')) {
  header('Content-Type: image/jpeg');
  echo file_get_contents('avatar-default.jpg');
  exit;
}

class Palette {
  public function __construct() {
    $d = [];
    $b = [
      mt_rand(0, 360),
      255 * mt_rand(80, 90) / 100,
      255 * mt_rand(40, 60) / 100,
    ];

    switch (mt_rand(0, 1)) {
      /* Additional color scheme types implementable here */

      case 0: //mono, total 6 colors
        foreach (self::l(0.5, 0.66, 1.0, 1.33, 1.5) as $w) {
          $d[] = self::blend($b, $w);
        }
        //Additional high lighted hue-altered color
        $h_d = mt_rand(90, 120) * (mt_rand(0,1) ? 1 : -1);
        $d[] = [$b[0] + $h_d, $b[1], mt_rand(225, 255)];
        break;
      case 1: //analogous, total 6 colors
        $t = [];
        foreach ([-30, -15, 0, 15, 30] as $off) {
          $t[] = [$b[0] + $off + mt_rand(-5, 5), $b[1], $b[2]];
        }
        foreach ([[0.66, 1.8], [1.33, 1.0], [1.33, 0.66], [0.66, 1.33]] as $w) {
          $hsl = array_splice($t, mt_rand(0, count($t)-1), 1)[0];
          $d[] = self::blend($hsl, $w);
        }
        foreach ($t as $c) {
          $d[] = $c;
        }
        break;
    }


    $this->d = [];
    foreach ($d as $hsl) {
      $this->d[] = self::from_hsl($hsl);
    }
  }

  public function get() {
    return $this->d[mt_rand(0, count($this->d)-1)];
  }

  private static function blend($hsl, $w) {
    foreach ($w as $k => $v) {
      $hsl[$k+1] = $hsl[$k+1] * $v;
    }
    return $hsl;
  }

  private static function s() {
    return self::from_comp(0, func_get_args());
  }

  private static function l() {
    return self::from_comp(1, func_get_args());
  }

  private static function from_comp($i, $val) {
    $ret = [];
    foreach ($val as $v) {
      $r = [1.0, 1.0];
      $r[$i] = $v;
      $ret[] = $r;
    }
    return $ret;
  }

  private static function from_hsl($hsl) {
    $h = array_shift($hsl);
    while ($h > 360) {
      $h -= 360;
    }
    while ($h < 0) {
      $h += 360;
    }
    $s = array_shift($hsl);
    if ($s < 0) {
      $s = 0;
    }
    if ($s > 255) {
      $s = 255;
    }
    $l = array_shift($hsl);
    if ($l < 0) {
      $l = 0;
    }
    if ($l > 255) {
      $l = 255;
    }

    return new ImagickPixel("hsl({$h}, {$s}, {$l})");
  }
}

function char_to_int($c) {
  switch ($c) {
    case '[': return 0;
    case ']': return 1;
    case '-': return 2;
    case '_': return 3;
  }
  $n = ord($c);
  if (ord('a') <= $n && $n <= ord('z')) {
    return $n - ord('a') + 4;
  }
  else { //is digit
    return $n - ord('0') + 26 + 4;
  }
}

function str_to_int($s) {
  $s = strtolower($s);
  $ret = 0;
  for ($n = 0; $n < strlen($s); $n++) {
    $ret += (1 + char_to_int($s[$n])) * pow(41, $n);
  }
  return (int)fmod($ret, PHP_INT_MAX);
}

function draw($x, $y, $o) {
  global $_w, $_h, $imd, $pal;

  $color = $pal->get();
  $imd->setStrokeColor($color);
  $imd->setFillColor($color);

  if ($o)
    $p = [
      ['x' => $x, 'y' => $y + $_h],
      ['x' => $x + $_w, 'y' => $y + $_h],
      ['x' => $x + $_w / 2.0, 'y' => $y],
    ];
  else
    $p = [
      ['x' => $x, 'y' => $y],
      ['x' => $x + $_w, 'y' => $y],
      ['x' => $x + $_w / 2.0, 'y' => $y + $_h],
    ];

  $imd->polygon($p);
}

$s = isset($_GET['s']) ? $_GET['s'] : 0;
$q = 2; //linear quality factor

$w = $q * 40;
$h = $w;

$_w = ($w / 2.0) / sqrt(3.0 / 4.0);
$_h = $_w * sqrt(3.0 / 4.0);

if ($s !== 0) {
  mt_srand(str_to_int($s));
}

$pal = new Palette();

$imd = new ImagickDraw();
$imd->setStrokeWidth(0);

for ($y = 0, $a = false; $y < $h; $y += $_h) {
  $a = !$a;
  for ($x = -$_w / 2.0 - ($w % ($_w / 2.0)) / 2.0, $b = $a; $x < $w; $x += $_w / 2.0) {
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
