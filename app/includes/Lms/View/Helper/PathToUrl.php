<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: PathToUrl.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_View_Helper_PathToUrl extends Zend_View_Helper_Abstract
{
    function pathToUrl($filePath)
    {
        $map = Lms_Application::getConfig('paths_map');
        if ($map) {
            foreach ($map as $from => $to) {
                if (substr($filePath, 0, strlen($from))==$from) {
                    $filePath = $to . substr($filePath, strlen($from));
                }
            }
        }
        $filePath = str_replace('\\', '/', $filePath);
        $url = str_replace($_SERVER['DOCUMENT_ROOT'], '', $filePath);
        return $url;
    }
    
}
