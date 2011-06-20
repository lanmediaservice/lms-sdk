<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: User.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_User
{
    private static $_acl;

    private static $_identified = false;

    /**
     * @var Lms_Item_User
     */
    private static $_userInstance;
    
    /**
     * Default usergroup
     * @var array
     */
    private static $_defaultGroup;
    
    /**
     * Guest usergroup
     * @var array
     */
    private static $_guestGroup;
    
    public static function getUser()
    {
        if (!self::$_userInstance) {
            self::$_userInstance = Lms_Item::create('User');
            self::initUserInstance();
        }
        $auth = Lms_MultiAuth::getInstance();
        if (!self::$_identified && $auth->hasIdentity()) {
            self::$_userInstance->setUserName($auth->getIdentity())
                ->setAuthProviderKey($auth->getAuthProviderKey())
                ->getIdOrCreate();
            self::$_identified = true;
        }
        return self::$_userInstance;
    }

    public static function setUser($userIdOrItem)
    {
        if ($userIdOrItem instanceof Lms_Item_User) {
            $user = $userIdOrItem;
        } else {
            $user = Lms_Item::create('User', $userIdOrItem);
        }
        self::$_userInstance = $user;
        self::initUserInstance();
        $auth = Lms_MultiAuth::getInstance();
        $storage = new Zend_Auth_Storage_NonPersistent();
        $auth->setStorage($storage);
        $auth->forceAuthenticate($user->getUserName(), $user->getAuthProviderKey());
    }
    
    /**
     * Set guest user groups
     * 
     * @param array $groups
     * @return Lms_Item_User
     */
    function setDefaultGroup($group)
    {
        self::$_defaultGroup = $group;
    }

    /**
     * Set guest user groups
     * 
     * @param array $groups
     * @return Lms_Item_User
     */
    
    function setGuestGroup($group)
    {
        self::$_guestGroup = $group;
    }

    function setAcl($acl)
    {
        self::$_acl = $acl;
    }
    
    private static function initUserInstance()
    {
        self::$_userInstance->setGuestGroup(self::$_guestGroup)
                            ->setUserGroup(self::$_defaultGroup)
                            ->setAcl(self::$_acl);
    }
}
