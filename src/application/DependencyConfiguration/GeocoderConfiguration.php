<?php

return function($dm,$config){

    $dm->registerService('PlacesGeocoderCacheFrontEnd','\ReverseGeocoderCache\CacheFrontEnd')
      ->addManagedDependency('CacheBackend', 'ZeitfadenRedisService')
      ->addUnmanagedInstance('KeySize', 50)
      ->addUnmanagedInstance('Prefix', "GEOCODING-lang-".$config['browserLanguage']);
  
    $dm->registerService('TimezonesGeocoderCacheFrontEnd','\ReverseGeocoderCache\CacheFrontEnd')
      ->addManagedDependency('CacheBackend', 'ZeitfadenRedisService')
      ->addUnmanagedInstance('KeySize', 1000)
      ->addUnmanagedInstance('Prefix', "TIMEZONE-CODING");

    $dm->registerService('PlacesGeocoderClient','\ReverseGeocoderCache\CacheClient')
      ->addManagedDependency('CacheFrontEnd', 'PlacesGeocoderCacheFrontEnd')
      ->addManagedDependency('DataProvider', 'ReverseGeocoderPlacesJsonProvider');

    $dm->registerService('TimezonesGeocoderClient','\ReverseGeocoderCache\CacheClient')
      ->addManagedDependency('CacheFrontEnd', 'TimezonesGeocoderCacheFrontEnd')
      ->addManagedDependency('DataProvider', 'ReverseGeocoderTimezonesProvider');

    $dm->registerService('ReverseGeocoderPlacesProvider','\ReverseGeocoderCache\Provider\GooglePlacesProvider')
      ->addUnmanagedInstance('ApiKey', $config['TOBIGA_GOOGLE_API_KEY'])
      ->addUnmanagedInstance('Language', $config['browserLanguage']);

    $dm->registerService('ReverseGeocoderPlacesJsonProvider','\ReverseGeocoderCache\Provider\GooglePlacesJsonProvider')
      ->addUnmanagedInstance('ApiKey', $config['TOBIGA_GOOGLE_API_KEY'])
      ->addUnmanagedInstance('Language', $config['browserLanguage']);


    $dm->registerService('ReverseGeocoderTimezonesProvider','\ReverseGeocoderCache\Provider\GoogleTimezonesProvider')
      ->addUnmanagedInstance('ApiKey', $config['TOBIGA_GOOGLE_API_KEY']);

};