<?php
require 'common.php';

require '../helpers.php';

arg('id_message', 'msg', 'form', 'ccode');

if($id_message && $msg && $form) {
  $msg = convert_stickers($msg);

  echo json_encode([
    'rep' => $jvc->edit_finish($id_message, $msg, $form, $ccode),
    'err' => $jvc->err()
  ]);
}
else if($id_message) {
  echo json_encode([
    'rep' => $jvc->edit_req($id_message),
    'err' => $jvc->err()
  ]);
}
