<?php
namespace Zeitfaden\Controller;


class StationAccessResolver
{
  use \PhpApplicationFront\GetSetTrait,\PhpApplicationFront\SessionTrait;
    
  public function isAllowedToView($stationId)
  {
    try
    {
      $station = $this->getSessionFacade()->getStationById($stationId);
      return true;
     }
     catch(\Speckvisit\Crud\MongoDb\NoMatchException $e)
     {
       return false;
     }
  }
    
    
}