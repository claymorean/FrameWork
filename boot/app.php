<?php
/**
 * @Author: Marte
 * @Date:   2018-03-08 11:18:16
 * @Last Modified by:   Marte
 * @Last Modified time: 2018-03-08 11:33:29
 */

use Illuminate\Database\Capsule\Manager as Capsule;
use Whoops\Run as Whoops;
use Dotenv\Dotenv;
// Eloquent ORM

$capsule = new Capsule;
$capsule->addConnection(require '../config/database.php');
$capsule->bootEloquent();

//报错
$whoops = new Whoops;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

$env=new Dotenv(BASE_PATH, '.env');
$env->load();

require BASE_PATH.'/config/test.php';
require APP_PATH.'/Libs/helpers.php';
//$app = new App\Core\App();
//$app->run();
