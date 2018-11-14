<?php

require_once(dirname(__FILE__).'/autoload.php');


return function(){
    
    $application = new \PhpApplicationFront\SugarloafApplication(array(
      'dependencyConfigurationFolder' => dirname(__FILE__).'/DependencyConfiguration',
      'templateFolder' => dirname(__FILE__).'/Templates',
      'routeConfigurationFolder' => dirname(__FILE__).'/RouteConfiguration'
    ));
    
    return $application;
    
};
