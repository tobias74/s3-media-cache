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

    
    $dm->registerService('StationProvider','\Speckvisit\Crud\MongoDb\EntityProvider')
       ->appendUnmanagedParameter('\Zeitfaden\Model\Station');

    $dm->registerService('StationMapper','\Zeitfaden\Model\StationMapper')
       ->appendUnmanagedParameter(array(
          'id',
          'userId',
          'groupId',
          'title',
          'description',
          'timestamp',
          'timezone',
          'fileMd5',
          'fileName',
          'fileType',
          'fileSize',
          'createdAt',
          'modifiedAt'
         ))
       ->appendUnmanagedParameter('stations')
       ->appendManagedParameter('StationProvider');



    $dm->registerService('StationRepository','\Speckvisit\Crud\MongoDb\Repository')
      ->appendUnmanagedParameter($serverConfig)
      ->addUnmanagedInstance('IdPrefix', 's')
      ->appendManagedParameter('StationMapper');

};