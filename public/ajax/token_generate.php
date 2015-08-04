<?php
require 'common.php';

$auth->discard($hash);
echo json_encode(['rep' => $auth->generate(), 'err' => 'Indéfinie']);
