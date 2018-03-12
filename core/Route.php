<?php
/**
 * Created by PhpStorm.
 * User: senuer
 * Date: 2018/3/12
 * Time: 10:23
 */

namespace Core;


class Route {
    public static $methods;
    public static $routes;
    public static $callbacks;
    public static $errors;

    public static function error($callback) {
        self::$errors = $callback;
    }

    public static function __callstatic($method,$arguments) {
        $method=strtoupper($method);
        if(in_array($method,['GET','POST','PUT','DELETE','ANY'])){
            $route=strpos($arguments[0], '/') === 0 ? $arguments[0] : '/' . $arguments[0];
            self::$methods[]=$method;
            self::$routes[]=$route;
            self::$callbacks[]=$arguments[1];
        }else{
            throw new \Exception('Error method');
        }
    }

    public static function dispatch() {
        $request=parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
//        dd($request);
        if(in_array($request,self::$routes)){
            //正常路由
            $matchRouteKeys=array_keys(self::$routes,$request);
            foreach ($matchRouteKeys as $matchRouteKey){
                if(self::$methods[$matchRouteKey] == $method || self::$methods[$matchRouteKey] == 'ANY'){
                    $callback=self::$callbacks[$matchRouteKey];
                    if (!is_object($callback)){
                        $params=explode('@',$callback);
                        $controller=$params[0];
                        $controller= new $controller;
                        $function=$params[1];
                        $controller->{$function}();
                    }else{
                        call_user_func($callback);
                    }
                }
            }
        }else{
            //正则定义的路由
            throw new \Exception('Route Or Method Error');
        }
    }
}