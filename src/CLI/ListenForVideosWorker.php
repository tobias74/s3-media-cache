<?php
namespace Zeitfaden\CLI;

class ListenForVideosWorker
{

  public function setCachedMediaService($val)
  {
    $this->cachedMediaService = $val;
  }

  protected function getCachedMediaService()
  {
    return $this->cachedMediaService;
  }

  public function work()
  {
    $this->getCachedMediaService()->listenForTranscodingJobs();
  }

}
