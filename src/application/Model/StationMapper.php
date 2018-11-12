<?php
namespace Zeitfaden\Model;


class StationMapper extends \Speckvisit\Crud\MongoDb\Mapper\UnderscoreMapper
{
    public function instantiate($document)
    {
        $resultHash = json_decode(\MongoDB\BSON\toJSON(\MongoDB\BSON\fromPHP($document)),true);
        $station = parent::instantiate($document);

        $station->setLatitude( isset($resultHash['location']['coordinates'][1]) ? $resultHash['location']['coordinates'][1] : null);
        $station->setLongitude( isset($resultHash['location']['coordinates'][0]) ? $resultHash['location']['coordinates'][0] :  null);

        return $station;
    }
    
    public function mapToDocument($station)
    {
        $document = parent::mapToDocument($station);
        $document['location'] = array('type' => 'Point', 'coordinates' => array(floatval($station->getLongitude()), floatval($station->getLatitude()) ));
        $document['simple_file_type'] = $station->getSimpleFileType();
        return $document;
    }
    
}