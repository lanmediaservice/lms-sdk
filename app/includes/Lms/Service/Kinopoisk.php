<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Kinopoisk.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_Service_Kinopoisk
{
    private static $_parserInstance;

    public static function getParser()
    {
        if (!self::$_parserInstance) {
            self::setParser();
        }
        return self::$_parserInstance;
    }

    public static function setParser($parser = null)
    {
        if ($parser === null) {
            $httpClient = Lms_Application::getHttpClient();
            $requestClient = new Lms_PhpHttpRequest_Client($httpClient);
            $parserService = new Lms_Service_DataParser($requestClient,
                                                        $httpClient);
            $config = Lms_Application::getConfig('parser_service');
            $parserService->setServiceUrl($config['url']);
            $parserService->setAuthData(
                $config['username'],
                $config['password']
            );
            self::$_parserInstance = $parserService;
        } else {
            self::$_parserInstance = $parser;
        }
    }


    public static function constructPath($action, $params)
    {
        switch($action){
            case 'search':
                $text = urlencode(
                    Lms_Translate::translate('UTF-8', 'CP1251', $params['name'])
                );
                return "http://www.kinopoisk.ru/index.php"
                     . "?kp_query=$text";
                break;
            case 'film':
                return "http://www.kinopoisk.ru/level/1/film/{$params['id']}/";
                break;
        }
    }

    public static function getKinopoiskIdFromUrl($url)
    {
        if (preg_match('{/level/1/film/(\d+)}', $url, $matches)) {
            return $matches[1];
        } else {
            throw new Lms_Exception("Invalid kinopoisk url: $url");
        }
    }

    public static function searchMovie($queryText)
    {
        $parserService = self::getParser();
        $results = array();
        $url = self::constructPath('search', array('name'=>$queryText));
        $currentResult = $parserService->parseUrl(
            $url,
            'kinopoisk',
            'search_results',
            array('film')
        );
        if (isset($currentResult['attaches']['film'])) {
            $film = $currentResult['attaches']['film'];
            $url = $currentResult['suburls']['film'][2];
            $currentResult = array(
                "names" => $film['names'],
                "year" => $film['year'],
                "url" => $url
            );
            $results[] = $currentResult;
        } else {
            $results = $currentResult['items'];
        }
        foreach ($results as &$result) {
            $result['kinopoisk_id'] = self::getKinopoiskIdFromUrl(
                $result['url']
            );
            unset($result['url']);
        }
        return $results;
    }

    public static function parseMovie($kinopoiskId)
    {
        $url = self::constructPath('film', array('id'=>$kinopoiskId));
        $parserService = self::getParser();
        $info = $parserService->parseUrl($url, 'kinopoisk', 'film');
        if ($info) {
            $cast = array();
            $directors = array();
            foreach ($info['persones'] as $person) {
                switch ($person['role']) {
                    case 'режиссер':
                        $directors[] = array_pop($person['names']);
                        break;
                    case 'актер': // break intentionally omitted
                    case 'актриса':
                        $cast[] = array_pop($person['names']);
                        break;
                    default:
                        break;
                }
            }
            $info['directors'] = $directors;
            $info['cast'] = $cast;
            $info['kinopoisk_id'] = $kinopoiskId;
        }
        return $info;
    }

    public static function parsePerson($url)
    {
        $parserService = self::getParser();
        $info = $parserService->parseUrl($url, 'kinopoisk', 'person');
        return $info;
    }
}

