<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Country.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_Item_Relations_Country {
    static function perform()
    {
        Lms_Item_Relations::add('Country', 'Linkator_CountryMovie','country_id', 'country_id', Lms_Item_Relations::MANY);
    }
}
