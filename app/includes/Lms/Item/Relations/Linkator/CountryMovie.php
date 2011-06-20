<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: CountryMovie.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_Item_Relations_Linkator_CountryMovie {
    static function perform()
    {
        Lms_Item_Relations::add('Linkator_CountryMovie','Movie', 'movie_id', 'movie_id', Lms_Item_Relations::ONE);
        Lms_Item_Relations::add('Linkator_CountryMovie','Country', 'country_id', 'country_id', Lms_Item_Relations::ONE );
    }
}
