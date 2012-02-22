<?php

// only CLI from our server
if (php_sapi_name() != 'cli') {
    die("You are not using CLI PHP\n");
}

$config = require 'config.php';
require __DIR__.'/../lib/GithubApi_v3/Api.class.php';
require __DIR__.'/../lib/JsonDB.class.php';