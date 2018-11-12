<?php

return function($dm,$config){

    $dm->registerService('S3ServiceForOriginalFiles', 'PhpS3Service\S3Service')
      ->appendUnmanagedParameter(array(
        'region' => $config['TOBIGA_S3_REGION_FOR_ORIGINAL_FILES'],
        'bucket' => $config['TOBIGA_S3_BUCKET_NAME_FOR_ORIGINAL_FILES'],
        'endpoint' => $config['TOBIGA_S3_ENDPOINT_FOR_ORIGINAL_FILES'],
        'key' => $config['TOBIGA_S3_KEY_FOR_ORIGINAL_FILES'],
        'secret' => $config['TOBIGA_S3_SECRET_FOR_ORIGINAL_FILES'],
      ));

    $dm->registerService('S3ServiceForTranscodedFiles', 'PhpS3Service\S3Service')
      ->appendUnmanagedParameter(array(
        'region' => $config['TOBIGA_S3_REGION_FOR_TRANSCODED_FILES'],
        'bucket' => $config['TOBIGA_S3_BUCKET_NAME_FOR_TRANSCODED_FILES'],
        'endpoint' => $config['TOBIGA_S3_ENDPOINT_FOR_TRANSCODED_FILES'],
        'key' => $config['TOBIGA_S3_KEY_FOR_TRANSCODED_FILES'],
        'secret' => $config['TOBIGA_S3_SECRET_FOR_TRANSCODED_FILES'],
      ));
  

};