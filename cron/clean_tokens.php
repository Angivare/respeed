<?php
require dirname(__FILE__) . '/../config.php';
require dirname(__FILE__) . '/../public/Db.php';

(new Db())->clean_tokens();
