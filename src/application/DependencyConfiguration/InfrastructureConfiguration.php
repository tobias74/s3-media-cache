<?php

return function($dm,$config){

    $serverConfig = array(
      'mongoDbHost' => $config['TOBIGA_MONGO_DB_HOST'],
      'mongoDbName' => $config['TOBIGA_MONGO_DB_NAME'],
      'rabbitMqHost' => $config['TOBIGA_RABBIT_MQ_HOST'],
      'rabbitQueueName' => $config['TOBIGA_RABBIT_MQ_QUEUE_NAME'],
      'auth0Domain' => $config['TOBIGA_AUTH0_DOMAIN'],
      'auth0ClientId' => $config['TOBIGA_AUTH0_CLIENT_ID'],
      'auth0Secret' => $config['TOBIGA_AUTH0_SECRET'],
      'auth0Callback' => $config['TOBIGA_AUTH0_CALLBACK'],
    );


    $dm->registerService('ZeitfadenRedisService','\PhpNamespacedRedisClient\RedisService')
      ->addManagedDependency('RedisClient', 'RedisClient')
      ->addUnmanagedInstance('Namespace', $config['TOBIGA_APPLICATION_ID']);


    $dm->registerService('RedisClient', '\Redis')
       ->addCallback(function($instance) use($config) {
         $instance->connect($config['TOBIGA_REDIS_HOST']);
       });

  
  
    $dm->registerService('NativeSessionStorage','\Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage')
       ->appendUnmanagedParameter(array())
       ->appendManagedParameter('RedisSessionHandler');

    $dm->registerService('SymfonySession','\Symfony\Component\HttpFoundation\Session\Session')
       ->appendManagedParameter('NativeSessionStorage');

    $dm->registerService('RedisSessionHandler','\Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler')
       ->appendManagedParameter('RedisClient');




  
  

    // video transcoder
    $dm->registerSingleton('ListenForVideosWorker','\Zeitfaden\CLI\ListenForVideosWorker')
      ->addManagedDependency('CachedMediaService', 'CachedMediaService');

    //indexing
    $dm->registerService('CreateInfrastructureWorker','\Zeitfaden\CLI\CreateInfrastructureWorker')
      ->addManagedDependency('S3ServiceForOriginalFiles', 'S3ServiceForOriginalFiles')
      ->addManagedDependency('S3ServiceForTranscodedFiles', 'S3ServiceForTranscodedFiles')
      ->addManagedDependency('ElasticSearchService', 'ElasticSearchService');

    $dm->registerService('IndexAllStationsWorker','\Zeitfaden\CLI\IndexAllStationsWorker')
      ->addManagedDependency('ControllerFacade', 'ControllerFacade');

    // Housekeeper
    $dm->registerService('AbandonnedFileSearchWorker','\Zeitfaden\CLI\AbandonnedFileSearchWorker')
      ->addManagedDependency('S3ServiceForOriginalFiles', 'S3ServiceForOriginalFiles')
      ->addManagedDependency('S3ServiceForTranscodedFiles', 'S3ServiceForTranscodedFiles')
      ->addManagedDependency('StationRepository', 'StationRepository')
      ->addManagedDependency('MediaCacheService', 'CachedMediaService');


    $dm->registerService('ResumableUploadProcessor','\PhpResumableUpload\ResumableUploadProcessor');


  	$dm->registerSingleton('SqlProfiler','\Tiro\Profiler');
  
  	$dm->registerSingleton('PhpProfiler','\Tiro\Profiler');


    $dm->registerService('CachedMediaService','\PhpMediaCache\CachedMediaService')
      ->appendUnmanagedParameter($serverConfig)
      ->addManagedDependency('CacheFileService','S3ServiceForTranscodedFiles');



/*
    $depList = $dm->registerDependencyManagedService(new \SugarLoaf\Service\ManagedService('Tobias3','Tobias3'));

    $nestedParameterArray = new \SugarLoaf\Parameter\ParameterArray();
    $nestedParameterArray->appendParameter(new \SugarLoaf\Parameter\ManagedParameter('Tobias3'));
    $depList = $dm->registerDependencyManagedService(new \SugarLoaf\Service\ManagedSingleton('Tobias2','Tobias2',$nestedParameterArray));

    $outerArray = new \SugarLoaf\Parameter\ParameterArray();
    $nestedParameterArray = new \SugarLoaf\Parameter\ParameterArray();
    $nestedParameterArray->appendNamedParameter('tobias2',new \SugarLoaf\Parameter\ManagedParameter('Tobias2'));
    $outerArray->appendParameter($nestedParameterArray);
    $depList = $dm->registerDependencyManagedService(new \SugarLoaf\Service\ManagedService('Tobias','Tobias',$outerArray));


    $depList = $dm->registerDependencyManagedService(new \SugarLoaf\Service\ManagedService('Somebody','Somebody'));
      $depList->addDependency('Tobias', new \SugarLoaf\Component\ManagedComponent('Tobias'));

*/

};