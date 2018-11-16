<?php

require_once(dirname(__FILE__).'/autoload.php');


return function(){
    
    $application = new \PhpApplicationFront\SugarloafApplication(array(
      'dependencyConfigurationFolder' => dirname(__FILE__).'/DependencyConfiguration',
      'routeConfigurationFolder' => dirname(__FILE__).'/RouteConfiguration'
    ));
    
    return $application;
    
};
