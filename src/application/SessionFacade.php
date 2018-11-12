<?php

namespace Zeitfaden;

class SessionFacade
{
  use \PhpApplicationFront\GetSetTrait;

  public function __construct($user)
  {
    $this->loggedInUser = $user;
    $this->aclCache = array();
    $this->groupCache = array();
  }

  public function getLoggedInUser()
  {
    if (!isset($this->loggedInUser))
    {
      throw new \Exception("no logged in user");
    }
    else
    {
      return $this->loggedInUser;
    }
  }

  public function getLoggedInUserId()
  {
    try 
    {
      return $this->getLoggedInUser()->getId();  
    }
    catch (\Exception $e)
    {
      return false;
    }
  }
  
  protected function getGroupById($groupId)
  {
    if (!isset($this->groupCache[$groupId]))
    {
      $this->groupCache[$groupId] = $this->getFacade()->getGroupById($groupId);
    }
    return $this->groupCache[$groupId];
  }

  public function getAclForGroup($groupId)
  {
    if (!isset($this->aclCache[$groupId]))
    {
      $acl = new \PhpSimpleAcl\AccessControlList();
      $acl->allow('admin','station', ['read','edit','delete']);
      $acl->allow('editor','station',['read','edit']);
      $acl->allow('guest','station', ['read']);
      $acl->allow('admin','group', ['administer_memberships','edit_meta']);
      $acl->allow('editor','group', []);
      
      $group = $this->getGroupById($groupId);

      foreach($group->getMembers() as $memberId => $memberRoles)
      {
        $acl->assignRole($memberRoles, $memberId);
      }
      
      $this->aclCache[$groupId] = $acl;
    }
    return $this->aclCache[$groupId];
  }

  public function hasReadPermissionForStation($station)
  {
    //echo "<pre>";
    //echo "this is loggedin user".$this->getLoggedInUserId();
    //die();
    $acl = $this->getAclForGroup($station->getGroupId());
    //print_r($acl);
    $value = $acl->isAllowed($this->getLoggedInUserId(), 'station', 'read');
    //echo "\n\n this is the value: ".$value;
    //die();
    return $acl->isAllowed($this->getLoggedInUserId(), 'station', 'read');
  }

  public function hasEditPermissionForStation($station)
  {
    $acl = $this->getAclForGroup($station->getGroupId());
    return $acl->isAllowed($this->getLoggedInUserId(), 'station', 'edit');
  }

  public function hasDeletePermissionForStation($station)
  {
    $acl = $this->getAclForGroup($station->getGroupId());
    return $acl->isAllowed($this->getLoggedInUserId(), 'station', 'delete');
  }

  public function hasAdminPermissionForGroup($group)
  {
    $acl = $this->getAclForGroup($group->getId());
    return $acl->isAllowed($this->getLoggedInUserId(), 'group', 'administer_memberships');
  }


  public function getStationById($stationId)
  {
    $station = $this->getFacade()->getStationById($stationId);
    if ($this->hasReadPermissionForStation($station))
    {
      return $station;
    }
    else
    {
      throw new \Exception('user not allowed to view station');
    }
  }

  public function mergeStation($station)
  {
    
    if ($this->hasEditPermissionForStation($station))
    {
      return $this->getFacade()->mergeStation($station);
    }
    else
    {
      throw new \Exception('user not allowed to edit station');
    }
  }


  public function deleteStation($station)
  {
    if ($this->hasDeletePermissionForStation($station))
    {
      return $this->getFacade()->deleteStation($station);
    }
    else
    {
      throw new \Exception('user not allowed to edit station');
    }

  }

  public function deleteStationById($stationId)
  {
    $station = $this->getFacade()->getStationById($stationId);
    $this->deleteStation($station);
  }

  public function getMyStationByFileMd5($fileMd5)
  {
    return $this->getFacade()->getStationByUserIdAndFileMd5($this->getLoggedInUserId(), $fileMd5);
  }

  public function existsStationByFileMd5AndGroupId($fileMd5, $groupId)
  {
    try
    {
      $this->getFacade()->getStationByFileMd5AndGroupId($fileMd5, $groupId);
      return true;
    }
    catch (\Speckvisit\Crud\MongoDb\NoMatchException $e)
    {
      return false;
    }
  }

  public function existsMd5InMyGlobalUploads($fileMd5)
  {
    try
    {
      $this->getFacade()->getStationByUserIdAndFileMd5($this->getLoggedInUserId(), $fileMd5);
      return true;
    }
    catch (\Speckvisit\Crud\MongoDb\NoMatchException $e)
    {
      return false;
    }
  }




  public function addUserToKnownUsers($userId)
  {
    $this->getFacade()->getUserById($userId);
    $this->getFacade()->addUserToKnownUsers($this->getLoggedInUserId(), $userId);
  }

  public function removeUserFromGroup($userId, $groupId)
  {
    $group = $this->getGroupById($groupId);
    $user = $this->getFacade()->getUserById($userId);
    
    if ($this->hasAdminPermissionForGroup($group))
    {
      $this->getFacade()->removeUserFromGroup($user, $group);
    }
  }


  

  
}
