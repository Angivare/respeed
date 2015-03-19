<?php
require '../Jvc.php';

$url = isset($_POST['url']) ? $_POST['url'] : FALSE;
$msg = isset($_POST['msg']) ? $_POST['msg'] : FALSE;
$form = isset($_POST['form']) ? $_POST['form'] : FALSE;
$ccode = isset($_POST['ccode']) ? $_POST['ccode'] : '';
$jvc = new Jvc();
if($url && $msg && $form)
  echo json_encode([
    'rep' => $jvc->post_msg_finish($url, $msg, $form, $ccode),
    'err' => $jvc->err() == 'Indéfinie' ? false : $jvc->err()
  ]);
else if($url)
  echo json_encode([
    'rep' => $jvc->post_msg_req($url),
    'err' => $jvc->err() == 'Indéfinie' ? false : $jvc->err()
  ]);
