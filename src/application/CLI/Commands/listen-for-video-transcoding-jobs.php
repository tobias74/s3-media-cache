#!/usr/bin/env php
<?php
die('not now .... 786985876598070978');
$application = (include(dirname(__FILE__).'/../../application.php'))();
call_user_func_array(array($application->getService('ListenForVideosWorker'), 'work'), array());
