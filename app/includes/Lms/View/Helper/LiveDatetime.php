<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: LiveDatetime.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_View_Helper_LiveDatetime extends Zend_View_Helper_Abstract
{
    const MODE_DEFAULT = 1;
    const MODE_DATE = 2;
    const MODE_AGO = 3;
    public function liveDatetime($datetimeUTC, $mode = self::MODE_DEFAULT)
    {
        $translate = Lms_Application::getTranslate();
        $datetime = new DateTime($datetimeUTC, new DateTimeZone('UTC'));
        $timestamp = $datetime->format('U');
        $date = new Zend_Date($timestamp, Zend_Date::TIMESTAMP);
        //Zend_Date::setOptions(array('format_type' => 'php'));
        //return $date->toString('d MMMM yyyy г. HH:mm');
        
        switch (true) {
            case ($date->isToday()):
                $dateStr = $date->toString($translate->translate('dateformat_today'));
                break;
            case ($date->isYesterday()):
                $dateStr = $date->toString($translate->translate('dateformat_yesterday'));
                break;
            case ($date->isLater(new Zend_Date(mktime(0, 0, 0, 1, 1, date('Y')), Zend_Date::TIMESTAMP))):
                $dateStr = $date->toString($translate->translate('dateformat_thisyear'));
                break;
            default:
                $dateStr = $date->toString($translate->translate('dateformat_default'));
                break;
        }
        
        $ta = Lms_Date::timeAgo(date('Y-m-d H:i:s', $timestamp), 1, $translate, Lms_Application::getLang(), 'ymdhi', 0.5); 
        
        switch ($mode) {
            case self::MODE_DEFAULT:
                $outStr = "$dateStr ($ta)";
                break;
            case self::MODE_DATE:
                $outStr = $dateStr;
                break;
            case self::MODE_AGO:
                $outStr = $ta;
                break;
        }
        
        $element = new Lms_View_Widget_Span();
        $element->setTitle($date->toString($translate->translate('dateformat_full')));
        $element->setValue($outStr);
        $element->setClass("live-datetime");
        $element->setMode($mode);
        $element->setTime($timestamp);
        
        return $element;
    }
    
}
