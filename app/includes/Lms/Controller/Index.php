<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Index.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_Controller_Index extends Zend_Controller_Action
{
    function init()
    {
        $this->_helper->layout->setLayout('simple');
        $this->getResponse()->setHeader('Expires', '-1', true);
        $this->getResponse()->setHeader('Cache-Control', 'no-cache', true);
        $this->getResponse()->setHeader('Pragma', 'no-cache', true);
    }
    
    public function indexAction()
    {
        $this->view->title = "Фильмы";
    }

}