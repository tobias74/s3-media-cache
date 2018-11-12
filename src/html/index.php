<?php

ini_set("session.save_handler","redis");
session_save_path("tcp://".$_ENV['TOBIGA_REDIS_HOST'].":6379");


$application = (include(dirname(__FILE__).'/../application/application.php'))();

$application->run();






