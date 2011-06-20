<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: User.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_Item_User extends Lms_Item_Abstract
{
    private $_acl;

    public static function getTableName()
    {
        return '?_users';
    }
     /**
     * Map 'virtual' and 'real' fields to 'real' fields
     * @var array
     */
    static protected $_fieldsMap = array('lastname' => 'info',
                                         'firstname' => 'info',
                                         'middlename' => 'info',
                                         'fullname' => 'info',
                                         'about' => 'info', 
                                         'timezone' => 'preferences',
                                         'itemsonpage' => 'preferences',
                                         'directlinks' => 'preferences',
                                         'resultonpage' => 'preferences');
    
    /**
     * list of real fields - containers of serialized
     * data both 'virtual' and 'real' fields
     * @var array
     */
    static protected $_serializedFields = array('info',
                                                'preferences');
    
    /**
     * Guest usergroups
     * @var array
     */
    private $_guestGroup;
    
    /**
     * Initializing object's params
     *
     */
    protected function _preInsert()
    {
        if (!$this->getIp()) {
            $this->setIp(Lms_Ip::getIp());
        }
        if (!$this->getRegisterAt()) {
            $this->setRegisterAt(gmdate("Y-m-d H:i:s"));
        }
        if (!$this->getActive()) {
            $this->setActive(1);
        }
    }
    
    /**
     * Get id or create it
     *
     * @return int/NULL
     */
    function getIdOrCreate()
    {
        if (!$this->getUserName() || !$this->getAuthProviderKey()) {
            return NULL;
        }
        
        if (!$this->_scalarPkValue) {
            $this->_loadByUserName();
        }
        if (!$this->_scalarPkValue) {
            $this->save();
        }
        return $this->_scalarPkValue;
    }
    

    /**
     * (non-PHPdoc)
     * @see Lms/Item/Lms_Item_Abstract#_set($paramName, $value)
     */
    function _set($propetryName, $value)
    {
        $fieldName = $this->_getFieldNameByProperty($propetryName);
        if (!isset(self::$_fieldsMap[$fieldName])) {
            if (in_array($fieldName, self::$_serializedFields)) {
                $newValue = serialize($value);
            } else {
                $newValue = $value;
            }
            return parent::_set($fieldName, $newValue);
        } else {
            $dbField = self::$_fieldsMap[$fieldName];
            $currentValue = $this->_get($dbField);
            $currentValue[$fieldName] = $value;
            return $this->_set($dbField, $currentValue);
        }
    }
    
    /**
     * (non-PHPdoc)
     * @see Lms/Item/Lms_Item_Abstract#_get($paramName)
     */
    function _get($propetryName)
    {
        $fieldName = $this->_getFieldNameByProperty($propetryName);
        if (!isset(self::$_fieldsMap[$fieldName])) {
            $value = parent::_get($fieldName);
            if (in_array($fieldName, self::$_serializedFields)) {
                $value = unserialize($value);
            }
            return $value;
        } else {
            $dbField = self::$_fieldsMap[$fieldName];
            $currentValue = $this->_get($dbField);
            return $currentValue[$fieldName];
        }
    }
    
    /**
     * Set guest usergroup
     * 
     * @param array $groups
     * @return Lms_Item_User
     */
    function setGuestGroup($group)
    {
        $this->_guestGroup = $group;
        return $this;
    }
    
    /**
     * Get usergroup
     * @return array
     */
    function getUserGroup()
    {
        if (!$this->getUserName()) {
            return $this->_guestGroup;
        } else {
            return $this->getGroup();
        }
    }
    
    /**
     * Load data from database by name
     *
     * @return mixed
     */
    function _loadByUserName()
    {
        $sql = "SELECT * " 
             . " FROM {$this->getTableName()}"
             . " WHERE `user_name`=? AND `auth_provider_key`=?";
        $data = $this->_slaveDb->selectRow(
            $sql,
            $this->getUserName(),
            $this->getAuthProviderKey()
        );
        if ($data) {
            $this->storeData($data);
        }
        return $this;
    }

    public function setAcl($acl)
    {
        $this->_acl = $acl;
    }

    public function isAllowed($resource, $privelege)
    {
        return $this->_acl->isAllowed(
            $this->getUserGroup(), $resource, $privelege
        );
    }


    public function getFio()
    {
        $fio = $this->getLastName();
        if ($this->getFirstName()) {
            Lms_Text::enableMultiByte();
            Lms_Text::setEncoding('UTF-8');
            $fio .= " " . Lms_Text::substring($this->getFirstName(), 0, 1) . '.';
            if ($this->getMiddleName()) {
                $fio .= Lms_Text::substring($this->getMiddleName(), 0, 1) . '.';
            }
        }
        return $fio;
    } 

    public static function search($userName, $authProvider)
    {
        if (!$userName || !$authProvider) {
            return null;
        }
        $slaveDb = Lms_Item::getSlaveDb();
        
        $row = $slaveDb->selectRow("SELECT * FROM " . self::getTableName() . " WHERE user_name=? AND auth_provider_key=?", $userName, $authProvider);
        
        if (!$row) {
            return null;
        }
        
        Lms_Item_Store::setValues(
            self::getTableName(),
            $row['user_id'], 
            $row, 
            true
        ); 
        return Lms_Item::create('User', $row['user_id']);
    }
}
