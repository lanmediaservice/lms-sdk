<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Person.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_Item_Person extends Lms_Item_Abstract 
{
    public function getTableName()
    {
        return '?_persones';
    }

    public static function getOrCreate($names, $url)
    {
        $slaveDb = Lms_Item::getSlaveDb();
        $row = $slaveDb->selectRow(
            "SELECT * FROM " . self::getTableName() . " WHERE `url`=? OR `name` IN(?a) OR `international_name` IN(?a) LIMIT 1", 
            $url, $names, $names
        );
        if (!$row) {
            $item = Lms_Item::create('Person');
            if (isset($names[1])) {
                $item->setInternationalName($names[1]);
            }
            $item->setName($names[0])
                 ->setUrl($url)
                 ->save();
            return $item;
        } else {
            return Lms_Item_Abstract::rowToItem($row);
        }
    }
    
    public static function selectNonParsed($limit = 10)
    {
        $slaveDb = Lms_Item::getSlaveDb();

        $rows = $slaveDb->select(
            "SELECT * FROM " . self::getTableName() . ' WHERE updated_at IS NULL LIMIT ?d', 
            $limit
        );
        return Lms_Item_Abstract::rowsToItems($rows);         
    }
    
    public function parse()
    {
        $result = Lms_Service_Kinopoisk::parsePerson($this->getUrl());
        if (isset($result['names'][0])) {
            $this->setName($result['names'][0]);
        }
        if (isset($result['names'][1])) {
            $this->setInternationalName($result['names'][1]);
        }
        if (isset($result['photos'][0])) {
            $this->setPhoto($result['photos'][0]);
        }
        $info = '';
        
        if (isset($result['born_date'])) {
            $this->setBornDate($result['born_date']);
        }
        if (isset($result['born_place'])) {
            $this->setBornPlace($result['born_place']);
        }
        if (isset($result['profile'])) {
            $this->setProfile($result['profile']);
        }
        
        $this->setInfo($info);
        $this->setUpdatedAt(gmdate('Y-m-d H:i:s'));
        return $this;
    }
    
    public function getMoviesAsStruct($hyphenated = true)
    {
        $sort = array();
        $movies = array();
        $sort = array();
        $participants = $this->getChilds('Linkator_MoviePersonRole');
        foreach ($participants as $participant) {
            $movie = $participant->getChilds('Movie');
            $movieId = $movie->getId();
            if (!isset($movies[$movieId])) {
                $movies[$movieId] = array(
                    'movie' => $movie,
                    'roles' => array()
                );
                $sort[$movieId] = 9999;
            }
            if ($sort[$movieId]>$movie->getYear()) {
                $sort[$movieId] = $movie->getYear();
            }
            $role = $participant->getChilds('Role');
            $movies[$movieId]['roles'][] = ($hyphenated && $role->getNameHyphenated())? $role->getNameHyphenated() : $role->getName();
        }
        array_multisort($sort, SORT_ASC, SORT_NUMERIC, $movies);
        
        return $movies;
        
    }
}
