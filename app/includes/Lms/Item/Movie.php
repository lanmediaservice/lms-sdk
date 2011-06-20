<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Movie.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_Item_Movie extends Lms_Item_Abstract 
{
    public function getTableName()
    {
        return '?_movies';
    }
    
    public function getTrailer()
    {
        return unserialize($this->_get('trailer'));
    }

    public function setTrailer($value)
    {
        return $this->_set('trailer', serialize($value));
    } 

    protected function _preInsert() 
    {
        if (!$this->getAddedAt()) {
            $this->setAddedAt(gmdate('Y-m-d H:i:s'));
        }
        if (!$this->getIp()) {
            $this->setIp(Lms_Ip::getIp());
        }
        if (!$this->getUserId()) {
            $this->setUserId(Lms_User::getUser()->getUserId());
        }
    } 

    public static function fromInfo($info)
    {
        $movie = Lms_Item::create('Movie');
        $movie->setName($info['names'][0])
              ->setInfo($info['description'])
              ->setYear($info['year'])
              ->setCover($info['posters'][0]);
        if (isset($info['names'][1])) {
            $movie->setInternationalName($info['names'][1]);
        }
        if (isset($info['trailer'])) {
            $movie->setTrailer($info['trailer']);
        }
        $movie->save();
        
        foreach ($info['genres'] as $genreName) {
            $genre = Lms_Item_Genre::getOrCreateByName($genreName);
            $movie->add($genre);
        }
        
        foreach ($info['countries'] as $countryName) {
            $country = Lms_Item_Country::getOrCreateByName($countryName);
            $movie->add($country);
        }
        
        foreach ($info['persones'] as $personStruct) {
            $person = Lms_Item_Person::getOrCreate($personStruct['names'], $personStruct['url']);
            $role = Lms_Item_Role::getOrCreateByName($personStruct['role']);
            $movie->add($person, $role);
        }
        $rating = Lms_Item::create('Rating');
        $rating->setSystem(Lms_Item_Rating::RATING_KINOPOISK)
               ->setSystemUid($info['kinopoisk_id'])
               ->setCount(@$info['rating']['kinopoisk']['count'])
               ->setValue(@$info['rating']['kinopoisk']['value']);
        $movie->add($rating);
        
        $rating = Lms_Item::create('Rating');
        $rating->setSystem(Lms_Item_Rating::RATING_IMDB)
               ->setCount(@$info['rating']['imdb']['count'])
               ->setValue(@$info['rating']['imdb']['value']);
        $movie->add($rating);
        
        return $movie;
    } 

    public static function getDuplicates($kinopoiskId)
    {
        return false;
        /*
        $sql = 'SELECT wish_id, subject, created_at, status '
             . 'FROM ' . Lms_Item::getTableName('Wish')
             . ' INNER JOIN ' . self::getTableName() . ' USING(wish_id) '
             . ' WHERE is_deleted=0 AND kinopoisk_id=?d {AND wish_id<?d}';
        $result = $this->_slaveDb->select(
             $sql,
             $this->getKinopoiskId(),
             $this->isSaved()? $this->getId() : DBSIMPLE_SKIP
        );
        if (!count($result)) {
            return false;
        } else {
            return $result;
        }*/
    }

    static function select(&$total,
        $page = 0, $size = 20, 
        $sortField = 'added_at', $sortDir = 'DESC',
        $conditions = array()
    )
    {
        $page = $page > 0 ? (int)$page : 1;
        $offset = ($page - 1) * $size;
        
        $slaveDb = Lms_Item::getSlaveDb();

        $conditionsStatement = '';
        $extraWhereStatement = array();
        if ($conditions) {
            $sqlBuilder = new Lms_SqlBuilder();
            $sqlBuilder->setSafeMode(true);
            $sqlBuilder->allow(
                array('ident', 'equal', 'notequal', 'gt', 'egt', 'lt', 
                      'elt', 'like', 'contain', 'notcontain', 'and', 
                      'or', 'in', 'notin', 'list')
            );
            $normalConditions = array();
            foreach ($conditions as $condition) {
                $normalCondition = array();
                switch ($condition['field']) {
                    case 'genre':
                        switch ($condition['operator']) {
                            case 'equal':
                                $extraWhereStatement[] = " (movie_id IN (SELECT movie_id FROM movies_genres WHERE `genre_id`=" . (int) $condition['argument'] . ")) ";
                                break;
                            case 'notequal':
                                $extraWhereStatement[] = " (movie_id NOT IN (SELECT movie_id FROM movies_genres WHERE `genre_id`=" . (int) $condition['argument'] . ")) ";
                                break;
                        }
                        $condition['argument'] = null;
                        break;
                    case 'country':
                        switch ($condition['operator']) {
                            case 'equal':
                                $extraWhereStatement[] = " (movie_id IN (SELECT movie_id FROM movies_countries WHERE `country_id`=" . (int) $condition['argument'] . ")) ";
                                break;
                            case 'notequal':
                                $extraWhereStatement[] = " (movie_id NOT IN (SELECT movie_id FROM movies_countries WHERE `country_id`=" . (int) $condition['argument'] . ")) ";
                                break;
                        }
                        $condition['argument'] = null;
                        break;
                    case 'names':
                        switch ($condition['operator']) {
                            case 'contain':
                                $extraWhereStatement[] = $sqlBuilder->parse(array('or' => array(
                                    array('contain' => array(
                                        array('ident' => 'name'),
                                        $condition['argument']
                                    )),
                                    array('contain' => array(
                                        array('ident' => 'international_name'),
                                        $condition['argument']
                                    ))
                                )));
                                break;
                        }
                        $condition['argument'] = null;
                        break;
                    default:
                        break;
                } 
                if ($condition['argument']!==null) {
                    $normalCondition[$condition['operator']] = array(
                        array('ident' => $condition['field']),
                        $condition['argument']
                    );
                    $normalConditions[] = $normalCondition;
                }
            }
            try {
                if (count($normalConditions)) {
                    $conditionsStatement = $sqlBuilder->parse(
                        array('and' => $normalConditions)
                    );
                }
            } catch (Lms_SqlBuilder_Exception $e) {
                Lms_Debug::debug('Conditions: ' . print_r($conditions, true));
                Lms_Debug::debug('Normal conditions: ' . print_r($normalConditions, true));
                throw $e;
            }
        }
        
        $extraJoin = '';
        if ($sortField=="rating_imdb" || $sortField=="rating_kinopoisk") {
            $extraJoin = "INNER JOIN " . Lms_Item_Rating::getTableName() . " USING(movie_id) ";
            switch ($sortField) {
                case "rating_imdb":
                    $extraWhereStatement[] = " `system`='" . Lms_Item_Rating::RATING_IMDB . "' ";
                    break;
                case "rating_kinopoisk":
                    $extraWhereStatement[] = " `system`='" . Lms_Item_Rating::RATING_KINOPOISK . "' ";
                    break;
            }
            $sortField = "value";
        }

        $restrictStatement = self::getRestrictStatement();
        if ($restrictStatement) {
            $conditionsStatement .= ($conditionsStatement? ' AND ' : '') . $restrictStatement;
        }
        if (count($extraWhereStatement)) {
            $conditionsStatement .= ($conditionsStatement? ' AND ' : '') . implode(" AND ", $extraWhereStatement);
        }
        
        
        $sql = "SELECT m.* FROM " . self::getTableName(). " m " . $extraJoin
             . ($conditionsStatement? " WHERE $conditionsStatement" : '')
             . " ORDER BY ?# $sortDir {LIMIT ?d, ?d}";

        $rows = $slaveDb->selectPage(
            $total, $sql, 
            $sortField, $offset, $size? $size : DBSIMPLE_SKIP
        );
        $items = Lms_Item_Abstract::rowsToItems($rows);
        
        $moviesIds = array();
        foreach ($items as $item) {
            $moviesIds[] = $item->getId();
        }
        //Lms_Item_Preloader::load('Movie', array('Country', 'Genre', 'Role', 'Person (name)'), 'movie_id', $moviesIds); 
        Lms_Item_Preloader::load('Linkator_MoviePersonRole', array('Role', 'Person (name)'), 'movie_id', $moviesIds); 
        Lms_Item_Preloader::load('Linkator_GenreMovie', array('Genre'), 'movie_id', $moviesIds); 
        Lms_Item_Preloader::load('Linkator_CountryMovie', array('Country'), 'movie_id', $moviesIds); 
        
        return $items;
    } 

    static public function selectNotLocalized($limit = 1)
    {
        $slaveDb = Lms_Item::getSlaveDb();
        $sql = "SELECT * FROM " . self::getTableName() 
             . " WHERE  `trailer` IS NOT NULL AND `trailer_localized` IS NULL LIMIT ?d";
        $rows = $slaveDb->select($sql, $limit);
        $items = Lms_Item_Abstract::rowsToItems($rows);
        return $items;
    }
    
    static private function getRestrictStatement()
    {
        if (!Lms_User::getUser()->isAllowed('movie', 'moderate')) {
            $userId = Lms_User::getUser()->getUserId();
            $orStatement = '';
            if ($userId) {
                $orStatement = ' OR user_id=' . (int) $userId;
            }
            return "(`access`='public' $orStatement)";
        } else {
            return '';
        }
    }
    
    public function getCountriesAsString($separator = ', ')
    {
        $countries = $this->getChilds('Country/@name');
        return implode($separator, $countries);
    }

    public function getGenresAsString($separator = ', ')
    {
        $genres = $this->getChilds('Genre/@name');
        return implode($separator, $genres);
    }
    
    public function getDirectorAsString($separator = ', ')
    {
        $directors = array();
        $participants = $this->getChilds('Linkator_MoviePersonRole');
        foreach ($participants as $participant) {
            $roleName = $participant->getChilds('Role/@name');
            if ($roleName=='режиссер') {
                $directors[] = $participant->getChilds('Person/@name');
            }
        }
        return implode($separator, $directors);
    }
    
    public function getCastAsString($separator = ', ')
    {
        $cast = array();
        $participants = $this->getChilds('Linkator_MoviePersonRole');
        $order = array();
        foreach ($participants as $participant) {
            $roleName = $participant->getChilds('Role/@name');
            if ($roleName=='актер' || $roleName=='актриса') {
                $cast[] = $participant->getChilds('Person/@name');
                $order[] = $participant->getId();
            }
        }
        array_multisort($order, SORT_ASC, SORT_NUMERIC, $cast);
        return implode($separator, $cast);
    }

    public function getPersonesAsStruct($hyphenated = true)
    {
        $sort = array();
        $persones = array();
        $sort = array();
        $order = array();
        $participants = $this->getChilds('Linkator_MoviePersonRole');
        foreach ($participants as $participant) {
            $person = $participant->getChilds('Person');
            $personId = $person->getId();
            if (!isset($persones[$personId])) {
                $persones[$personId] = array(
                    'person' => $person,
                    'roles' => array()
                );
                $sort[$personId] = 99;
            }
            $role = $participant->getChilds('Role');
            if ($sort[$personId]>$role->getSort()) {
                $sort[$personId] = $role->getSort();
            }
            $order[$personId] = $participant->getId();
            $persones[$personId]['roles'][] = ($hyphenated && $role->getNameHyphenated())? $role->getNameHyphenated() : $role->getName();
        }
        array_multisort($sort, SORT_ASC, SORT_NUMERIC, $order, SORT_ASC, SORT_NUMERIC, $persones);
        
        return $persones;
    }

    public function getRatings()
    {
        return $this->getChilds('Rating');
    }
}
