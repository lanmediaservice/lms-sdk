<?php 
/**
 * Настройки конфигурации по-умолчанию
 * 
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: default.settings.php 700 2011-06-10 08:40:53Z macondos $
 */

/**
 * Режим вывода ошибок
 */
error_reporting(E_ALL);

/**
 * Установка временной зоны
 */
date_default_timezone_set('UTC');

ini_set("iconv.internal_encoding", 'UTF-8');
 
/**
 * Инициализация отладки
 */
$config['logger'] = new Zend_Log();
$config['logger']->setEventItem('pid', getmypid());
$config['logger']->setEventItem('ip', Lms_Ip::getIp());

/**
 * Конфигурация баз данных
 */

$config['databases']['main'] = array(
    'connectUri' => 'mysql://root@localhost/lms_sdk_movies?ident_prefix=',
    'initSql' => 'SET NAMES utf8',
    'debug' => 1
);


/**
 * Настройка шаблона
 */
$config['template'] = 'default';

/**
 * Настройка языков
 */
$config['langs']['supported'] = array('ru'=>'Русский', 
                                      'en'=>'English (US)');
$config['langs']['default'] = 'en';


/**
 * Временная директория для общих нужд
 */
$config['tmp'] = (isset($_ENV['TEMP']))? $_ENV['TEMP'] : '/tmp';

/**
 * Настройки аутентификации
 */
$config['auth']['cookie'] = array('key' => 'SECRET_KEY1234',
                                  'cookieName' => 'lms_auth',
                                  'cookiePath' => '/',
                                  'cookieExpire' => (time() + 3600*24*60));

/**
 * Настройка файловой системы
 */
$config['ufs']['system_encoding'] = 'UTF-8';

$config['optimize']['classes_combine'] = 0;
$config['optimize']['js_combine'] = 0;
$config['optimize']['js_compress'] = 0;
$config['optimize']['css_combine'] = 0;
$config['optimize']['css_compress'] = 0;
$config['optimize']['less_combine'] = 1;


$config['symlinks'] = array('//' => dirname(dirname(__FILE__)) . '/public/');

$config['views'] = array();
$config['views']['movies'] = array();

$config['views']['movies']['default'] = array(
    'name' => 'Все',
    'rop' => '12',
    'sort' => array('field' => 'added_at',
                    'dir' => 'desc'),
    'conditions' => array()
);

$config['views_groups']['movies'] = array(
    'guest' => array('default'),
    'user' => array('default',),
    'moder' => array('default',),
    'admin' => array('default',),
);

$config['paths_map'] = array();

$config['parser_service']['username'] = 'demo';
$config['parser_service']['password'] = 'demo';
$config['parser_service']['url'] = 'http://service.lanmediaservice.com/2/actions.php';