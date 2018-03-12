<?php
/**
 * @Author: Marte
 * @Date  :   2018-03-08 11:24:51
 * @Last  Modified by:   Marte
 * @Last  Modified time: 2018-03-08 11:32:33
 */

//use NoahBuscher\Macaw\Macaw;
use Core\Route;

Route::get('', 'HomeController@home');

Route::get('fuck', function () {
    echo "成功！";
});

Route::get('(:all)', function ($fu) {
    echo '未匹配到路由<br>'.$fu;
});

Route::$error_callback = function () {
    throw new Exception("路由无匹配项 404 Not Found");
};
Route::dispatch();