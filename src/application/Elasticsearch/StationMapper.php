<?php
namespace Zeitfaden\Elasticsearch;


class StationMapper
{

  public function __construct($config)
  {
      $this->config = $config;
  }

  protected function getConfig()
  {
    return $this->config;  
  }
  
  public function getIndexName()
  {
    return $this->getConfig()['indexName'];  
  }
  
  public function getTypeName()
  {
    return "station";
  }
  
  public function produceStation()
  {
    return new \Zeitfaden\Model\Station();
  }

  public function getColumnForField($fieldName)
  {
    $map = $this->getMap();
    return $map[lcfirst($fieldName)];
  }

  protected function getMap()
  {
    return array(
        'id' => 'id',
        'userId' => 'userId',
        'groupId' => 'groupId',
        'location' => 'location',
        'timestamp' => 'timestamp',
        'createdAt' => 'createdAt',
        'modifiedAt' => 'modifiedAt',
        'fileMd5' => 'stationFileMd5',
        'fileSize' => 'stationFileSize',
        'hashTagList' => 'hashTagList',
        'hashTagList.keyword' => 'hashTagList.keyword',
        'title' => 'title',
        'description' => 'description'
    );
  }


  public function mapHashToEntity($data)
  {
    $stationData = $data['_source'];
    
    $station = $this->produceStation();
    $station->setId( $stationData['id'] );
    $station->setUserId( $stationData['userId']);
    $station->setTitle( $stationData['title'] ?? '');
    $station->setDescription( $stationData['description'] );
    $station->setLatitude( $stationData['location']['lat'] );
    $station->setLongitude( $stationData['location']['lon'] );
    $station->setTimestamp(floor($stationData['timestamp']));
    $station->setModifiedAt(floor($stationData['modifiedAt']));
    $station->setCreatedAt(floor($stationData['createdAt']));
    $station->setTimezone( $stationData['timezone'] );
    $station->setGroupId( $stationData['groupId'] ?? false);
    $station->setFileMd5( $stationData['stationFileMd5'] ?? false);
    $station->setFileSize( $stationData['stationFileSize'] ?? false);
    $station->setFileType( $stationData['stationFileType'] ?? false);
    $station->setFileName( $stationData['stationFileName'] ?? false);

    $station->esSort = $data['sort'];

    return $station;
  }
    
    
  public function mapEntityToHash($station)
  {
    $hash = array(
      'id' => $station->getId(),
      'userId' => $station->getUserId(),
      'title' => $station->getTitle(),
      'description' => $station->getDescription(),
      'hashTagList' => $this->getHashTagList($station->getTitle().' '.$station->getDescription()),
      'timezone' => $station->getTimezone(),
      'timestamp' => $station->getTimestamp(),
      'modifiedAt' => $station->getModifiedAt(),
      'createdAt' => $station->getCreatedAt(),
      'location' => array('lat' => floatval($station->getLatitude()), 'lon' => floatval($station->getLongitude())),
      'groupId' => $station->getGroupId()
    );

    $hash['stationSimpleFileType'] = $station->getSimpleFileType();
    $hash['stationFileType'] = $station->getFileType();
    $hash['stationFileMd5'] = $station->getFileMd5();
    $hash['stationFileName'] = $station->getFileName();
    $hash['stationFileSize'] = $station->getFileSize();

    return $hash;
  }

  protected function getHashTagList($myString)
  {
    $hashtags = array();
    preg_match_all("/(#\w+)/u", $myString, $matches);
    if ($matches)
    {
      $hashtagsArray = array_count_values($matches[0]);
      $hashtags = array_keys($hashtagsArray);
      array_walk($hashtags, function(&$hashtag){
        $hashtag = strtolower($hashtag);
      });
    }
    return $hashtags;
  }


  public function getCreateIndexCommand()
  {

    $indexParams['index'] = $this->getIndexName();
    $indexParams['body']['settings']['number_of_shards'] = 1;
    $indexParams['body']['settings']['number_of_replicas'] = 0;
    $indexParams['body']['mappings']['station']['properties'] = [
      'id' => [
        'type' => 'keyword'
      ],
      'userId' => [
        'type' => 'keyword'
      ],
      'stationFileMd5' => [
        'type' => 'keyword'
      ],
      'title' => [
        'type' => 'text',
        'analyzer' => "description_analyzer"
      ],
      'description' => [
        'type' => 'text',
        'analyzer' => "description_analyzer"
      ],
      'hashTagList' => [
        'type' => 'text',
        'analyzer' => "description_analyzer",
         "fields" => [
            "keyword" => [ 
              "type" => "keyword"
            ]
          ]
      ],
      'stationFileSize' => [
        'type' => 'long'
      ],
      'stationFileName' => [
        'type' => 'keyword'
      ],
      'stationFileType' => [
        'type' => 'keyword'
      ],
      'stationSimpleFileType' => [
        'type' => 'keyword'
      ],
      'groupId' => [
        'type' => 'keyword'
      ],
      'location' => [
        'type' => 'geo_point'
      ],
      'timestamp' => [
        'type' => 'date',
        'format' => 'epoch_second'
      ],
      'modifiedAt' => [
        'type' => 'date',
        'format' => 'epoch_second'
      ],
      'createdAt' => [
        'type' => 'date',
        'format' => 'epoch_second'
      ]
      
    ];

    $indexParams['body']['settings']['analysis'] = [
      'filter' => [
          'tweet_filter' => [
              'type' => "word_delimiter",
              'type_table' => ["# => ALPHANUM", "@ => ALPHANUM"]
          ],
      ],
      'analyzer' => [
          'tweet_analyzer' => [
              'type' => "custom",
              'tokenizer' => "whitespace",
              'filter' => ["lowercase", "tweet_filter"]
          ],
          'description_analyzer' => [
              'type' => "custom",
              'tokenizer' => "whitespace",
              'filter' => ["lowercase", "tweet_filter"]
          ]
      ]
    ];

    return $indexParams;
  }

    
}