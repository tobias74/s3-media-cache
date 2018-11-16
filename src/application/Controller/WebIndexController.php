<?php
namespace Zeitfaden\Controller;


class WebIndexController extends \PhpApplicationFront\AbstractActionController
{

  public function getTranscodedURI_Action()
  {
    echo $this->getParam('itemId','');
    die('good');
  }

  public function getConvertedImage_Action()
  {
    $fileNameInBucket = $_REQUEST['fileNameInBucket'];
    $imageSize = $_REQUEST['imageSize'];
    $imageUri = $this->getFileService()->getExternalUri($fileNameInBucket);
    $spec = $this->getImageSpec( $imageSize );
    $cachedImage = $this->getMediaCacheService()->getCachedImage($imageUri, $fileNameInBucket, $spec);
    echo(json_encode(array(
      "fileNameInBucket" => $cachedImage->getId()
    )));
  }

  public function getConvertedVideo_Action()
  {
    $fileNameInBucket = $_REQUEST['fileNameInBucket'];
    $format = $_REQUEST['format'];
    $quality = $_REQUEST['quality'];
    $videoUrl = $this->getFileService()->getExternalUri($fileNameInBucket);

    $flySpec = $this->getVideoSpec( $format, $quality);
    $cachedVideo = $this->getMediaCacheService()->getCachedVideo($videoUrl, $fileNameInBucket, $flySpec);
    if ($cachedVideo->isScheduled())
    {
      echo(json_encode(array(
        'status'=>'scheduled'        
      )));
    }
    else
    {
      echo(json_encode(array(
        'status'=>'done',
        'fileNameInBucket' => $cachedVideo->getId()
      )));

    }
  }



  public function getImageSpec($sizeName)
  {
    $flySpec = new \PhpMediaCache\FlyImageSpecification();
    
    switch ($sizeName)
    {
      case "small": 
        $flySpec->width=100;
        $flySpec->height=100;
        break;
        
      case "medium": 
        $flySpec->width=300;
        $flySpec->height=300;
        break;
        
      case "big": 
        $flySpec->width=800;
        $flySpec->height=800;
        break;

      case "very_big": 
        $flySpec->width=1400;
        $flySpec->height=1400;
        break;
        
      case "original":
        $flySpec->width=false;
        $flySpec->height=false;
        break;
    }
    
    return $flySpec;
  }

  public function getVideoSpec($format, $quality)
  {
    $flySpec = new \PhpMediaCache\FlyVideoSpecification();
    $flySpec->format = $format;
    $flySpec->quality = $quality;
    return $flySpec;
  }
    
    
  public function requestTranscoding($id)
  {
    $videoUrl = $this->getFileService()->getExternalUri($id);

    $pairs = [
      [
        'quality' => 'medium',
        'format' => 'webm'
      ],
      [
        'quality' => 'medium',
        'format' => 'ogg'
      ],
      [
        'quality' => 'medium',
        'format' => 'mp4'
      ],
      [
        'quality' => 'medium',
        'format' => 'jpg'
      ]
    ];
    
    foreach ($pairs as $pair)
    {
      $spec = $this->getVideoSpec( $pair['format'], $pair['quality'] );
      $values = $this->getMediaCacheService()->getCachedVideo($videoUrl, $id, $spec);
    }
  }
    


  protected function sendFile($uri)
  {
      $this->getFileSendingStrategy()->sendFile($uri);
  }



  public function indexAction()
  {
    echo $this->render('about.html',[]);
    die();
    
    header("HTTP/1.1 303 See Other");
    header("Location: ".$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/website/groups');  
    die();
  }
  
  public function aboutAction()
  {
    echo $this->render('about.html',array());
  }

  public function tocAction()
  {
    echo $this->render('toc.html',array());
  }

  public function gdprAction()
  {
    echo $this->render('privacy.html',array());
  }

  public function imprintAction()
  {
    echo $this->render('imprint.html',array());
  }


  public function faviconAction()
  {

  }




  public function auth0CallbackAction()
  {
    $userInfo = $this->getAuth0()->getUser();
    header('Location: https://' . $_SERVER['HTTP_HOST']);
    die();
  }
  
  public function logoutAction()
  {
    $this->getAuth0()->logout();
    session_destroy();
    header('Location: https://' . $_SERVER['HTTP_HOST']);
    die();
  }


  public function addToKnownUsersGetAction()
  {
    echo $this->render('add_to_known_users.html',array(
      'knownUserId' => $this->getParam('knownUserId','')  
    ));
  }

  public function addToKnownUsersPostAction()
  {
    $this->getSessionFacade()->addUserToKnownUsers($_REQUEST['knownUserId']);
    
    echo json_encode(array(
      'knownUserId' => $_REQUEST['knownUserId']  
    ));
    
  }
  
  
}

