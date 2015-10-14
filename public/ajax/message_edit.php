<?php
require 'common.php';

arg('id_message', 'msg', 'form', 'ccode');

if ($id_message && $msg && $form) {
  $msg = adapt_message_to_post($msg);

  echo json_encode([
    'rep' => $jvc->edit_finish($id_message, $msg, $form, $ccode),
    'err' => $jvc->err(),
  ]);
}
else if ($id_message) {
  echo json_encode([
    'rep' => $jvc->edit_req($id_message),
    'err' => $jvc->err(),
  ]);
}
