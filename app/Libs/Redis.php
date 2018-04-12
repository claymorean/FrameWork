<?php
/**
 * Created by PhpStorm.
 * User: senuer
 * Date: 2018/3/8
 * Time: 12:51
 */

namespace App\Libs;

use Predis\Client;

class Redis {

    const CONFIG_FILE = '/config/cache.php';
    protected static $redis;

    //保存类的实例的静态成员变量
    static private $_instance=null;
    //私有的构造方法
    private function __construct(){
        $cache=require BASE_PATH.self::CONFIG_FILE;
        self::$redis = new Client($cache['redis']);
    }
    //用于访问类的实例的公共的静态方法
    public static function getInstance(){
        if(!(self::$_instance instanceof Redis)){
            self::$_instance = new self;
        }
        return self::$_instance;
    }
    //类的其它方法

    public function has($key) {
        return ! is_null(self::$redis->get($key));
    }

//    public function set($key, $value, $time = null, $unit = null) {
//        if ($time) {
//            switch ($unit) {
//                case 'h':
//                    $time *= 3600;
//                    break;
//                case 'm':
//                    $time *= 60;
//                    break;
//                case 's':
//                case 'ms':
//                    break;
//                default:
//                    throw new InvalidArgumentException('单位只能是 h m s ms');
//                    break;
//            }
//            if ($unit == 'ms') {
//                self::_psetex($key, $value, $time);
//            } else {
//                self::_setex($key, $value, $time);
//            }
//        } else {
//            self::$redis->set($key, $value);
//        }
//    }

    public function set($key,$value,$time) {
        //time 单位：秒
        if ($time){
            self::_setex($key, $value, $time);
        }else{
            self::$redis->set($key, $value);
        }
    }

    public function get($key) {
        return self::$redis->get($key);
    }

    public function delete($key) {
        return self::$redis->del($key);
    }

    public function lock($key,$exp){
        $bool=self::$redis->setnx($key,time()+$exp);
        if(time()>self::$redis->get($key)){
            $bool=self::$redis->set($key,time()+$exp);
        }
        return $bool;
    }

    public function unlock($key){
        return self::$redis->delete($key);
    }

    private static function _setex($key, $value, $time) {
        self::$redis->setex($key, $time, $value);
    }

    private static function _psetex($key, $value, $time) {
        self::$redis->psetex($key, $time, $value);
    }
}