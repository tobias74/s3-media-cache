<?php
namespace Zeitfaden\Model;


class Station
{


	protected $userId;
	protected $groupId;
	protected $title;
	protected $description;

	protected $latitude = null;
	protected $longitude = null;
	protected $timezone = null;

  protected $fileType;
  protected $fileSize=false;
  protected $fileName;
  protected $fileMd5;
  protected $createdAt=0;
  protected $modifiedAt=0;
  
  protected $id = false;


  public function __construct()
  {
    $timeObject = new \DateTime();
    $timestamp = $timeObject->getTimestamp();

    $this->createdAt = $timestamp;
    $this->modifiedAt = $timestamp;
    $this->timestamp = $timestamp;

  }

  public function getKey()
  {
      return $this->getId();
  }

  public function getStringIdentifier()
  {
    return 'zfi_'.$this->getId();
  }

  public function getId()
  {
    return $this->id;
  }

  public function injectValue($field,$value)
  {
    $this->$field = $value;
  }

  public function getDryValue($field)
  {
    return $this->$field;
  }

  public function getDebugName()
  {
    return print_r($this->declareSynthesizedProperties(), true);
  }




  protected function isSameInstance($objA, $objB )
  {
    if ($objA === $objB)
    {
      return 0;
    }
    else
    {
      return 1;
    }
  }


  public function setId($val)
  {
    $this->id = $val;
  }


  public function getSimpleFileType()
  {
    if (substr($this->getFileType(),0,5) == "image")
    {
      return "image";
    }
    elseif (substr($this->getFileType(),0,5) == "video")
    {
      return "video";
    }
    else
    {
      return $this->getFileType();
    }
  }


  public function setFileMd5($value)
  {
    $this->fileMd5 = $value;
  }

  public function getFileMd5()
  {
    return $this->fileMd5;
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




  public function setTimezone($val)
  {
    return $this->timezone = $val;
  }

  public function getTimezone()
  {
    return $this->timezone;
  }


  public function getLatitude()
  {
    return $this->latitude;
  }

  public function getLongitude()
  {
    return $this->longitude;
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


  public function setFileSize($val)
  {
    $this->fileSize = $val;
  }

	public function getFileSize()
	{
	  return $this->fileSize;
	}


  public function setFileType($val)
  {
    $this->fileType = $val;
  }

	public function getFileType()
	{
	  return $this->fileType;
	}


  public function setFileName($val)
  {
    $this->fileName = $val;
  }

	public function getFileName()
	{
	  return $this->fileName;
	}


	public function setLocation($latitude, $longitude)
	{
		$this->setLatitude($latitude);
		$this->setLongitude($longitude);
	}

	public function setLatitude($latitude)
	{
		$this->latitude = $latitude;
	}

	public function setLongitude($longitude)
	{
		$this->longitude = $longitude;
	}


  public function setUserId($userId)
  {
    $this->userId = $userId;
  }

  public function getUserId()
  {
    return $this->userId;
  }


  public function setGroupId($groupId)
  {
    $this->groupId = $groupId;
  }

  public function getGroupId()
  {
    return $this->groupId;
  }

  public function setTimestamp($timestamp)
  {
		$this->timestamp = $timestamp;
  }

	public function getTimestamp()
  {
		return $this->timestamp;
  }


  public function getDateISO8601()
  {
    return $this->getDateObject()->format(\DateTime::ATOM);
  }


  protected function getDateObject()
  {
    $timeObject = new \DateTime();
    $timeObject->setTimestamp($this->getTimestamp());
		return $timeObject;
  }


	protected function getTimezoneObject()
	{
		try
		{
			$tz = new \DateTimeZone($this->getTimezone());
		}
		catch (\Exception $e)
		{
			$tz = new \DateTimeZone('Europe/Berlin');
		}
		return $tz;
	}


  public function getLocalDateString($format)
  {
    try
    {
      $stationTime = $this->getDateObject();
      $stationTime->setTimezone($this->getTimezoneObject());
      return $stationTime->format($format);
    }
    catch (ErrorException $e)
    {
      return "invalid date";
    }
  }

}
