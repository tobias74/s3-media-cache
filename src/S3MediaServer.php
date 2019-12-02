<?php

namespace S3MediaCache;

class S3MediaServer
{
    public function __construct($config)
    {
        $this->configureDependencies($config);
    }

    public function getImageData($id, $imageSize)
    {
        $imageUri = $this->getS3ServiceForOriginalFiles()->getExternalUri($id);
        $spec = $this->getImageSpec($imageSize);
        $cachedImage = $this->getMediaCacheService()->getCachedImage($imageUri, $id, $spec);

        return array(
          'fileNameInBucket' => $cachedImage->getId(),
        );
    }

    public function getVideoData($id, $quality, $format)
    {
        $videoUrl = $this->getS3ServiceForOriginalFiles()->getExternalUri($id);
        $flySpec = $this->getVideoSpec($format, $quality);
        $cachedVideo = $this->getMediaCacheService()->getCachedVideo($videoUrl, $id, $flySpec);
        if ($cachedVideo->isScheduled()) {
            return array(
             'status' => 'scheduled',
            );
        } else {
            return array(
            'status' => 'done',
            'fileNameInBucket' => $cachedVideo->getId(),
          );
        }
    }

    public function deleteMedia($id)
    {
        $this->getMediaCacheService()->deleteCachedMedias($id);
    }

    public function getImageSpec($sizeName)
    {
        $flySpec = new \PhpMediaCache\FlyImageSpecification();

        switch ($sizeName) {
          case 'small':
            $flySpec->width = 100;
            $flySpec->height = 100;
            break;

          case 'medium':
            $flySpec->width = 300;
            $flySpec->height = 300;
            break;

          case 'big':
            $flySpec->width = 800;
            $flySpec->height = 800;
            break;

          case 'very_big':
            $flySpec->width = 1400;
            $flySpec->height = 1400;
            break;

          case 'original':
            $flySpec->width = false;
            $flySpec->height = false;
            break;
        }

        return $flySpec;
    }

    public function getVideoSpec($format, $quality)
    {
        $flySpec = new \PhpMediaCache\FlyVideoSpecification();
        $flySpec->format = $format;
        $flySpec->quality = $quality;

        return $flySpec;
    }

    protected function configureDependencies($config)
    {
        $dm = new \SugarLoaf\DependencyManager();
        $this->dependecyManager = $dm;

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

        // video transcoder
        $dm->registerSingleton('ListenForVideosWorker', '\Zeitfaden\CLI\ListenForVideosWorker')
         ->addManagedDependency('CachedMediaService', 'CachedMediaService');

        $dm->registerService('CreateInfrastructureWorker', '\Zeitfaden\CLI\CreateInfrastructureWorker')
         ->addManagedDependency('S3ServiceForOriginalFiles', 'S3ServiceForOriginalFiles')
         ->addManagedDependency('S3ServiceForTranscodedFiles', 'S3ServiceForTranscodedFiles');

        // Housekeeper
        $dm->registerService('AbandonnedFileSearchWorker', '\Zeitfaden\CLI\AbandonnedFileSearchWorker')
         ->addManagedDependency('S3ServiceForOriginalFiles', 'S3ServiceForOriginalFiles')
         ->addManagedDependency('S3ServiceForTranscodedFiles', 'S3ServiceForTranscodedFiles')
         ->addManagedDependency('MediaCacheService', 'CachedMediaService');

        $dm->registerSingleton('SqlProfiler', '\Tiro\Profiler');

        $dm->registerSingleton('PhpProfiler', '\Tiro\Profiler');

        $dm->registerService('CachedMediaService', '\PhpMediaCache\CachedMediaService')
         ->appendUnmanagedParameter(array(
           'mongoDbHost' => $config['TOBIGA_MONGO_DB_HOST'],
           'mongoDbName' => $config['TOBIGA_MONGO_DB_NAME'],
           'rabbitMqHost' => $config['TOBIGA_RABBIT_MQ_HOST'],
           'rabbitQueueName' => $config['TOBIGA_RABBIT_MQ_QUEUE_NAME'],
         ))
         ->addManagedDependency('CacheFileService', 'S3ServiceForTranscodedFiles');
    }

    protected function getMediaCacheService()
    {
        return $this->dependecyManager->get('CachedMediaService');
    }

    protected function getS3ServiceForOriginalFiles()
    {
        return $this->dependecyManager->get('S3ServiceForOriginalFiles');
    }
}
