<?php
namespace Zeitfaden\Model;


class GroupRepository extends \Speckvisit\Crud\MongoDb\Repository
{
    
    public function addUserToGroup($userId, $groupId)
    {
        $this->getCollection()->updateOne(array('id'=>$groupId), array('$set' => array("members.".$userId => array())));
    }

    public function removeUserFromGroup($userId, $groupId)
    {
        $this->getCollection()->updateOne(array('id'=>$groupId), array('$unset' => array("members.".$userId => '' )));
    }
    
    public function addRoleForUserInGroup($role, $userId, $groupId)
    {
        $this->getCollection()->updateOne(array('id'=>$groupId), array('$addToSet' => array("members.".$userId => $role)));
    }

    public function setSingleRoleForUserInGroup($role, $userId, $groupId)
    {
        $this->getCollection()->updateOne(array('id'=>$groupId), array('$set' => array("members.".$userId => array($role))));
    }

    public function removeRoleForUserInGroup($role, $userId, $groupId)
    {
        $this->getCollection()->updateOne(array('id'=>$groupId), array('$pull' => array("members.".$userId => $role)));
    }
    
    
    public function findAllMemberships($userId)
    {
        return $this->getCollection()->find(array("members.".$userId => array('$exists' => true)));
    }
    
    public function updateMetaDataForGroup($group)
    {
        $this->getCollection()->updateOne(array('id'=>$group->getId()), array('$set' => array("title" => $group->getTitle(), "description" => $group->getDescription())));
    }
    
}
