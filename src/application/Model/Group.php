<?php
namespace Zeitfaden\Model;


class Group
{
  protected $id = false;
  protected $members;
  protected $title;
  protected $description;
  protected $createByUserId;
  protected $createdAt=0;
  protected $modifiedAt=0;
  


  public function __construct()
  {
    $timeObject = new \DateTime();
    $timestamp = $timeObject->getTimestamp();

    $this->createdAt = $timestamp;
    $this->modifiedAt = $timestamp;
    $this->members = (object) array();
  }

  public function getId()
  {
    return $this->id;
  }

  public function setId($val)
  {
    $this->id = $val;
  }

  public function getModifiedAt()
  {
    return $this->modifiedAt;
  }

  public function setModifiedAt($val)
  {
    $this->modifiedAt = $val;
  }


  public function getCreatedAt()
  {
    return $this->createdAt;
  }

  public function setCreatedAt($val)
  {
    $this->createdAt = $val;
  }




  public function setDescription($val)
  {
    $this->description = $val;
  }

  public function getDescription()
  {
    return $this->description;
  }

  public function setTitle($val)
  {
    $this->title = $val;
  }

  public function getTitle()
  {
    return $this->title;
  }

  public function setCreatedByUserId($val)
  {
    $this->createdByUserId = $val;
  }

  public function getCreatedByUserId()
  {
    return $this->createdByUserId;
  }

  public function getMembers()
  {
    return $this->members;
  }

  public function setMembers($val)
  {
    $this->members = $val;
  }
  

}
