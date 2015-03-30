<?php
require 'common.php';

$id = isset($_POST['id']) ? $_POST['id'] : FALSE;
$msg = isset($_POST['msg']) ? $_POST['msg'] : FALSE;
$form = isset($_POST['form']) ? $_POST['form'] : FALSE;
$ccode = isset($_POST['ccode']) ? $_POST['ccode'] : '';
$jvc = new Jvc();

if($id && $msg && $form)
  echo json_encode([
    'rep' => $jvc->edit_finish($id, $msg, $form, $ccode),
    'err' => $jvc->err()
  ]);
else if($id)
  echo json_encode([
    'rep' => $jvc->edit_req($id),
    'err' => $jvc->err()
  ]);
