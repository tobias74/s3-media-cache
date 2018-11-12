<?php

return function($dm,$config){


    $dm->registerService('ElasticStationMapper', 'Zeitfaden\Elasticsearch\StationMapper')
      ->appendUnmanagedParameter(array(
        'indexName' => $config['TOBIGA_STATIONS_INDEX_NAME'],
      ));
  


    $dm->registerService('ElasticSearchService','\Speckvisit\Crud\Elasticsearch\ElasticSearchService')
      ->appendUnmanagedParameter(array(
        'elasticSearchHost' => $config['TOBIGA_ELASTIC_SEARCH_HOST']  
      ))
      ->appendManagedParameter('ElasticStationMapper');


};