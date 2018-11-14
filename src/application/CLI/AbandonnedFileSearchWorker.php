<?php
namespace Zeitfaden\CLI;

class AbandonnedFileSearchWorker
{
  use \PhpApplicationFront\GetSetTrait;


  public function work()
  {
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
