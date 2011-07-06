<?php
/**
 * Инициализация приложения
 * 
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: Application.php 700 2011-06-10 08:40:53Z macondos $
 */

class Lms_Application
{
    private static $_config;

    /**
     * @var Lms_Api_Controller
     */
    private static $_apiController = null;
    /**
     * @var Zend_Controller_Front
     */
    private static $_frontController = null;
    /**
     * @var Zend_Translate
     */
    private static $_translate;
    /**
     * @var Zend_Acl
     */
    private static $_acl;
    /**
     * @var Lms_User
     */
    private static $_user;
    /**
     * @var Lms_MultiAuth
     */
    private static $_auth;
    /**
     * @var Zend_Controller_Request_Http
     */
    private static $_request;

    /**
     * Текущий язык
     * @var string
     */
    private static $_lang;
    /**
     * Текущий макет
     * @var string
     */
    private static $_layout;
    /**
     * Базовый URL без учета модификатора языка
     * http://examle.com/root/Url/ru/blah/blah ($_rootUrl = /root/Url)
     * @var string
     */
    private static $_rootUrl;

    /**
     * Массив директорий скриптов шаблона (.phtml)
     * @var array
     */
    private static $_scriptsTemplates;

    /**
     * Массив реальных путей и соответствующих относительных URL
     * публичных файлов шаблона (.css, .js и т.д.)
     * Пример:
     * Array(
     *      [0] => Array
     *          (
     *              [path] => C:/www/english/public/templates/user/ru
     *              [url] => /public/templates/user/ru
     *          )
     *
     *      [1] => Array
     *          (
     *              [path] => C:/www/english/public/templates/user
     *              [url] => /public/templates/user
     *          )
     *
     *      [2] => Array
     *          (
     *              [path] => C:/www/english/public/templates/default.dist/ru
     *              [url] => /public/templates/default.dist/ru
     *          )
     *
     *      [3] => Array
     *          (
     *              [path] => C:/www/english/public/templates/default.dist
     *              [url] => /public/templates/default.dist
     *          )
     *
     *  )
     *
     * @var array
     */
    private static $_publicTemplates;

    /**
     * Время начало работы скрипта
     * @var float
     */
    private static $_mainTimer;
    
    private static $_httpClient;
    
    private static function detectLang(Zend_Controller_Request_Http &$request)
    {
        self::$_lang = self::$_config['langs']['default'];
        if (isset($_COOKIE['lang']) && array_key_exists($_COOKIE['lang'], self::$_config['langs']['supported'])) {
            self::$_lang = $_COOKIE['lang'];
        } else {
            $langs = Zend_Locale::getBrowser();
            asort($langs);
            foreach (array_keys($langs) as $lang) {
                if (array_key_exists($lang, self::$_config['langs']['supported'])) {
                    self::$_lang = $lang;
                }
            }
        }
        $locale = new Zend_Locale(self::$_lang);
        Zend_Registry::set('Zend_Locale', $locale);
        return self::$_lang;
    }
    
    public static function runApi()
    {
        self::$_request = new Zend_Controller_Request_Http();
        self::prepareApi();
        self::$_apiController->exec();
        self::close();
    }

    public static function prepareApi()
    {
        /**
         * Разъяснение комментариев:
         * self::initYYY()//зависит от XXX
         * Это значит перед запуском, метода YYY, должен отработать метод XXX
         * self::initYYY()//требует XXX
         * Это значит, что для корректной работы сущностей определяемых
         * методом YYY, должен быть проинизиализирован метод XXX (место
         * инициализации не имеет важного значения)
         */

        self::initEnvironmentApi();
        self::initConfig();//зависит от initEnvironment
        self::initDebug();//зависит от initConfig
        self::initErrorHandler();//зависит от initDebug
        self::initDb(); //зависит от initConfig, требует initDebug
        self::initVariables();//зависит от initDb
        self::initConfigFromDb();//зависит от initDb
        self::initApiController();//зависит от initVariables
        self::initTranslate();//зависит от initApiRequest, initDebug
        self::initAcl();//зависит от initConfig, initVariables, initDb
    }

    public static function initApiController()
    {
        self::$_apiController = Lms_Api_Controller::getInstance();
        self::$_apiController->analyzeHttpRequest();
        self::$_lang = self::$_apiController->getLang();
        if (!self::$_lang) {
            self::$_lang = self::$_config['langs']['default'];
        }

    }


    public static function run()
    {
        self::$_request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();
        $channel = Zend_Wildfire_Channel_HttpHeaders::getInstance();
        $channel->setRequest(self::$_request);
        $channel->setResponse($response);
        // Start output buffering
        ob_start();
        try { 
            self::prepare();
            Lms_Debug::debug('Request URI: ' . $_SERVER['REQUEST_URI']);
            try {
                self::$_frontController->dispatch(self::$_request);
            } catch (Exception $e) {
                Lms_Debug::crit($e->getMessage());
                Lms_Debug::crit($e->getTraceAsString());
            }
            self::close();
        } catch (Exception $e) {
            Lms_Debug::crit($e->getMessage());
            Lms_Debug::crit($e->getTraceAsString());
        }
        // Flush log data to browser
        $channel->flush();
        $response->sendHeaders();
    }

    public static function prepare()
    {
        /**
         * Разъяснение комментариев:
         * self::initYYY()//зависит от XXX
         * Это значит перед запуском, метода YYY, должен отработать метод XXX
         * self::initYYY()//требует XXX
         * Это значит, что для корректной работы сущностей определяемых
         * методом YYY, должен быть проинизиализирован метод XXX (место
         * инициализации не имеет важного значения)
         */

        self::initEnvironment();
        self::initConfig();//зависит от initEnvironment
        self::initSessions();//зависит от initConfig
        self::initDebug();//зависит от initConfig
        self::initErrorHandler();//зависит от initDebug
        self::initDb(); //зависит от initConfig, требует initDebug
        self::initVariables();//зависит от initDb
        self::initConfigFromDb();//зависит от initDb
        self::initFrontController();//зависит от initConfig
        self::initTranslate();//зависит от initFrontController, initDebug
        self::initRoutes();//зависит от initFrontController
        self::initAcl();//зависит от initConfig, initVariables, initDb
        self::initView();//зависит от initConfig, initFrontController,
                         //initAcl, initTranslate
    }
    
    public static function initEnvironmentApi()
    {
        ini_set('max_execution_time', 1000);
    }

    public static function initEnvironment()
    {
        ini_set('max_execution_time', 1000);
        header("Content-type:text/html;charset=utf-8");
        if(get_magic_quotes_runtime())
        {
            set_magic_quotes_runtime(false);
        }
        static $alreadyStriped = false;
        if (get_magic_quotes_gpc() || !$alreadyStriped) {
            $_COOKIE = Lms_Array::recursiveStripSlashes($_COOKIE);
            //$_FILES = Lms_Array::recursiveStripSlashes($_FILES);
            $_GET = Lms_Array::recursiveStripSlashes($_GET);
            $_POST = Lms_Array::recursiveStripSlashes($_POST);
            $_REQUEST = Lms_Array::recursiveStripSlashes($_REQUEST);
            $alreadyStriped = true;
        } 
    }
    
    public static function initConfig()
    {

        include_once APP_ROOT . "/default.settings.php";
        include_once APP_ROOT . "/local.settings.php";
        self::$_config = $config;
    }

    public static function initConfigFromDb()
    {
        if ($params = self::getConfig('db_config')) {
            $db = Lms_Db::get($params['alias']);
            $rows = $db->select('SELECT * FROM ?#', $params['table']);
            foreach ($rows as $row) {
                switch ($row['type']) {
                    case 'array': 
                        $value = unserialize($row['value']);
                        break;
                    case 'scalar': 
                    default: 
                        $value = $row['value'];
                        break;
                }
                $keys = preg_split('{/}', $row['key']);
                switch (count($keys)) {
                    case 1:
                        self::$_config[$keys[0]] = $value;
                        break;
                    case 2: 
                        self::$_config[$keys[0]][$keys[1]] = $value;
                        break;
                    case 3: 
                        self::$_config[$keys[0]][$keys[1]][$keys[2]] = $value;
                        break;
                    case 4: 
                        self::$_config[$keys[0]][$keys[1]][$keys[2]][$keys[3]] = $value;
                        break;
                    case 5: 
                        self::$_config[$keys[0]][$keys[1]][$keys[2]][$keys[3]][$keys[4]] = $value;
                        break;
                    default: 
                        throw new Lms_Exception("DB-config keys not support deep more 5 subitems");
                        break;
                }
            }
        }
    }
    
    public static function initSessions()
    {
        Zend_Session::start();
    }
    
    public static function initDebug()
    {
        Lms_Debug::setLogger(self::$_config['logger']);
        self::$_mainTimer = new Lms_Timer();
        self::$_mainTimer->start();
    }
    
    public static function initErrorHandler()
    {
        Lms_Debug::initErrorHandler();
    }

    public static function initDb()
    {
        foreach (self::$_config['databases'] as $dbAlias => $dbConfig) {
            Lms_Db::addDb(
                $dbAlias,
                $dbConfig['connectUri'],
                $dbConfig['initSql'],
                $dbConfig['debug']
            );
        }
    }

    public static function initVariables()
    {
        if (self::$_request) {
            self::$_rootUrl = self::$_request->getBaseUrl();
        }
        if (preg_match('{\.php$}i', self::$_rootUrl)) {
            self::$_rootUrl = dirname(self::$_rootUrl);
        }

        Lms_Item::setDb(Lms_Db::get("main"), Lms_Db::get("main"));
        Lms_Item_Preloader::setDb(Lms_Db::get("main"));

        Lms_Item_Struct_Generator::setStoragePath(
            APP_ROOT . '/includes/Lms/Item/Struct'
        );

        Lms_Text::setEncoding('UTF-8');
        Lms_Text::enableMultiByte();
        
        Lms_Thumbnail::setHttpClient(self::getHttpClient());
        Lms_Thumbnail::setImageDir(
            rtrim($_SERVER['DOCUMENT_ROOT'] . self::$_rootUrl, '/\\') . '/media'
        );
        Lms_Thumbnail::setCacheDir(
            rtrim($_SERVER['DOCUMENT_ROOT'] . self::$_rootUrl, '/\\') . '/media/cache'
        );
        
        Lms_View_Helper_OptimizedHeadScript::setCacheDir(
            rtrim($_SERVER['DOCUMENT_ROOT'] . self::$_rootUrl, '/\\') . '/media/cache/js'
        );
        Lms_View_Helper_OptimizedHeadLink::setCacheDir(
            rtrim($_SERVER['DOCUMENT_ROOT'] . self::$_rootUrl, '/\\') . '/media/cache/css'
        );
        Lms_View_Helper_OptimizedHeadLess::setCacheDir(
            rtrim($_SERVER['DOCUMENT_ROOT'] . self::$_rootUrl, '/\\') . '/media/cache/css'
        );
    }

    public static function initAcl()
    {
        self::$_auth = Lms_MultiAuth::getInstance();

        $cookieManager = new Lms_CookieManager(
            self::$_config['auth']['cookie']['key']
        );
        $authStorage = new Lms_Auth_Storage_Cookie(
            $cookieManager,
            self::$_config['auth']['cookie']
        );
        self::$_auth->setStorage($authStorage);

        self::$_acl = new Zend_Acl();
        self::$_acl->addRole(new Zend_Acl_Role('guest'))
                   ->addRole(new Zend_Acl_Role('user'), 'guest')
                   ->addRole(new Zend_Acl_Role('moder'), 'user')
                   ->addRole(new Zend_Acl_Role('admin'));

        self::$_acl->add(new Zend_Acl_Resource('movie'))
                   ->add(new Zend_Acl_Resource('comment'))
                   ->add(new Zend_Acl_Resource('user'));
                   

        self::$_acl->allow('admin')
                   ->allow('moder', array('movie', 'comment'));
                   
        Lms_User::setDefaultGroup('user');
        Lms_User::setGuestGroup('guest');
        Lms_User::setAcl(self::$_acl);
        self::$_user = Lms_User::getUser();
        if ($timezone = self::$_user->getTimezone()) {
            date_default_timezone_set($timezone);
        } elseif (isset($_COOKIE['std_time_offset'])) {
            $timezones = array(
                '-12'=>'Pacific/Kwajalein',
                '-11'=>'Pacific/Samoa',
                '-10'=>'Pacific/Honolulu',
                '-9'=>'America/Juneau',
                '-8'=>'America/Los_Angeles',
                '-7'=>'America/Denver',
                '-6'=>'America/Mexico_City',
                '-5'=>'America/New_York',
                '-4'=>'America/Caracas',
                '-3.5'=>'America/St_Johns',
                '-3'=>'America/Argentina/Buenos_Aires',
                '-2'=>'Atlantic/Azores',// no cities here so just picking an hour ahead
                '-1'=>'Atlantic/Azores',
                '0'=>'Europe/London',
                '1'=>'Europe/Paris',
                '2'=>'Europe/Helsinki',
                '3'=>'Europe/Moscow',
                '3.5'=>'Asia/Tehran',
                '4'=>'Asia/Baku',
                '4.5'=>'Asia/Kabul',
                '5'=>'Asia/Karachi',
                '5.5'=>'Asia/Calcutta',
                '6'=>'Asia/Colombo',
                '7'=>'Asia/Bangkok',
                '8'=>'Asia/Singapore',
                '9'=>'Asia/Tokyo',
                '9.5'=>'Australia/Darwin',
                '10'=>'Pacific/Guam',
                '11'=>'Asia/Magadan',
                '12'=>'Asia/Kamchatka'
            ); 
            if (isset($timezones[$_COOKIE['std_time_offset']])) {
                date_default_timezone_set($timezones[$_COOKIE['std_time_offset']]);
            }
        }
    }

    public static function initFrontController()
    {
        self::$_frontController = Zend_Controller_Front::getInstance();
        $controllerDirectory = APP_ROOT . "/templates/"
                             . self::$_config['template'] . '/controllers';
        self::$_frontController->throwExceptions(true)
                               ->setControllerDirectory($controllerDirectory)
                               ->setDefaultControllerName('index')
                               ->setDefaultAction('index')
                               ->setParams(array());
   
        self::detectLang(self::$_request);
        self::$_frontController->setRequest(self::$_request);
        return self::$_frontController;
    }

    
    
    public static function initTranslate()
    {
        $translateFile = APP_ROOT . '/lang/' . self::$_lang . '/main.csv';
        self::$_translate = new Zend_Translate('csv',
                                               $translateFile,
                                               self::$_lang,
                                               array('delimiter'=>'='));
        $userTranslateFile = APP_ROOT . '/lang/'
                           . self::$_lang . '/user/main.csv';
        if (file_exists($userTranslateFile)) {
            self::$_translate->addTranslation($userTranslateFile);
        }
        self::$_translate->setOptions(
            array('log' => Lms_Debug::getLogger(),
            'logUntranslated' => true)
        );

    }
    
    public static function initRoutes()
    {
        self::$_frontController->setDefaultControllerName('movies')
                               ->setDefaultAction('index');

        $router = self::$_frontController->getRouter();

        $router->addRoute(
            'info',
            new Zend_Controller_Router_Route('info/:action/',
                array(
                    'controller' => 'info',
                    'action' => 'faq'
                )
            )
        );

        $router->addRoute(
            'profile',
            new Zend_Controller_Router_Route('profile/:action/*',
                array(
                    'controller' => 'profile',
                    'action' => 'settings'
                )
            )
        );

        $router->addRoute(
            'user',
            new Zend_Controller_Router_Route('user/:action/*',
                array(
                    'controller' => 'user',
                )
            )
        );

        $router->addRoute(
            'movies',
            new Zend_Controller_Router_Route('movies/:action/*',
                array(
                    'controller' => 'movies',
                    'action' => 'index'
                )
            )
        );

        $router->addRoute(
            'cp',
            new Zend_Controller_Router_Route(
                'cp/*',
                array('controller' => 'cp',
                      'action' => 'cp'))
        );
    }

    public static function initView()
    {
        //LIFO order
        self::$_scriptsTemplates = array(
            APP_ROOT . '/templates/' . self::$_config['template'],
            APP_ROOT . '/templates/user',
        );

        //FIFO order
        $relativeTemplateUrls = array(
            '/user/' . self::$_lang,
            '/user',
            '/' . self::$_config['template'] . '/' . self::$_lang,
            '/' . self::$_config['template']
        );

        self::$_publicTemplates = array();
        foreach ($relativeTemplateUrls as $relativeTemplateUrl) {
            $path = $_SERVER['DOCUMENT_ROOT']
                  . self::$_rootUrl . '/templates' . $relativeTemplateUrl;
            if (is_dir($path)) {
                self::$_publicTemplates[] = array(
                    'path' => $path,
                    'url' => self::$_rootUrl . '/templates' . $relativeTemplateUrl
                );
            }
        }
        //Lms_Debug::debug(print_r(self::$_publicTemplates,1));

        $view = new Zend_View(array('encoding' => 'UTF-8'));

        $view->addHelperPath('Lms/View/Helper', 'Lms_View_Helper');

        foreach (self::$_scriptsTemplates as $scriptTemplate) {
            $view->addScriptPath($scriptTemplate . '/views/scripts')
                 ->addScriptPath($scriptTemplate . '/layouts');
        }
        //Lms_Debug::debug(print_r($view->getScriptPaths(),1));

        $view->supportedLangs = self::$_config['langs']['supported'];
        $view->t = self::$_translate;
        $view->lang = self::$_lang;
        $view->hostUrl = 'http://' . $_SERVER['HTTP_HOST'];
        $view->rootUrl = self::$_rootUrl;
        $view->baseUrl = self::$_request->getBaseUrl();
        $view->user = self::$_user;
        $view->auth = self::$_auth;
        $view->publicTemplates = self::$_publicTemplates;

        $config = self::getConfig();
        if (isset($config['lms_jsf_path'])) {
            $view->lmsJsfPath = $config['lms_jsf_path'];
        } else {
            if (is_dir($_SERVER['DOCUMENT_ROOT'] . '/lms-jsf')) {
                $view->lmsJsfPath = '/lms-jsf';
            } else {
                $view->lmsJsfPath = self::$_rootUrl . '/lms-jsf';
            }
        }
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
            'viewRenderer'
        );
        $viewRenderer->setView($view);

        Zend_Layout::startMvc();

        $view->tabs = array();
     
        return $view;
    }

    public static function close()
    {
        if (self::getConfig('optimize', 'classes_combine')) {
            Lms_NameScheme_Autoload::compileTo(APP_ROOT . '/includes/All.php');
        }
        
        foreach (self::$_config['databases'] as $dbAlias => $dbConfig) {
            if (Lms_Db::isInstanciated($dbAlias)) {
                $db = Lms_Db::get($dbAlias);
                $sqlStatistics = $db->getStatistics();
                $time = $sqlStatistics['time'];
                $count = $sqlStatistics['count'];
                Lms_Debug::debug(
                    "Database $dbAlias time: $time ($count queries)"
                );
            }
        }
        Lms_Debug::debug(
            'Used memory: ' . round(memory_get_usage()/1024) . ' KB'
        );
        self::$_mainTimer->stop();
        Lms_Debug::debug('Execution time: ' . self::$_mainTimer->getSumTime());
    }
    
    public static function getLang()
    {
        return self::$_lang;
    }

    public static function getTranslate()
    {
        return self::$_translate;
    }

    public static function getRequest()
    {
        return self::$_request;
    }

    public static function getConfig($param = null)
    {
        $params = func_get_args();
        $result = self::$_config;
        foreach($params as $param) {
            if (!array_key_exists($param, $result)) {
                return null;
            }
            $result = $result[$param];
        }
        return $result;
    }

    public static function getHttpClient()
    {
        if (!self::$_httpClient) {
            $httpOptions = Lms_Application::getConfig('http_client');
            self::$_httpClient = new Zend_Http_Client(
                null,
                $httpOptions
            );
        }
        return self::$_httpClient;
    }    
}