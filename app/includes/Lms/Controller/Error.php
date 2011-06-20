<?php 
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Error.php 700 2011-06-10 08:40:53Z macondos $
 */
 
class Lms_Controller_Error extends Zend_Controller_Action
{
    public function errorAction()
    {
        $this->_redirect('/', array('exit'=>true));
        return;
        $errors = $this->_getParam('error_handler');

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // ошибка 404 - не найден контроллер или действие
                $this->getResponse()
                     ->setRawHeader('HTTP/1.1 404 Not Found');

                // ... получение данных для отображения...
                $this->view->title = "404 Not Found";
                break;
            default:
                // ошибка приложения; выводим страницу ошибки,
                // но не меняем код статуса
                break;
        }
    }
}