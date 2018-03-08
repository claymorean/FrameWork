<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/26
 * Time: 16:23
 */

//记录开始运行时间
define('PUBLIC_PATH', __DIR__);
define('APP_PATH', __DIR__."/app");

require 'functions.php';

require __DIR__.'/../vendor/autoload.php';

require __DIR__.'/../boot/app.php';