<?php
session_start();
require '../Jvc.php';

$url = isset($_POST['url']) ? $_POST['url'] : FALSE;
$id = isset($_POST['id']) ? $_POST['id'] : FALSE;
$msg = isset($_POST['msg']) ? $_POST['msg'] : FALSE;
$form = isset($_POST['form']) ? $_POST['form'] : FALSE;
$ccode = isset($_POST['ccode']) ? $_POST['ccode'] : '';
$jvc = new Jvc();

if($url && $id && $msg && $form)
  echo json_encode([
    'rep' => $jvc->edit_finish($url, $id, $msg, $form, $ccode),
    'err' => $jvc->err()
  ]);
else if($url && $id)
  echo json_encode([
    'rep' => $jvc->edit_req($url, $id),
    'err' => $jvc->err()
  ]);