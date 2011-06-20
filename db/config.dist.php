<?php
/**
 * Синхронизатор версий базы данных
 * 
 * @see readme.txt  
 * 
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: config.dist.php 700 2011-06-10 08:40:53Z macondos $
 */

//Перед поиском новых патчей проводить обновление
$config['svn']['autoupdate'] = false;

$config['mysql']['db'] = 'lms_sdk';
$config['mysql']['host'] = 'localhost';
$config['mysql']['username'] = 'root';
$config['mysql']['password'] = '';
$config['mysql']['prefix'] = '';