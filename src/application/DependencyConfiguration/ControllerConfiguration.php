<?php

return function($dm,$config){
    
    $dm->registerService('ControllerFacade','\Zeitfaden\Facade')
       ->addManagedDependency('MediaCacheService', 'CachedMediaService')
       ->addManagedDependency('ElasticSearchService', 'ElasticSearchService')
       ->addManagedDependency('StationRepository', 'StationRepository')
       ->addManagedDependency('GroupRepository', 'GroupRepository')
       ->addManagedDependency('UserRepository', 'UserRepository')
       ->addManagedDependency('UserSessionRecognizer', 'UserSessionRecognizer')
       ->addUnmanagedInstance('Config', $config);


    $dm->registerService('SessionFacade','\Zeitfaden\SessionFacade')
       ->addManagedDependency('Facade', 'ControllerFacade');

    $dm->registerService('MediaHelper','\PhpApplicationFront\MediaHelper')
      ->addManagedDependency('FileService', 'S3ServiceForOriginalFiles')
      ->addManagedDependency('MediaCacheService', 'CachedMediaService');


    $dm->registerService('WebIndexController','\Zeitfaden\Controller\WebIndexController')
      ->addManagedDependency('FileSendingStrategy', 'ReverseProxyWithNginx')
      ->addManagedDependency('MediaHelper', 'MediaHelper')
      ->addManagedDependency('FileService', 'S3ServiceForOriginalFiles')
      ->addManagedDependency('MediaCacheService', 'CachedMediaService')
      ->addManagedDependency('Profiler', 'PhpProfiler')
      ->addUnmanagedInstance('GoogleApiKey', $config['TOBIGA_GOOGLE_API_KEY'])
      ->addUnmanagedInstance('Auth0Callback', $config['TOBIGA_AUTH0_CALLBACK'])
      ->addUnmanagedInstance('Auth0ClientId', $config['TOBIGA_AUTH0_CLIENT_ID'])
      ->addUnmanagedInstance('Auth0Domain', $config['TOBIGA_AUTH0_DOMAIN'])
      ->addManagedDependency('Facade', 'ControllerFacade')
      ->addManagedProvider('SessionFacadeProvider', 'SessionFacade')
      ->addManagedDependency('UserSessionRecognizer', 'UserSessionRecognizer');
    
    
    $dm->registerService('WebGroupController','\Zeitfaden\Controller\WebGroupController')
      ->addManagedDependency('Profiler', 'PhpProfiler')
      ->addUnmanagedInstance('GoogleApiKey', $config['TOBIGA_GOOGLE_API_KEY'])
      ->addUnmanagedInstance('Auth0Callback', $config['TOBIGA_AUTH0_CALLBACK'])
      ->addUnmanagedInstance('Auth0ClientId', $config['TOBIGA_AUTH0_CLIENT_ID'])
      ->addUnmanagedInstance('Auth0Domain', $config['TOBIGA_AUTH0_DOMAIN'])
      ->addManagedDependency('Facade', 'ControllerFacade')
      ->addManagedProvider('SessionFacadeProvider', 'SessionFacade')
      ->addManagedDependency('UserSessionRecognizer', 'UserSessionRecognizer');
    


    $dm->registerService('WebStationController','\Zeitfaden\Controller\WebStationController')
       ->addUnmanagedInstance('AwsConfig', array(
         'region' => $config['TOBIGA_S3_REGION_FOR_ORIGINAL_FILES'],
         'bucket' => $config['TOBIGA_S3_BUCKET_NAME_FOR_ORIGINAL_FILES'],
         'endpoint' => $config['TOBIGA_S3_ENDPOINT_FOR_ORIGINAL_FILES'],
         'key' => $config['TOBIGA_S3_KEY_FOR_ORIGINAL_FILES'],
         'secret' => $config['TOBIGA_S3_SECRET_FOR_ORIGINAL_FILES'],
         'faceCollectionId' => $config['TOBIGA_REKOGNITION_COLLECTION_ID']
       ))
      ->addManagedDependency('Profiler', 'PhpProfiler')
      ->addUnmanagedInstance('GoogleApiKey', $config['TOBIGA_GOOGLE_API_KEY'])
      ->addUnmanagedInstance('Auth0Callback', $config['TOBIGA_AUTH0_CALLBACK'])
      ->addUnmanagedInstance('Auth0ClientId', $config['TOBIGA_AUTH0_CLIENT_ID'])
      ->addUnmanagedInstance('Auth0Domain', $config['TOBIGA_AUTH0_DOMAIN'])
      ->addManagedDependency('Facade', 'ControllerFacade')
      ->addManagedDependency('MediaHelper', 'MediaHelper')
      ->addManagedDependency('PlacesGeocoderClient', 'PlacesGeocoderClient')
      ->addManagedProvider('SessionFacadeProvider', 'SessionFacade')
      ->addManagedDependency('ElasticSearchService', 'ElasticSearchService')
      ->addManagedDependency('S3Service', 'S3ServiceForOriginalFiles')
      ->addManagedDependency('TimezoneCache', 'TimezonesGeocoderClient')
      ->addManagedDependency('MediaCacheService', 'CachedMediaService')
      ->addManagedProvider('ResumableUploadProcessorProvider', 'ResumableUploadProcessor')
      ->addManagedDependency('QueryEngine', 'QueryEngine')
      ->addManagedDependency('UserSessionRecognizer', 'UserSessionRecognizer');




    $dm->registerService('StationAccessResolver','\Zeitfaden\Controller\StationAccessResolver')
      ->addManagedProvider('SessionFacadeProvider', 'SessionFacade')
      ->addManagedDependency('Session', 'SymfonySession')
      ->addManagedDependency('UserSessionRecognizer', 'UserSessionRecognizer');

    $dm->registerService('ReverseProxyWithNginx','\PhpApplicationFront\FileSendingStrategies\ReverseProxyWithNginx');


    $dm->registerService('MediaActionController','\PhpApplicationFront\MediaActionController')
      ->addManagedDependency('FileSendingStrategy', 'ReverseProxyWithNginx')
      ->addManagedDependency('MediaHelper', 'MediaHelper')
      ->addManagedDependency('FileService', 'S3ServiceForOriginalFiles')
      ->addManagedDependency('MediaCacheService', 'CachedMediaService')
      ->addManagedDependency('AccessResolver', 'StationAccessResolver');

};
