<?php
require '../Jvc.php';

$url = isset($_POST['url']) ? $_POST['url'] : FALSE;
$title = isset($_POST['title']) ? $_POST['title'] : FALSE;
$msg = isset($_POST['msg']) ? $_POST['msg'] : FALSE;
$form = isset($_POST['form']) ? $_POST['form'] : FALSE;
$ccode = isset($_POST['ccode']) ? $_POST['ccode'] : '';
$jvc = new Jvc();
if($url && $msg && $form)
  echo json_encode([
    'rep' => $jvc->post_topic_finish($url, $title, $msg, $form, '', [], $ccode),
    'err' => $jvc->err() == 'Indéfinie' ? false : $jvc->err()
  ]);
else if($url)
  echo json_encode([
    'rep' => $jvc->post_topic_req($url),
    'err' => $jvc->err() == 'Indéfinie' ? false : $jvc->err()
  ]);
