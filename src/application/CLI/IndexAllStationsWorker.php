<?php
namespace Zeitfaden\CLI;

class IndexAllStationsWorker
{

  public function setControllerFacade($val)
  {
    $this->controllerFacade = $val;
  }

  protected function getControllerFacade()
  {
    return $this->controllerFacade;
  }

  public function work()
  {
    $counter=0;
    $entities = $this->getControllerFacade()->getAllStationsIterator();
    foreach ($entities as $entity)
    {
      $counter++;
      if (($counter % 100) == 0)
      {
        echo $counter."\n";
      }
      $this->getControllerFacade()->indexStation($entity);
    }
    exit(0);
  }


}
