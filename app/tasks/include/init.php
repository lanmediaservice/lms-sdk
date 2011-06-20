<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: init.php 700 2011-06-10 08:40:53Z macondos $
 */

@ini_set('max_execution_time', 0);
@set_time_limit();

if (!defined('LOGS_SUBDIR')) {
    define('LOGS_SUBDIR', 'tasks');
}

require_once dirname(dirname(dirname(__FILE__))) . "/config.php";

Lms_Application::prepareApi();