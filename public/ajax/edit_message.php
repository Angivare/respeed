<?php
require 'common.php';

arg('id', 'msg', 'form', 'ccode');
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
