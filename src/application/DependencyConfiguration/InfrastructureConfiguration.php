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



    // video transcoder
    $dm->registerSingleton('ListenForVideosWorker','\Zeitfaden\CLI\ListenForVideosWorker')
      ->addManagedDependency('CachedMediaService', 'CachedMediaService');

    $dm->registerService('CreateInfrastructureWorker','\Zeitfaden\CLI\CreateInfrastructureWorker')
      ->addManagedDependency('S3ServiceForOriginalFiles', 'S3ServiceForOriginalFiles')
      ->addManagedDependency('S3ServiceForTranscodedFiles', 'S3ServiceForTranscodedFiles');

    // Housekeeper
    $dm->registerService('AbandonnedFileSearchWorker','\Zeitfaden\CLI\AbandonnedFileSearchWorker')
      ->addManagedDependency('S3ServiceForOriginalFiles', 'S3ServiceForOriginalFiles')
      ->addManagedDependency('S3ServiceForTranscodedFiles', 'S3ServiceForTranscodedFiles')
      ->addManagedDependency('MediaCacheService', 'CachedMediaService');



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