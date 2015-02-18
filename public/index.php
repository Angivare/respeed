<?php
ob_start();
require 'views/index.php';
$body = ob_get_contents();
ob_end_clean();
require 'views/layout.php';
