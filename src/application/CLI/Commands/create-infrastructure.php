#!/usr/bin/env php
<?php
echo "Generating Index in Elasticsearch...";
$application = (include(dirname(__FILE__).'/../../application.php'))();
call_user_func_array(array($application->getService('CreateInfrastructureWorker'), 'work'), array());

return 0;

