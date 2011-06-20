<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: NavUrl.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_View_Helper_NavUrl extends Zend_View_Helper_Abstract
{
    const MERGE_REPLACE = 1;
    const MERGE_REPLACE_RECURSIVE = 2;
    const MERGE_ADD = 3;

    private static function arrayDiffAssocRecursive($array1, $array2) 
    { 
        foreach($array1 as $key => $value) { 
            if (is_array($value)) { 
                if (!isset($array2[$key])) { 
                    $difference[$key] = $value; 
                } elseif (!is_array($array2[$key])) { 
                    $difference[$key] = $value; 
                } else { 
                    $newDiff = self::arrayDiffAssocRecursive($value, $array2[$key]); 
                    if($newDiff !== null) { 
                          $difference[$key] = $newDiff; 
                    } 
                } 
            } elseif (!array_key_exists($key, $array2) || ($array2[$key] != $value)) { 
                $difference[$key] = $value; 
            } 
        } 
        return !isset($difference) ? null : $difference; 
    } 
    
    public function navUrl($param, $value, $mode = self::MERGE_REPLACE, $unique = true, $escape = true)
    {
        $currentView = $this->view->view;
        if ($param === null) {
            $currentViewPart = &$currentView;
        } else {
            $currentViewPart = &$currentView[$param];
        }
        
        switch ($mode) {
            case self::MERGE_REPLACE:
                $currentViewPart = $value;
                break;
            case self::MERGE_REPLACE_RECURSIVE:
                $currentViewPart = array_merge_recursive($currentViewPart, $value);
                break;
            case self::MERGE_ADD:
                $currentViewPart[] = $value;
                break;
        }
        $currentView['conditions'] = Lms_Navigator::uniqueConditions($currentView['conditions']);
        
        $diffView = self::arrayDiffAssocRecursive($currentView, $this->view->views['default']);
        
        $newView = count($diffView)? Zend_Json::encode($diffView) : null;
        $url = $this->view->url(array('action'=>'index', 'view'=>$newView, 'search_text'=>null, 'page' => null), null, false); 
        return $escape? $this->view->escape($url) : $url;
    }
}
