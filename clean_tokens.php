<?php
require 'config.php';
require 'public/db.php';

(new Db())->clean_tokens();
