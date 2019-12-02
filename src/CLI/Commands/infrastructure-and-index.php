#!/usr/bin/env php
<?php
echo "Generating Index in Elasticsearch...";
$application = (include(dirname(__FILE__).'/../../application.php'))();

try
{
    call_user_func_array(array($application->getService('CreateInfrastructureWorker'), 'work'), array());
}
catch (\Elasticsearch\Common\Exceptions\BadRequest400Exception $e)
{
    error_log('Notice: Index already existed');
}

call_user_func_array(array($application->getService('IndexAllStationsWorker'), 'work'), array());


