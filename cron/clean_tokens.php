<?php
require '../config.php';
require '../public/Db.php';

(new Db())->clean_tokens();
