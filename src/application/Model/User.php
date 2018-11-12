<?php
namespace Zeitfaden\Model;


class User extends \PhpUserRecognizer\User
{

  protected $knownUsers;

  public function setKnownUsers($users)
  {
    if (!$users)
    {
      $this->knownUsers = array();
    }
    else
    {
      $this->knownUsers = $users;
    }
  }
  
  public function getKnownUsers()
  {
      return $this->knownUsers;
  }

}
