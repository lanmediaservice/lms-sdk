<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Rating.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_Item_Rating extends Lms_Item_Abstract 
{
    const RATING_IMDB = 'imdb';
    const RATING_KINOPOISK = 'kinopoisk';
    
    public function getTableName()
    {
        return '?_ratings';
    }

    public function _customInitStructure($struct, $masterDb, $slaveDb)
    {
        parent::_customInitStructure($struct, $masterDb, $slaveDb);
        $struct->addIndex('movie_id', array('movie_id'));
    }
    
    public function getLink()
    {
        if ($uid = $this->getSystemUid()) {
            switch ($this->getSystem()) {
                case self::RATING_IMDB:
                    return "http://www.imdb.com/title/tt" . sprintf('%07d', $uid) . "/";
                    break;
                case self::RATING_KINOPOISK:
                    return "http://www.kinopoisk.ru/level/1/film/$uid/";
                    break;
            }
        }
        return null;
    }
}
