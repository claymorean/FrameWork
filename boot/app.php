<?php
/**
 * @Author: Marte
 * @Date:   2018-03-08 11:18:16
 * @Last Modified by:   Marte
 * @Last Modified time: 2018-03-08 11:33:29
 */

use Illuminate\Database\Capsule\Manager as Capsule;
use Whoops\Run as Whoops;
// Eloquent ORM

$capsule = new Capsule;
$capsule->addConnection(require '../config/database.php');
$capsule->bootEloquent();

//报错
$whoops = new Whoops;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

require __DIR__.'/../config/routes.php';

//$app = new App\Core\App();
//$app->run();
