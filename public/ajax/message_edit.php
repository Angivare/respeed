<?php
require 'common.php';

arg('id_message', 'msg', 'form', 'ccode');
$jvc = new Jvc();

if($id_message && $msg && $form)
  echo json_encode([
    'rep' => $jvc->edit_finish($id_message, $msg, $form, $ccode),
    'err' => $jvc->err()
  ]);
else if($id_message)
  echo json_encode([
    'rep' => $jvc->edit_req($id_message),
    'err' => $jvc->err()
  ]);
