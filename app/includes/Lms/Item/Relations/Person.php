<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Person.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_Item_Relations_Person {
    static function perform()
    {
         Lms_Item_Relations::add('Person', 'Linkator_MoviePersonRole', 'person_id', 'person_id', Lms_Item_Relations::MANY);
    }
}
