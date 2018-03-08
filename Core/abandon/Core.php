<?php
if (version_compare(PHP_VERSION, '5.0.0', '<')) {
    die('irequire PHP > 5.0 !');
}

//框架版本
define('PHPFW_VERSION', '1.0');

//记录开始运行时间
$GLOBALS['_beginTime'] = microtime(true);

//检测该定制的常量
if (!defined('APP_PATH')) {
    die('You must defined APP_PATH first!');
}
if (!defined('CORE_PATH')) {
    die('You must defined CORE_PATH first!');
}
//if (!defined('APP_NAME'))	{define('APP_NAME',md5($_SERVER['SCRIPT_NAME']));}
// PHPFW系统目录定义
define('SYS_PATH', dirname(__FILE__));

require(SYS_PATH . '/app.php');

$GLOBALS['classFiles'] = array(
    'Controller' => SYS_PATH . '/Controller.php',
    'Model' => SYS_PATH . '/Model/model.php',
//    'View' => SYS_PATH . '/view.class.php',
//    'Template' => SYS_PATH . '/view/template.class.php',
    'Query' => SYS_PATH . '/model/Query.php',
//    'HttpClient' => SYS_PATH . '/libs/httpclient.class.php',
//    'Cache' => SYS_PATH . '/cache/cache.class.php',

);

spl_autoload_register(function ($className) {
    if (isset($GLOBALS['classFiles'][$className])) {
        require($GLOBALS['classFiles'][$className]);
    } elseif (is_file($file = APP_PATH . '/model/' . $className . '.php')) {
        require($file);
    } elseif (is_file($file = APP_PATH . '/controller/' . $className . '.php')) {
        require($file);
    }
});
//function __autoload($className)
//{
//    if (isset($GLOBALS['classFiles'][$className])) {
//        require($GLOBALS['classFiles'][$className]);
//    } elseif (is_file($file = APP_PATH . '/model/' . $className . '.php')) {
//        require($file);
//    } elseif (is_file($file = APP_PATH . '/controller/' . $className . '.php')) {
//        require($file);
//    }
//}

