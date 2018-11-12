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


    $dm->registerService('GroupProvider','\Speckvisit\Crud\MongoDb\EntityProvider')
       ->appendUnmanagedParameter('\Zeitfaden\Model\Group');

    $dm->registerService('GroupMapper','\Speckvisit\Crud\MongoDb\Mapper\UnderscoreMapper')
       ->appendUnmanagedParameter(array(
          'id',
          'modifiedAt',
          'createdAt',
          'title',
          'description',
          'createdByUserId',
          'members'
         ))
       ->appendUnmanagedParameter('groups')
       ->appendManagedParameter('GroupProvider');








    $dm->registerService('GroupRepository','\Zeitfaden\Model\GroupRepository')
      ->appendUnmanagedParameter($serverConfig)
      ->addUnmanagedInstance('IdPrefix', 'g')
      ->appendManagedParameter('GroupMapper');




};