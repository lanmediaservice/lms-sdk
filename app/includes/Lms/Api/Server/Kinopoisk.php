<?php
/**
 * Интерфейс операций поиска и парсинга информации с kinopoisk.ru 
 *
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Kinopoisk.php 700 2011-06-10 08:40:53Z macondos $
 * @package Api
 */
 
/**
 * @package Api
 */
class Lms_Api_Server_Kinopoisk extends Lms_Api_Server_Abstract
{

    public static function searchMovie($params)
    {
        try {
            $results = Lms_Service_Kinopoisk::searchMovie($params['query']);
            return new Lms_Api_Response(200, null, $results);
        } catch (Lms_Service_DataParser_Exception $e) {
            return new Lms_Api_Response(500, $e->getMessage());
        }
    }

    public static function parseMovie($params)
    {
        try {
            $result = Lms_Service_Kinopoisk::parseMovie($params['kinopoisk_id']);
            $result['posters'][0] = Lms_Thumbnail::thumbnail($result['posters'][0]);
            $result['duplicates'] = Lms_Item_Movie::getDuplicates($kinopoiskId);
            if ($result['duplicates']) {
                $translate = Lms_Application::getTranslate();
                $lang = Lms_Application::getLang();
                foreach ($result['duplicates'] as &$duplicate) {
                    $duplicate['created_at'] = Lms_Date::timeAgo(
                        $duplicate['created_at'], 1, $translate, $lang
                    );
                }
            }
            return new Lms_Api_Response(200, null, $result);
        } catch (Lms_Service_DataParser_Exception $e) {
            return new Lms_Api_Response(500, $e->getMessage());
        }
    }
}
