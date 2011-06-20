<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: MoviePersonRole.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_Item_Linkator_MoviePersonRole extends Lms_Item_Abstract 
{
    static public function getTableName() 
    {
        return '?_participants';
    }
    
    public function _customInitStructure($struct, $masterDb, $slaveDb)
    {
        parent::_customInitStructure($struct, $masterDb, $slaveDb);
        $struct->addIndex('movie_id', array('movie_id'));
        $struct->addIndex('person_id', array('person_id'));
        $struct->addIndex('role_id', array('role_id'));
    }
}