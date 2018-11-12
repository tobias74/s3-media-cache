<?php
namespace Zeitfaden\Model;


class UserRepository extends \Speckvisit\Crud\MongoDb\Repository
{
    public function addUserToKnownUsers($userId, $friendId)
    {
        $this->getCollection()->updateOne(array('id'=>$userId), array('$addToSet' => array("known_users" => $friendId  )));
    }
    
    public function removeKnownUser($userId, $friendId)
    {
        $this->getCollection()->updateOne(array('id'=>$userId), array('$pull' => array("known_users" => $friendId  )));
    }
    

}
