#!/usr/bin/env php
<?php
echo "Searching for abandonned files...";
$application = (include(dirname(__FILE__).'/../../application.php'))();
call_user_func_array(array($application->getService('AbandonnedFileSearchWorker'), 'work'), array());










