#!/usr/local/bin/php -q
<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: parser.php 700 2011-06-10 08:40:53Z macondos $
 */

require_once dirname(__FILE__) . '/include/init.php';

$persones = Lms_Item_Person::selectNonParsed(100);
foreach ($persones as $person) {
    $person->parse()
           ->save();
    echo ".";
}
require_once dirname(__FILE__) . '/include/end.php';