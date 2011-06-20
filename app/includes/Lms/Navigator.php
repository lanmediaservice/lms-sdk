<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Navigator.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_Navigator
{
    const STATUS_DEFAULT = 0;
    const STATUS_INCLUDED = 1;
    const STATUS_EXCLUDED = 2;
    
    public static function getStatus($conditions, $field, $value)
    {
        if (!is_array($conditions)) {
            return self::STATUS_DEFAULT;
        }
        foreach ($conditions as $condition) {
            if ($field == $condition['field'] && $condition['argument']==$value) {
                if ($condition['operator'] == 'equal') {
                    return self::STATUS_INCLUDED;
                } else if ($condition['operator'] == 'notequal') {
                    return self::STATUS_EXCLUDED;
                } else {
                    return self::STATUS_DEFAULT;
                }
            }
        } 
    }
    
    public static function uniqueConditions($conditions)
    {
        $index = array();
        $uniqueConditions = array_reverse($conditions);
        foreach ($uniqueConditions as $num => $condition) {
            $field = $condition['field'];
            $argument = $condition['argument'];
            if (isset($index[$field][$argument])) {
                unset($uniqueConditions[$num]);
            } else {
                $index[$field][$argument] = 1;
            }
        }
        return array_reverse($uniqueConditions);
    }
}