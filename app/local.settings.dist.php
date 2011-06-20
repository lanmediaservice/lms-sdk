<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: local.settings.dist.php 700 2011-06-10 08:40:53Z macondos $
 */

$logDir = dirname(__FILE__) . '/logs/';
if (defined('LOGS_SUBDIR')) {
    $logDir .= rtrim(LOGS_SUBDIR, '/') . '/';
}
$writer = new Zend_Log_Writer_Stream(
    $logDir . 'error.' . date('Y-m-d') . '.log'
);
$config['logger']->addWriter($writer);
$format = '%timestamp% %ip%(%pid%) %priorityName% (%priority%): %message%'
        . PHP_EOL;
$formatter = new Zend_Log_Formatter_Simple($format);
$writer->setFormatter($formatter);
$filter = new Zend_Log_Filter_Priority(Zend_Log::WARN);
$writer->addFilter($filter);

$writer = new Zend_Log_Writer_Stream(
    $logDir . 'debug.' . date('Y-m-d') . '.log'
);
$config['logger']->addWriter($writer);
$format = '%timestamp% %ip%(%pid%) %priorityName% (%priority%): %message%'
        . PHP_EOL;
$formatter = new Zend_Log_Formatter_Simple($format);
$writer->setFormatter($formatter);

$writer = new Zend_Log_Writer_Firebug();
$config['logger']->addWriter($writer);

if (php_sapi_name() != 'cli' && !(isset($_GET['format']) 
    && in_array($_GET['format'], array('ajax', 'php')))
    && (!defined('SKIP_DEBUG_CONSOLE') || !SKIP_DEBUG_CONSOLE)
) {
    $writer = new Lms_Log_Writer_Console();
    $format = '%timestamp%: %message%';
    $formatter = new Zend_Log_Formatter_Simple($format);
    $writer->setFormatter($formatter);
    $config['logger']->addWriter($writer);
}

$config['databases']['main'] = array(
    'connectUri' => 'mysql://root@localhost/lms_sdk_movies?ident_prefix=',
    'initSql' => 'SET NAMES utf8',
    'debug' => 0
);

$config['parser_service']['username'] = 'demo';
$config['parser_service']['password'] = 'demo';

$config['symlinks'] = array('//lms-sdk' => dirname(dirname(__FILE__)) . '/public');
$config['paths_map'] = array(
    realpath($_SERVER['DOCUMENT_ROOT'] . '/lms-sdk') => $_SERVER['DOCUMENT_ROOT'] . '/lms-sdk'
);
 