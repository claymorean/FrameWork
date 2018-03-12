<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/12
 * Time: 13:15
 * @property mixed _config
 */
namespace Core;

class App
{
    private static $_config;
    public $controller;
    public $action;
    /**
     * app constructor.
     * @param array $config
     */
    public function __construct($config=array())
    {
        //设置
        self::$_config=env();

        if (empty($config)) { $config= self::$_config; }
        foreach ((array)$config as $key=>$val) {
            self::$_config[$key]=$val;
        }
        //解析controller和action URL使用 ?a= ?c=
        $ctr = isset($_GET['c']) ? $_GET['c'] : null;
        $act = isset($_GET['a']) ? $_GET['a'] : null;

        $this->controller	= $ctr ? $ctr : self::$_config['default_controller'];
        $this->action		= $act ? $act : self::$_config['default_action'];

        //是否开启sesseion
        self::$_config['session_start'] && session_start();
        date_default_timezone_set(self::$_config['default_timezone']);
//		header('Content-Type: text/html; charset='.$this->config('page_charset'));
    }
    /**
     * 运行应用实例
     * @access public
     * @return void
     */
    public function run()
    {
        //检测控制器文件是否存在
        if (!is_file(APP_PATH.'/Http/controller/'.$this->controller.'Controller.php')) {
            die("<h1>Invalid Request</h1>\nController <strong>".$this->controller."</strong> not found.");
        }
        
        //实例化控制器并运行
        $controllerName='\App\Http\Controller\\'.$this->controller.'Controller';
        $controller=new $controllerName();
        $controller->run($this->action);
    }
}