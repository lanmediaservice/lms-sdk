<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Movies.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_Controller_Movies extends Zend_Controller_Action
{
    function init()
    {
        $this->view->thisPath = $this->getRequest()->getRequestUri();
        $this->view->isFrame = $this->_getParam('frame');
        if ($this->view->isFrame) {
            $this->_helper->layout->setLayout('frame');
            $this->view->thisPath = preg_replace('{(\?|&)frame=[^&\?]+}i', "", $this->view->thisPath);
        }
        $this->getResponse()->setHeader('Expires', '-1', true);
        $this->getResponse()->setHeader('Cache-Control', 'no-cache', true);
        $this->getResponse()->setHeader('Pragma', 'no-cache', true);
        $this->view->title = "Фильмы";
        $this->view->thisUrl = $this->getRequest()->getRequestUri();
    }
    
    public function indexAction()
    {
        $this->view->title .= " — Каталог";
        $page = $this->_getParam('page');
        if (!$page) {
            $page = 1;
        }
        $view = $this->_getParam('view', 'default');
        if (preg_match('#{.*?}#i', $view)) {
            $this->view->view = Zend_Json::decode($view);
        } else {
            $this->view->view = $view;
        }
        $sortField = 'added_at';
        $sortDir = 'desc';
        $conditions = array();
        $this->view->views = array();

        $views = Lms_Application::getConfig('views', 'movies');
        $userGroup = Lms_User::getUser()->getUserGroup();
        foreach (Lms_Application::getConfig('views_groups', 'movies', $userGroup) as $viewCode) {
            $this->view->views[$viewCode] = $views[$viewCode];
        } 
        
        if (is_string($this->view->view)) {
            $this->view->selectedView = $this->view->view;
            $this->view->view = $this->view->views[$this->view->selectedView];
        } else {
            $this->view->selectedView = 'current';
            $this->view->views[$this->view->selectedView] = array('name' => 'Текущий');
        }
        $this->view->searchText = $this->_getParam('search_text');
        if ($this->view->searchText) {
            $this->getFrontController()->getRouter()->setGlobalParam('search_text', $this->_getParam('search_text'));
        }
        
        $this->view->view = array_replace_recursive($this->view->views['default'], $this->view->view);
        $size = $this->view->view['rop'];
        $sortField = $this->view->view['sort']['field'];
        $sortDir = $this->view->view['sort']['dir'];
        $conditions = $this->view->view['conditions']; 
        if ($this->view->searchText) {
            $conditions[] = array(
                'field' => 'names',
                'operator' => "contain",
                'argument' => $this->view->searchText
            );
        }
        
        
        $this->view->movies = Lms_Item_Movie::select(
            $total, 
            $page, $size, $sortField, $sortDir, $conditions
        ); 

        $this->view->paginator = new Lms_Paginator($total);
        $this->view->paginator->setItemCountPerPage($size);
        $this->view->paginator->setCurrentPageNumber($page);
        
        if ($this->view->isFrame) {
            return $this->render("index-frame");
        }
    }

    public function addAction()
    {
        $this->view->title .= " — Добавить";
        $this->view->formErrors = array();
        if ($this->getRequest()->isPost()) {
            $formData = $_POST;
            if ($formData['kinopoisk_id'] && ($info = Lms_Service_Kinopoisk::parseMovie($formData['kinopoisk_id']))) {
                $db = Lms_Db::get('main');
                $db->transaction();
                $movie = Lms_Item_Movie::fromInfo($info);
                $movie->setExtendedInfo($formData['description'])
                      ->save();
                $this->view->redirectUrl = $this->view->url(
                    array('action' => 'index'),
                    'movies'
                );
                $db->commit();
                return $this->render('complete');
            } else {
                $this->view->formErrors[] = 'Выберите фильм';
            }
        }
    }
    
    public function personAction()
    {
        $personId = $this->_getParam('id');
        $this->view->person = Lms_Item::create('Person', $personId);
        $this->view->title = $this->view->person->getName();
    }

    public function movieAction()
    {
        $movieId = $this->_getParam('id');
        Lms_Item_Preloader::load('Movie', array('Country', 'Genre', 'Role', 'Person (name, photo)', 'Rating'), 'movie_id', array($movieId)); 
        $this->view->movie = Lms_Item::create('Movie', $movieId);
        $this->view->title = $this->view->movie->getName();
        if ($this->view->isFrame) {
            return $this->render("movie-frame");
        }
    }

}