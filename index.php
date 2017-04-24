<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/26
 * Time: 16:23
 */

//记录开始运行时间
define('LARAVEL_START', microtime(true));
define('SYS_PATH', __DIR__);
define('APP_PATH', __DIR__."/app");

require 'functions.php';
require 'vendor/autoload.php';

$app = new App\Core\App();
$app->run();
