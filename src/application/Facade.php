<?php

namespace Zeitfaden;

class Facade
{
  use \PhpApplicationFront\GetSetTrait;

  public function __construct()
  {
  }

  public function getAllStationsIterator()
  {
    return $this->getStationRepository()->getAll();
  }

  public function getStationById($stationId)
  {
    return $this->getStationRepository()->getById($stationId);
  }



  public function getStationByUserIdAndFileMd5($userId, $fileMd5)
  {
    return $this->getStationRepository()->getOneByUserIdAndFileMd5($userId, $fileMd5);
  }

  public function getStationByFileMd5AndGroupId($fileMd5, $groupId)
  {
    return $this->getStationRepository()->getOneByFileMd5AndGroupId($fileMd5, $groupId);
  }

  public function getStationByUserIdAndFileMd5AndGroupId($userId, $fileMd5, $groupId)
  {
    return $this->getStationRepository()->getOneByUserIdAndFileMd5AndGroupId($userId, $fileMd5, $groupId);
  }

  public function indexStation($station)
  {
    $this->getElasticSearchService()->indexEntity($station);
  }

  public function deleteStation($station)
  {
    $this->getMediaCacheService()->deleteCachedMedias($station->getId());
    $this->getFileService()->deleteFile($station->getPathToFile());
    $this->getElasticSearchService()->deleteEntity($station);
    $this->getStationRepository()->delete($station);
  }

  public function deleteStationById($stationId)
  {
    $station = $this->getStationRepository()->getById($stationId);
    $this->deleteStation($station);
  }

  public function mergeStation($station)
  {
    $this->getStationRepository()->merge($station);
  }

  public function updateMetaDataForGroup($group)
  {
    return $this->getGroupRepository()->updateMetaDataForGroup($group);
  }

  public function getGroupById($groupId)
  {
    return $this->getGroupRepository()->getById($groupId);
  }

  public function mergeGroup($group)
  {
    if ($group->getId() !== false)
    {
        throw new \Exception('We do not want to merge groups that already exists, use single updates please');
    }
    else 
    {
      return $this->getGroupRepository()->merge($group);
    }
  }

  public function deleteGroup($group)
  {
    return $this->getGroupRepository()->delete($group);
  }

  public function mergeUser($user)
  {
    return $this->getUserRepository()->merge($user);
  }

  public function deleteUser($user)
  {
    return $this->getUserRepository()->delete($user);
  }

  public function getUserById($id)
  {
    return $this->getUserRepository()->getById($id);
  }



  public function removeUserFromGroup($user,$group)
  {
    $this->getGroupRepository()->removeUserFromGroup($user->getId(), $group->getId());
  }
  
  public function addUserToGroup($user,$group)
  {
    $this->getGroupRepository()->addUserToGroup($user->getId(), $group->getId());
  }

  public function addRoleForUserInGroup($role, $user, $group)
  {
    $this->getGroupRepository()->addRoleForUserInGroup($role, $user->getId(), $group->getId());
  }

  public function removeRoleForUserInGroup($role, $user, $group)
  {
    $this->getGroupRepository()->removeRoleForUserInGroup($role, $user->getId(), $group->getId());
  }

  public function setSingleRoleForUserInGroup($role, $user, $group)
  {
    $this->getGroupRepository()->setSingleRoleForUserInGroup($role, $user->getId(), $group->getId());
  }


  public function findAllMemberships($user)
  {
    return iterator_to_array($this->getGroupRepository()->findAllMemberships($user->getId()));
  }


  public function addUserToKnownUsers($userId, $knownUserId)
  {
    $this->getUserRepository()->addUserToKnownUsers($userId, $knownUserId);
  }

  







  

  
}
