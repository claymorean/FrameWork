<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/12
 * Time: 13:15
 * @property mixed _config
 */
class App
{
    private static $_config;
    public $controller;
    public $action;
    /**
     * App constructor.
     * @param array $config
     */
    public function __construct($config=array())
    {
        //设置
        self::$_config=require(CONFIG_PATH.'/config.php');
        if (empty($config)) { $config=require(CONFIG_PATH.'/config.php'); }
        foreach ((array)$config as $key=>$val) {
            self::$_config[$key]=$val;
        }
        //解析controller和action URL使用 ?a= ?c=
        $ctr = isset($_GET['c']) ? $_GET['c'] : null;
        $act = isset($_GET['a']) ? $_GET['a'] : null;

        $this->controller	= $ctr ? $ctr : self::config('default_controller');
        $this->action		= $act ? $act : self::config('default_action');

        //是否开启sesseion
//      $this->config('session_start') && session_start();
        date_default_timezone_set(self::config('default_timezone'));
//		header('Content-Type: text/html; charset='.$this->config('page_charset'));
    }
    /**
     * 获取配置信息
     *
     * @param string $key
     * @return mixed
     */
    public static function config($key=null)
    {
        if (null===$key) {
            return App::$_config;
        } elseif (isset(App::$_config[$key])) {
            return App::$_config[$key];
        } else {
            return null;
        }
    }
    /**
     * 运行应用实例
     * @access public
     * @return void
     */
    public function run()
    {
        //检测控制器文件是否存在
        if (!is_file(APP_PATH.'/controller/'.$this->controller.'Controller.php')) {
            die("<h1>Invalid Request</h1>\nController <strong>".$this->controller."</strong> not found.");
        }
        //导入必需文件
        require(SYS_PATH.'/Controller.php');
        require(APP_PATH.'/Controller/'.$this->controller.'Controller.php');
        //实例化控制器并运行
        $controllerName=$this->controller.'Controller';
        $controller=new $controllerName();
        $controller->run($this->action);
    }
}