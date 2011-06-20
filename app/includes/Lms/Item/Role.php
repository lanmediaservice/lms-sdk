<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Role.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_Item_Role extends Lms_Item_Abstract 
{
    public function getTableName()
    {
        return '?_roles';
    }

    public static function getOrCreateByName($name)
    {
        $slaveDb = Lms_Item::getSlaveDb();
        $row = $slaveDb->selectRow("SELECT * FROM " . self::getTableName() . " WHERE `name`=?", $name);
        if (!$row) {
            $item = Lms_Item::create('Role');
            $item->setName($name)
                 ->save();
            return $item;
        } else {
            return Lms_Item_Abstract::rowToItem($row);
        }
    }
}
