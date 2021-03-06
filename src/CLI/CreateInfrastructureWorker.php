<?php
namespace Zeitfaden\CLI;

class CreateInfrastructureWorker
{
  use \PhpApplicationFront\GetSetTrait;

  public function work()
  {
    $this->getS3ServiceForOriginalFiles()->createYourBucket();
    $this->getS3ServiceForTranscodedFiles()->createYourBucket();
    exit(0);
  }


}
