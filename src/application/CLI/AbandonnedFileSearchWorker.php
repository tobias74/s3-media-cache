<?php
namespace Zeitfaden\CLI;

class AbandonnedFileSearchWorker
{
  use \PhpApplicationFront\GetSetTrait;


  public function work()
  {
    echo "Searching for original files without stations\n";
    foreach ($this->getS3ServiceForOriginalFiles()->getPaginator() as $item)
    {
      if (!$this->hasStationReference($item['Key']))
      {
        echo "found item ".$item['Key'];
        echo " ... this file does not have an entry in the database...";
        echo "\n";
      }
    }

    echo "Searching for transcoded files without media-database-reference\n";
    foreach ($this->getS3ServiceForTranscodedFiles()->getPaginator() as $item)
    {
      if (!$this->hasTranscodedMediaReference($item['Key']))
      {
        echo "found trancoded item  ".$item['Key'];
        echo " ... this file does not have an entry in the transcoded-media-database...";
        echo "\n";
      }
    }
    exit(0);
  }


  protected function hasStationReference($stationId)
  {
    try
    {
      $station = $this->getStationRepository()->getById($stationId);
      return true;
    }
    catch (\Exception $e)
    {
      return false;
    }
  }

  protected function hasTranscodedMediaReference($mediaId)
  {
    try
    {
      $station = $this->getMediaCacheService()->getMediaById($mediaId);
      return true;
    }
    catch (\Exception $e)
    {
      return false;
    }
  }



}
