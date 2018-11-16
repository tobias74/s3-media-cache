<?php

return function($dm,$config){
    

    $dm->registerService('WebIndexController','\Zeitfaden\Controller\WebIndexController')
      ->addManagedDependency('FileSendingStrategy', 'ReverseProxyWithNginx')
      ->addManagedDependency('FileService', 'S3ServiceForOriginalFiles')
      ->addManagedDependency('MediaCacheService', 'CachedMediaService')
      ->addManagedDependency('Profiler', 'PhpProfiler')
      ->addUnmanagedInstance('GoogleApiKey', $config['TOBIGA_GOOGLE_API_KEY'])
      ->addUnmanagedInstance('Auth0Callback', $config['TOBIGA_AUTH0_CALLBACK'])
      ->addUnmanagedInstance('Auth0ClientId', $config['TOBIGA_AUTH0_CLIENT_ID'])
      ->addUnmanagedInstance('Auth0Domain', $config['TOBIGA_AUTH0_DOMAIN']);


    $dm->registerService('ReverseProxyWithNginx','\PhpApplicationFront\FileSendingStrategies\ReverseProxyWithNginx');


};
