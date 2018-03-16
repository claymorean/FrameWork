<?php
/**
 * Created by PhpStorm.
 * User: senuer
 * Date: 2018/3/12
 * Time: 10:23
 */

namespace Core;

/**
 * Class Route
 *
 * @package Core
 * @routeMethods $methods
 * @routes $routes
 * @callbacks 路有对应回调函数
 * @errors
 * @regex 路由的正则表达式
 * @replace 参数和正则的对应关系
 * @attributes 正则对应的变量名
 * @routeNames 路由名
 */
class Route {
    public static $methods;
    public static $routes;
    public static $callbacks;
    public static $errors;
    public static $regex;
    public static $replace;
    public static $attributes;
    public static $routeNames;

    public static function error($callback) {
        self::$errors = $callback;
    }

    public static function __callstatic($method, $arguments) {
        $method = strtoupper($method);
        if (in_array($method, ['GET', 'POST', 'PUT', 'DELETE', 'ANY'])) {
            $route = strpos($arguments[ 0 ], '/') === 0 ? $arguments[ 0 ] : '/'.$arguments[ 0 ];
            self::$methods[] = $method;
            self::$routes[] = $route;
            self::$callbacks[] = $arguments[ 1 ];
            preg_match_all('/{[^\/]*}/', $route, $matches);
//            preg_match_all('/{[^\/]*}/', '/{id}/{name}', $matches);
//            dd($matches[0]);
//            if (strpos($route, '{') !== false) {
            if (count($matches[0])>0) {
                self::$regex[] = self::replace($route,$matches[0]);
//                self::$regex[] = '/^'.substr($route, 0, $start - 1).'(?:\/(?P<'.$attributes.'>'.$reg.'))?$/';
//                '^/facility/log(?:/(?P<id>[0-9]+))?$';
            } else {
                self::$regex[] = '';
            }
        } else {
            throw new \Exception('Error method');
        }
    }

    public static function dispatch() {
        $request = parse_url($_SERVER[ 'REQUEST_URI' ], PHP_URL_PATH);
        $method = $_SERVER[ 'REQUEST_METHOD' ];
//        dd($request);
        if (in_array($request, self::$routes)) {
            //正常路由
            $matchRouteKeys = array_keys(self::$routes, $request);
        } else {
            //正则定义的路由
//            dd(self::$regex);
            foreach (self::$regex as $regex) {
                if (strpos($regex, '(') !== false) {
                    $result = preg_match_all($regex, $request, $matches);
                    if ($result) {
                        $matchRouteKeys = array_keys(self::$regex, $regex);
                    }
                }
            }
//            $regRule = '^/facility/log(?:/(?P<id>[0-9]+))?$';
        }
        if(empty($matchRouteKeys)){
            throw new \Exception('Route Or Method Error');
        }else{
            foreach ($matchRouteKeys as $matchRouteKey) {
                if (self::$methods[ $matchRouteKey ] == $method || self::$methods[ $matchRouteKey ] == 'ANY') {
                    $callback = self::$callbacks[ $matchRouteKey ];
                    if (!is_object($callback)) {
                        $action = is_array($callback) ? $callback : ['use' => $callback];
                        self::$routeNames[ isset($action[ 'as' ]) ? $action[ 'as' ] : $request ] = $request;
                        $params = explode('@', $action[ 'use' ]);
                        $controller = $params[ 0 ];
                        $controller = new $controller;
//                        $function = $params[ 1 ];
//                        $controller->{$function}();
                        call_user_func([$controller,$params[1]]);
                    } else {
                        call_user_func($callback);
                    }
                }
            }
        }
    }

    //用正则替换路由中的{} 以及带{}时/变更为\/
    private static function replace($route,$matches){
        $str='';
        $routeArray=explode('/',$route);
        foreach ($routeArray as $value){
            if(in_array($value,$matches)){
                if(strpos($value,'?')){
                    $attributes = substr($value, 1, strlen($value)- 3);
                    $reg = array_key_exists($attributes, self::$replace) ? self::$replace[ $attributes ] : '.*';
                    $str.='(?:\/(?P<'.$attributes.'>'.$reg.'))?';
                }else{
                    $attributes = substr($value, 1, strlen($value)- 2);
                    $reg = array_key_exists($attributes, self::$replace) ? self::$replace[ $attributes ] : '.*';
                    $str.='(?:\/(?P<'.$attributes.'>'.$reg.'))';
                }
            }elseif(!empty($value)){
                $str.='\/'.$value;
            }
        }
//        $start = strpos($route, '{');
//        $str=substr($route, 0, $start - 1);
//        foreach ($matches[0] as $match){
//            if(strpos($match,'?')){
//                $attributes = substr($match, 1, strlen($match)- 3);
//                $reg = array_key_exists($attributes, self::$replace) ? self::$replace[ $attributes ] : '.*';
//                $str.='(?:\/(?P<'.$attributes.'>'.$reg.'))?';
//            }else{
//                $attributes = substr($match, 1, strlen($match)- 2);
//                $reg = array_key_exists($attributes, self::$replace) ? self::$replace[ $attributes ] : '.*';
//                $str.='(?:\/(?P<'.$attributes.'>'.$reg.'))';
//            }
//        }
        return '/^'.$str.'$/';
    }
}