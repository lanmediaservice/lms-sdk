<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Movies.php 700 2011-06-10 08:40:53Z macondos $
 * @package Api
 */
 
/**
 * @package Api
 */
class Lms_Api_Server_Movies extends Lms_Api_Server_Abstract
{

    public static function getMovie($params)
    {
        try {
            $movieId = $params['movie_id'];
            Lms_Item_Preloader::load('Movie', array('Country', 'Genre', 'Role', 'Person (name, photo)', 'Rating'), 'movie_id', array($movieId)); 
            $movie = Lms_Item::create('Movie', $movieId);

            $baseUrl = dirname(Lms_Application::getRequest()->getBaseUrl());
            $frontController = Lms_Application::initFrontController();
            $frontController->setBaseUrl($baseUrl);
            Lms_Application::initRoutes();
            $view = Lms_Application::initView();
            $view->movie = $movie;
            $renderedHtml = $view->render('movie.phtml');
            $result = array(
                'html' => $renderedHtml,
                'movie_id' => $movie->getId()
            );
            return new Lms_Api_Response(200, null, $result);
        } catch (Lms_Service_DataParser_Exception $e) {
            return new Lms_Api_Response(500, $e->getMessage());
        }
    }

    public static function getPerson($params)
    {
        try {
            $person = Lms_Item::create('Person', $params['person_id']);
            $baseUrl = dirname(Lms_Application::getRequest()->getBaseUrl());
            $frontController = Lms_Application::initFrontController();
            $frontController->setBaseUrl($baseUrl);
            Lms_Application::initRoutes();
            $view = Lms_Application::initView();
            $view->person = $person;
            $renderedHtml = $view->render('person.phtml');
            $result = array(
                'html' => $renderedHtml,
                'person_id' => $person->getId()
            );
            return new Lms_Api_Response(200, null, $result);
        } catch (Lms_Service_DataParser_Exception $e) {
            return new Lms_Api_Response(500, $e->getMessage());
        }
    }
}
