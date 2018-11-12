#!/usr/bin/env php
<?php
$application = (include(dirname(__FILE__).'/../../application.php'))();
call_user_func_array(array($application->getService('ListenForVideosWorker'), 'work'), array());
