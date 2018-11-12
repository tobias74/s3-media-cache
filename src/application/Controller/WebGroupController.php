<?php
namespace Zeitfaden\Controller;


class WebGroupController extends AbstractZeitfadenController
{

  public function groupsAction()
  {
    echo $this->render('groups.html',array(
      'groups' => $this->getFacade()->findAllMemberships($this->getLoggedInUser())  
    ));
  }

  public function createGroupAction()
  {
    $group = new \Zeitfaden\Model\Group();
    $group->setCreatedByUserId($this->getLoggedInUserId());

    $this->getFacade()->mergeGroup($group);
    $this->getFacade()->addUserToGroup($this->getLoggedInUser(), $group);
    $this->getFacade()->addRoleForUserInGroup('creator',$this->getLoggedInUser(), $group);
    $this->getFacade()->addRoleForUserInGroup('admin',$this->getLoggedInUser(), $group);
    
    //$this->getFacade()->addRoleForUserInGroup('bullshit_one',$this->getLoggedInUser(), $group);
    //$this->getFacade()->removeRoleForUserInGroup('bullshit_one',$this->getLoggedInUser(), $group);
    //$this->getFacade()->removeUserFromGroup($this->getLoggedInUser(), $group);
    //print_r($this->getFacade()->findAllMemberships($this->getLoggedInUser()));
    
    
    
    header("HTTP/1.1 301 Moved Permanently"); 
    header("Location: /website/edit-group/".$group->getId());     
    die();
  }

  public function editGroupAction()
  {
    $group = $this->getFacade()->getGroupById($this->getParam('groupId'));
    
    $users = array();
    foreach($group->getMembers() as $memberId => $roles)
    {
      $users[$memberId] = $this->getUserById($memberId);
    }
    
    
    $knownUserIds = $this->getLoggedInUser()->getKnownUsers();
    if (!$knownUserIds)
    {
      $knownUserIds = array();
    }
    
    $memberIds = array_keys($group->getMembers());
    
    $knownButNonMembers = array_diff($knownUserIds, $memberIds);

    
    foreach($knownButNonMembers as $userId)
    {
      $users[$userId] = $this->getUserById($userId);
    }

    echo $this->render('edit-group.html',array(
      'users' => $users,
      'group' => $group,
      'nonMembers' => $knownButNonMembers
    ));
  }

  public function editGroupPostAction()
  {
    $group = $this->getFacade()->getGroupById($this->getParam('groupId'));
    $group->setTitle($_REQUEST['group_title']);
    $group->setDescription($_REQUEST['group_description']);
    
    $this->getFacade()->updateMetaDataForGroup($group);

    header("HTTP/1.1 301 Moved Permanently"); 
    header("Location: /website/edit-group/".$group->getId());     
    die();
  }

  public function apiSetRolesAction()
  {
    $group = $this->getFacade()->getGroupById($_REQUEST['groupId']);
    
    $role = $_REQUEST['role'];
    $user = $this->getFacade()->getUserById($_REQUEST['memberId']);
    $group = $this->getFacade()->getGroupById($_REQUEST['groupId']);
    
    if ($this->getSessionFacade()->hasAdminPermissionForGroup($group))
    {
      $this->getFacade()->setSingleRoleForUserInGroup($role, $user, $group);
      echo json_encode(array(
        'status' => 'ok'
      ));
    }
    else 
    {
      echo json_encode(array(
        'status' => 'not_allowed'
      ));
    }
    die();
  }


  public function removeUserFromGroupAction()
  {
    $this->getSessionFacade()->removeUserFromGroup($_REQUEST['memberId'], $_REQUEST['groupId']);
    echo json_encode(array(
      'status' => 'removed'
    ));
    
  }
}

