<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Comment.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_Item_Comment extends Lms_Item_Abstract 
{
    public function getTableName()
    {
        return '?_comments';
    }
}
