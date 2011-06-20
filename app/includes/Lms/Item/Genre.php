<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Genre.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_Item_Genre extends Lms_Item_Abstract 
{
    public function getTableName()
    {
        return '?_genres';
    }

    public static function getOrCreateByName($name)
    {
        $slaveDb = Lms_Item::getSlaveDb();
        $row = $slaveDb->selectRow("SELECT * FROM " . self::getTableName() . " WHERE `name`=?", $name);
        if (!$row) {
            $item = Lms_Item::create('Genre');
            $item->setName($name)
                 ->save();
            return $item;
        } else {
            return Lms_Item_Abstract::rowToItem($row);
        }
    }
    
    public static function select()
    {
        $slaveDb = Lms_Item::getSlaveDb();
        $rows = $slaveDb->select("SELECT * FROM " . self::getTableName() . " ORDER BY `name` ");
        return Lms_Item_Abstract::rowsToItems($rows);
    }    
}
