<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: UserKey.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_Item_UserKey extends Lms_Item_Abstract {
    public function getTableName()
    {
        return '?_users_keys';
    }
    
    public function resetToken()
    {
        $token = Lms_Text::generateString(20);
        $this->setToken($token);
        $this->setTokenExpiredAt(gmdate('Y-m-d H:i:s', time()+24*3600));
        return $this;
    }

}