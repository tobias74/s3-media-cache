<?php
namespace Zeitfaden\Controller;


class WebStationController extends AbstractZeitfadenController
{
  
  
  
  protected function getRekognitionClient()
  {
    if (!isset($this->rekognitionClient))
    {
      $this->rekognitionClient = RekognitionClient::factory(array(
    		'region'	=> $this->getAwsConfig()['region'],
    		'version'	=> 'latest',
        'credentials' => array(
          'key' => $this->getAwsConfig()['key'],
          'secret' => $this->getAwsConfig()['secret']
        )
      ));
    }
    return $this->rekognitionClient;
  }

  
  protected function indexFaces($station)
  {
    $result = $this->getRekognitionClient()->indexFaces([
        'CollectionId' => $this->getAwsConfig()['faceCollectionId'],
        'DetectionAttributes' => [
        ],
        'ExternalImageId' => $station->getId(),
        'Image' => [
            'S3Object' => [
                'Bucket' => $this->getAwsConfig()['bucket'],
                'Name' => $station->getId(),
            ],
        ],
    ]);
  }
  
  
  public function getByIdAction()
  {
    try
    {
      $stationId = $this->getParam('stationId',0);
      $station = $this->getSessionFacade()->getStationById($stationId);
      echo $this->render('station.html', array(
        'station' => $this->getStationDTO($station)
      ));
    }
    catch (\Speckvisit\Crud\MongoDb\NoMatchException $e)
    {
      header('X-Tobias: some');
      header('HTTP/1.0 404 Not Found',true,404);
      echo json_encode(array(
        "error" => 'sole not found',
        "errorMessage" => $e->getMessage(),
        "stackTrace" => $e->getTraceAsString()
      ));
    }
  }



  protected function presetDefault($request)
  {
    $defaults = array(
      'limit' => 10,
      'sorting' => 'intoThePast',
      'useMap' => 'no',
      'maxDistance' => '1000'
    );
    
    foreach ($defaults as $key => $value)
    {
      if (!$request->hasParam($key))
      {
        $request->setParam($key, $value);
      }
    }
  }


  public function indexAction()
  {

    $this->startTimer();

    if ($this->getParam('clickedButton','') === 'export')
    {
      $this->exportAction();
    }
    else 
    {
      $this->presetDefault($this);
  
      if ( ($this->getParam('useMap') == 'yes') && (!$this->hasParam('latitude') || !$this->hasParam('longitude')))
      {
        echo $this->render('index.html', array(
          'requestLocationFromBrowser' => 'yes'
        ));
        
      }
      else
      {
        $params = $this->_routeParameters;
        $userId = $this->getParam('userId',false);
        $groupId = $this->getParam('groupId',false);
  
  
        if ($this->getParam('form_action','') === 'delete_station')
        {
          $deleteStationId = $this->getParam('deleteStationId',0);
          $station = $this->getSessionFacade()->getStationById($deleteStationId);
      
          if ($this->getLoggedInUserId() === $station->getUserId())
          {
            $this->getFacade()->deleteStationById($deleteStationId);
            header("HTTP/1.1 303 See Other");
            header("Location: ".$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);  
            die();
          }      
        }

        $stationRequest = array_merge(array('groupId' => $this->getParam('groupId')), $_REQUEST);
        $specBuilder = new \Zeitfaden\Elasticsearch\SpecificationBuilder();
        $spec = $specBuilder->getElasticSpecificationByRequest($stationRequest);
        $stations = $this->getElasticSearchService()->getBySpecification($spec);

        $hashTagRequest = array_merge($stationRequest);
        unset($hashTagRequest['searchText']);
        $hasTagSpec = $specBuilder->getElasticSpecificationByRequest($hashTagRequest);
        $hashTagList = $this->getElasticSearchService()->aggregate($hasTagSpec['criteria'], array(
          'type' => 'terms',
          'field' => 'hashTagList.keyword',
          'size' => 100
        ));

        $stationDTOs = $this->getStationDTOs($stations);

        if ($userId)
        {
          $profileUser = $this->getFacade()->getUserById($userId);
        }
        else 
        {
          $profileUser = false; 
        }
        

        
        $myGet = array_merge($_GET);
        unset($myGet['searchAfter']);
        unset($myGet['previousSearchAfter']);
    
        $this->reportTimer();
        
        echo $this->render('index.html', array(
          'isFirstPage' => !isset($_REQUEST['searchAfter']) || !$_REQUEST['searchAfter'],
          'currentQueryWithoutSearchAfter' => http_build_query($myGet),
          'isMyProfile' => ($userId === $this->getLoggedInUserId()) && $this->getLoggedInUserId(),
          'profileUser' => $profileUser,
          'myDiskUsage' => $this->getDiskUsage() / (1000 * 1000 * 1000),
          'myCostsLast30Days' => $this->getCostsLastDays(30),
          'stations' => $this->getStationDTOs($stations),
          'hashtags' => $hashTagList['buckets'],
          'request' => $stationRequest
        ));
  
  
        
      }
      
    }
  }
  
  


  protected function storeFile($sourceFilePath, $key)
  {
    $this->getS3Service()->storeFile($sourceFilePath, $key);
  }

  protected function getTimezoneByLatLng($lat,$lng)
  {
    $timezoneId = $this->getTimezoneCache()->get($lat,$lng);
    return $timezoneId;

  }

  protected function ensureTimezone($station)
  {
    if (($station->getLatitude() !== null) && ($station->getLongitude() !== null) && ($station->getTimezone() === null))
    {
      $station->setTimezone( $this->getTimezoneByLatLng($station->getLatitude(), $station->getLongitude()) );
    }
  }


  public function uploadFormAction()
  {
    $stationId = $this->getParam('stationId',0);
    if ($stationId)
    {
      $station = $this->getSessionFacade()->getStationById($stationId);
    }
    else 
    {
      $station = false;
    }
    
    echo $this->render('upload-form.html', array(
      'station' => $station ? $this->getStationDTO($station) : false,
      'params' => $this->_routeParameters
    ));
  }

  protected function updateStationWithRequest($station)
  {
    $date = new \DateTime();
    $station->setLatitude($_REQUEST['latitude']);
    $station->setLongitude($_REQUEST['longitude']);
    $station->setTitle($_REQUEST['title']);
    $station->setDescription($_REQUEST['description']);
    $this->ensureTimezone($station);

    $timeObject = new \DateTime($this->getParam('inputDateTimeString','now') , new \DateTimeZone($station->getTimezone()) );
    $station->setTimestamp($timeObject->getTimestamp());

    $station->setModifiedAt($date->getTimestamp());
    
  }


  protected function createNewUploadedStation()
  {
    $date = new \DateTime();
    $station = new \Zeitfaden\Model\Station();
    $station->setUserId($this->getLoggedInUserId());
    $station->setGroupId($this->getParam('groupId'));
    $station->setCreatedAt($date->getTimestamp());
    return $station;    
  }



  protected function processExifFromFile($station, $fileName)
  {
    try
    {
      $this->updateStationWithZeitfadenExifData($station, $fileName);    
    }
    catch (\PhpExifTimePlace\ExifException $e)
    {
      try
      {
        $this->updateStationWithExifData($station, $fileName);
      }
      catch (\PhpExifTimePlace\ExifException $e)
      {
        error_log('exif updating did not work.. ok?');
      }
    }

    $this->ensureTimezone($station);

    $date = new \DateTime();
    $station->setModifiedAt($date->getTimestamp());

  }

  protected function updateStationWithExifData($station, $fileName)
  {
    $exifExtractor = new \PhpExifTimePlace\ExifTimePlaceExtractor($fileName);
    
    try
    {
      $location = $exifExtractor->getLocation();
      $station->setLatitude($location['latitude']);    
      $station->setLongitude($location['longitude']);    
    }
    catch(\PhpExifTimePlace\ExifException $e)
    {
      // useless
    }
    
    try
    {
      $this->ensureTimezone($station);
      $timezoneString = $this->getParam('helperTimezone','Europe/Paris');
      $myDate = $exifExtractor->getTime($timezoneString);
    }
    catch (\PhpExifTimePlace\ExifException $e)
    {
      $myDate = \DateTime::createFromFormat("Y?m?d H:i:s", '1800-01-01 07:00:00', new \DateTimeZone('GMT'));
    }

    $station->setTimestamp($myDate->getTimestamp());
  }

  protected function updateStationWithZeitfadenExifData($station, $fileName)
  {
    $exifExtractor = new \PhpExifTimePlace\ExifTimePlaceExtractor($fileName);

    $jsonData = $exifExtractor->decodeJsonComment();
    $station->setLatitude($jsonData['latitude']);
    $station->setLongitude($jsonData['longitude']);
    $station->setTimestamp($jsonData['timestamp']);
    $station->setDescription($jsonData['description']);
  }



  public function getStationDTOs($stations)
  {
    $dtos = array();
    foreach ($stations as $station)
    {
      $dtos[] = $this->getStationDTO($station);
    }
    return $dtos;
  }

	public function getStationDTO($station)
	{
	  $timer = $this->getProfiler()->startTimer('Assemble One Station-DTO');
	  $fileData = array();
    $item = $station;
    $values = array();

    $stationDateTime = new \DateTime();
    try
    {
      $timezone = new \DateTimeZone($station->getTimezone());
      $stationDateTime->setTimezone($timezone);
    }
    catch (\Exception $e)
    {
      error_log('we had a bad timezone in stationId '.$station->getId());      
    }
    
    $stationDateTime->setTimestamp($station->getTimestamp());
    
    try 
    {
      $placeData = json_decode($this->getPlacesGeocoderClient()->get($station->getLatitude(), $station->getLongitude()), true);
    }
    catch (\Exception $e)
    {
      $placeData = array(
        'address_components' => false,
        'formatted_address' => 'unknown location'
      );  
    }
    
    $addressComponents = $placeData['address_components'];

    if (is_array($addressComponents))
    {
      $sublocalities = array_merge(array_filter($addressComponents, function($element){
        return (array_search('sublocality', $element['types']) !== false);
      }));
      $localities = array_merge(array_filter($addressComponents, function($element){
        return (array_search('locality', $element['types']) !== false);
      }));
      
      $shortPlaceName = "";
      if (isset($localities[0]))
      {
        $shortPlaceName.=$localities[0]['short_name'];
      }
      if (isset($sublocalities[0]))
      {
        $shortPlaceName.=", ".$sublocalities[0]['short_name'];
      }
      
    }
    else 
    {
      $shortPlaceName = '';
    }

    $basicData = array(
      'debugFileMd5' => $station->getFileMd5(),

      'id' => $station->getId(),
      'title' => $station->getTitle(),
      'description' => $station->getDescription(),
      'userId' => $station->getUserId(),
      'groupId' => $station->getGroupId(),
      'stationId' => $station->getId(),

      'timestamp' => $station->getTimestamp(),
      'timestampString' => $stationDateTime->format("Y-m-d\TH:i:s"),
      'localTime' => $station->getLocalDateString('l jS F Y'),
      'latitude' => $station->getLatitude(),
      'longitude' => $station->getLongitude(),
      'timezone' => $station->getTimezone(),
      
      'placeName' => $placeData['formatted_address'],
      'shortPlaceName' => $shortPlaceName,
      
      'userDisplayName' => $this->getDisplayNameByUserId($station->getUserId()),
      
      'esSort' => isset($station->esSort) ? $station->esSort : '{}'

    );

    $basicData['readPermission']   = $this->getSessionFacade()->hasReadPermissionForStation($station);
    $basicData['editPermission']   = $this->getSessionFacade()->hasEditPermissionForStation($station);
    $basicData['deletePermission'] = $this->getSessionFacade()->hasDeletePermissionForStation($station);

    $basicData['fileSize'] = $item->getFileSize();
    $basicData['fileType'] = $item->getFileType();

    switch ($item->getSimpleFileType())
    {
      case "image":
        $basicData['attachmentType'] = "image";
        break;

      case "video":
        $basicData['attachmentType'] = "video";

        break;

      default:
        $basicData['attachmentType'] = $item->getSimpleFileType();
        break;
    }

    $timer->stop();
    
		return $basicData;
	}




  public function existsMd5HashAction()
  {
    $fileMd5 = $this->getParam('fileMd5',false);

    if ($this->preventMeFromDoubleGlobalUpload() && $this->existsMd5InMyGlobalUploads($fileMd5))
    {
      echo json_encode(array(
        'status' => 'already_exists_in_your_uploads'
      ));
    }
    else if (!$this->preventMeFromDoubleGlobalUpload() && $this->existsMd5InGroup($fileMd5, $this->getParam('groupId', false)) )
    {
      echo json_encode(array(
        'status' => 'already_exists_in_group'
      ));
    }
    else {
      echo json_encode(array(
        'status' => 'not_found'
      ));
    }
  }


  protected function haveIAlreadyUploadedThisFileAnywhere($fileName)
  {
    $fileMd5 = md5_file($fileName);
    return $this->existsMd5InMyGlobalUploads($fileMd5);
  }

  protected function doesTheGroupAlreadyKnowThisFile($fileName, $groupId)
  {
    $fileMd5 = md5_file($fileName);
    return $this->existsMd5InGroup($fileMd5, $groupId);
  }

  
  protected function existsMd5InGroup($fileMd5, $groupId)
  {
      return $this->getSessionFacade()->existsStationByFileMd5AndGroupId($fileMd5, $groupId);
      $station = $this->getSessionFacade()->getMyStationByFileMd5($fileMd5);
  }

  protected function existsMd5InMyGlobalUploads($fileMd5)
  {
      return $this->getSessionFacade()->existsMd5InMyGlobalUploads($fileMd5);
  }


  protected function preventMeFromDoubleGlobalUpload()
  {
    if ( ($_REQUEST['preventGlobalDoppelganger'] ?? false) === 'yes')
    {
      return true; 
    }
    else 
    {
      return false;
    }
  }




  public function apiDeleteStationAction()
  {
    try
    {
      $station = $this->getSessionFacade()->getStationById($this->getParam('stationId',false));
      $this->getSessionFacade()->deleteStation($station);
      echo json_encode(array(
        'status' => 'deleted'
      ));
    }
    catch (\Speckvisit\Crud\MongoDb\NoMatchException $e)
    {
      echo json_encode(array(
        'status' => 'not_found'
      ));
    }
    catch (\Exception $e)
    {
      echo json_encode(array(
        'status' => 'probably not allowed'
      ));
    }
    
  }






  public function chunkedUploaderForm()
  {
    echo $this->render('chunked-uploader.html',array());
  }

  protected function issueConflictHeader()
  {
    header('HTTP/1.0 409 Conflict',true,409);
    echo json_encode(array(
      'status' => 'not_stored',
      'message' => 'a file with accoridng md5-hash already exists.'
    ));
    die();
  }


  public function chunkedUploadGetAction()
  {
    if ($this->getResumableUploadProcessor()->existsChunk())
    {
       header("HTTP/1.0 200 Ok");
    }
    else 
    {
       header("HTTP/1.0 404 Not Found");
    }
    die();
  }

  
  public function getResumableUploadProcessor()
  {
    if (!isset($this->resumableUploadProcessor))
    {
      $this->resumableUploadProcessor = $this->getResumableUploadProcessorProvider()->provide(array(
        'processorId' => $this->getLoggedInUserId()
      ));
    }
    
    return $this->resumableUploadProcessor;
  }
  

  public function chunkedUploadPostAction()
  {
    $this->needsLoggedInUser();
    $this->ensureRequestOwner();
    
    try
    {
      $this->getResumableUploadProcessor()->processNextChunk();

      if ($this->getResumableUploadProcessor()->isComplete())
      {
        $fileName = $this->getResumableUploadProcessor()->getTargetFileName();
        
        if (($this->preventMeFromDoubleGlobalUpload()) && $this->haveIAlreadyUploadedThisFileAnywhere( $fileName ))
        {
          $this->getResumableUploadProcessor()->cleanUpAfterUpload();
          $this->issueConflictHeader();
        }

        if ($this->doesTheGroupAlreadyKnowThisFile( $fileName, $this->getParam('groupId', false) ))
        {
          $this->getResumableUploadProcessor()->cleanUpAfterUpload();
          $this->issueConflictHeader();
        }
        

        $station = $this->createStationFromUploadedFile(array(
          'filePath' => $fileName,
          'originalName' => $this->getResumableUploadProcessor()->getOriginalFileName()
        ));
  
        if (isset($_REQUEST['title']) && ($_REQUEST['title'] !== ''))
        {
          $station->setTitle($_REQUEST['title']);
        }


        $this->processExifFromFile($station, $fileName);

        $this->getSessionFacade()->mergeStation($station);
        $this->getFacade()->indexStation($station);
        
        echo json_encode($this->getStationDTO($station));
  
        $this->getResumableUploadProcessor()->cleanUpAfterUpload();
        die();
        
      }
    }
    catch (\ErrorException $e)
    {
      http_response_code(500);
      echo $e->getMessage();
      die('\n error in chunked upload 23984769876');
    }
      
  }


  public function editAction()
  {
    $this->needsLoggedInUser();
    $this->ensureRequestOwner();

    $stationId = $this->getParam('stationId','');
    $station = $this->getSessionFacade()->getStationById($stationId);
    $this->updateStationWithRequest($station);

    $this->getSessionFacade()->mergeStation($station);
    $this->getFacade()->indexStation($station);
    header("HTTP/1.1 303 See Other");
    header("Location: https://".$_SERVER['HTTP_HOST']."/website/group/".$this->getParam('groupId')."/station-by-id/".$station->getId());    
  }


  protected function createStationFromUploadedFile($fileData)
  {
    $station = $this->createNewUploadedStation();
    $this->getSessionFacade()->mergeStation($station);
    
    $station->setFileMd5(md5_file($fileData['filePath']));
    $station->setFileType(mime_content_type($fileData['filePath']));
    $station->setFileName($fileData['originalName']);
    $station->setFileSize(filesize($fileData['filePath']));
    $this->storeFile($fileData['filePath'], $station->getId());

    if ($station->getSimpleFileType() === 'video')
    {
      $this->getMediaHelper()->requestTranscoding($station->getId());
    }
    
    return $station;
  }
  

  public function getDiskUsageAction()
  {
    $this->getResponse()->setHash(array(
      'diskUsage' => floatval($this->getDiskUsage()),
      'unit' => 'bytes'
    ));
  }

  protected function getDiskUsage($untilTimestamp = false)
  {
    $specBuilder = new \Zeitfaden\Elasticsearch\SpecificationBuilder();
    $criteria = $specBuilder->getDiskUsageCriteria($this->getLoggedInUserId(), $untilTimestamp);
    return $this->getElasticSearchService()->aggregate($criteria, array(
      'type' => 'sum',
      'field' => 'fileSize'
    ))['value'];
  }


  protected function getCostsLastDays($countDays)
  {
    $costsGigaBytePerDay = 0.05;
    
    $dateNow = new \DateTime();
    $costs = 0;

    for ($i=1; $i <= $countDays; $i++)
    {
      $untilTimestamp = $dateNow->sub( new \DateInterval('P'.$i.'D') )->getTimestamp();
      $diskUsage = $this->getDiskUsage($untilTimestamp) / (1000 * 1000 * 1000);
      $costs = $costs + $diskUsage*$costsGigaBytePerDay;
    }
    
    return $costs;
  }


  public function getByQueryAction()
  {
    $queryString = "get 777 stations";
    $queryString = "get 777 stations at offset 4 where (@startLatitude is greater than or equal to '2a' and @startLatitude is greater than '48') or @startLatitude is greater than '2' sorted by descending @startLatitude ";
    $queryString = "get 777 \r\n stations \n\r at offset    4 where ( ((((@startLatitude > 12.34))) and @tobias equals '' and @whoopwhoop within_distance (80,90,100) and @something is not null and @startLatitude > '22.55') or @startLatitude < '11.55' or @startLatitude < 33.55) or (@startLatitude is   greater than or equal to 2 and @startLatitude is greater than 48) or @startLatitude is greater than 2 sorted by descending @startLatitude ";

    $query = $this->getQueryEngine()->translateQueryString($queryString);
    echo "<pre>";
    print_r($query);
    die('yeah inside query: '.$queryString);

    // if no criteria is given
  	//$criteriaMaker = new \Speckvisit\Specification\CriteriaMaker();
  	//$spec->setCriteria($criteriaMaker->any());
    
    
  }

}

