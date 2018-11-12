<?php
namespace Zeitfaden\Elasticsearch;


class SpecificationBuilder
{
  public function getDiskUsageCriteria($userId, $timestamp = false)
  {
    $criteriaMaker = new \Speckvisit\Specification\CriteriaMaker();
    $criteria = $criteriaMaker->equals('userId', $userId);
    
    if ($timestamp !== false)
    {
      $criteria = $criteria->logicalAnd( $criteriaMaker->lessOrEqual('createdAt', $timestamp) );
    }

    return $criteria;
  }

  
  public function getElasticSpecificationByRequest($request)
  {
    $criteriaMaker = new \Speckvisit\Specification\CriteriaMaker();
    $criteria = $criteriaMaker->any();
    
    $searchText = $request['searchText'] ?? false;
    if ($searchText && ($searchText != ''))
    {
      $textCriteria = $criteriaMaker->equals('description', $searchText);
      $textCriteria = $textCriteria->logicalOr($criteriaMaker->equals('title', $searchText));
      $criteria = $criteria->logicalAnd( $textCriteria);
    }    
    
    $criteria = $criteria->logicalAnd( $criteriaMaker->equals('groupId', $request['groupId'] ?? '') );
    
    $fromDateString = $request['fromDateString'] ?? false;
    $untilDateString = $request['untilDateString'] ?? false;
    if ($fromDateString && $untilDateString)
    {
      $timeObjectFrom = \DateTime::createFromFormat('Y-m-d H:i:s', $fromDateString.' 00:00:00');
      $timeObjectUntil = \DateTime::createFromFormat('Y-m-d H:i:s', $untilDateString.' 23:59:59');
      $criteria = $criteria->logicalAnd( $criteriaMaker->between('timestamp', intval($timeObjectFrom->getTimestamp()), intval($timeObjectUntil->getTimestamp()) ));
      
    }

    
    if ( ($request['useMap'] ?? 'no') === 'yes') {
      
      $latitude = $request['latitude'];
      $longitude = $request['longitude'];
      $maxDistance = $request['maxDistance'];
      if ($latitude && $longitude && $maxDistance)
      {
        $criteria = $criteria->logicalAnd( $criteriaMaker->withinDistance('location', array(
          'latitude' => $latitude,
          'longitude' => $longitude
        ), $maxDistance));
      }

    }

    $userId = $request['userId'] ?? false;
    if ($userId)
    {
      $criteria = $criteria->logicalAnd( $criteriaMaker->equals('userId', $userId) );
    }

    



    $sorting = $request['sorting'] ??'intoThePast';
    switch ($sorting)
    {
      case 'intoTheFuture':
        $orderer = array(
          'sortType' => 'byField',
          'sortOrder' => 'asc',
          'sortField' => 'timestamp'
        );
        break;

      case 'intoThePast':
        $orderer = array(
          'sortType' => 'byField',
          'sortOrder' => 'desc',
          'sortField' => 'timestamp'
        );
        break;
        
      case 'lastModified':
        $orderer = array(
          'sortType' => 'byField',
          'sortOrder' => 'desc',
          'sortField' => 'modifiedAt'
        );
        break;

      case 'leastModified':
        $orderer = array(
          'sortType' => 'byField',
          'sortOrder' => 'asc',
          'sortField' => 'modifiedAt'
        );
        break;
        
      case 'lastCreated':
        $orderer = array(
          'sortType' => 'byField',
          'sortOrder' => 'desc',
          'sortField' => 'createdAt'
        );
        break;

      case 'leastCreated':
        $orderer = array(
          'sortType' => 'byField',
          'sortOrder' => 'asc',
          'sortField' => 'createdAt'
        );
        break;

      case 'distanceToPin':
        if (($latitude === false) || ($longitude === false))
        {
          throw new \ErrorException('ordering by distance needs a position');  
        }
        
        $orderer = array(
          'sortType' => 'byDistanceToPin',
          'sortField' => 'location',
          'latitude' => $latitude,
          'longitude' => $longitude
        );
        break;
        
      default:
        throw new \ErrorException('');
        break;
        
    }


    
    $spec = array(
      'criteria' => $criteria,
      'offset' => $request['offset'] ?? 0,
      'limit' =>  $request['limit'] ?? 100,
      'sorting' => $orderer
    ); 
    
    if ($request['searchAfter'] ?? false)
    {
      $spec['search_after'] = json_decode($request['searchAfter']);
    }
    
    //print_r($spec);
    
    return $spec;    
  }

    
}