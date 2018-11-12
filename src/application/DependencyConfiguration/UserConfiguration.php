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

    $dm->registerService('UserProvider','\Speckvisit\Crud\MongoDb\EntityProvider')
       ->appendUnmanagedParameter('\Zeitfaden\Model\User');

    $dm->registerService('UserMapper','\Speckvisit\Crud\MongoDb\Mapper\CamelCaseMapper')
       ->appendUnmanagedParameter(array(
          'id',
          'auth0Id',
          'profileImage',
          'displayName',
          'knownUsers'
         ))
       ->appendUnmanagedParameter('users')
       ->appendManagedParameter('UserProvider');




    $dm->registerService('UserRepository','\Zeitfaden\Model\UserRepository')
      ->appendUnmanagedParameter($serverConfig)
      ->addUnmanagedInstance('IdPrefix', 'u')
      ->appendManagedParameter('UserMapper');


    $dm->registerService('UserSessionRecognizer','\PhpUserRecognizer\UserSessionRecognizer')
      ->appendUnmanagedParameter($serverConfig)
      ->addManagedDependency('UserRepository', 'UserRepository');





};