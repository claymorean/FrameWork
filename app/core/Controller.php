<?php
/**
 * Action控制器基类 抽象类
 */
namespace App\Core;

abstract class Controller
{
    //视图对象（模板类）
    private $view = null;
    //缓存对象
    private $cache = null;
    //保存已经实例化的model类
    private $models=array();
    //实例货的db类
    private $db = null;

    /**
     * 架构函数
     * @access public
     */
    public function __construct()
    {
    }

    /**
     * 未知的 action
     *
     */
    public function __call($method,$params)
    {
        echo "<h1>Invalid Request</h1>\n";
        echo "Action <strong>$method</strong> from ".App::$controller." not found.\n";
        exit;
    }

    /**
     * 自动在其它action前运行
     * 当该方法返回值为false时，将不会执行当前的action方法，但是 _afterAction()无论如何都会运行
     */
    protected function _beforAction() {}

    //自动在其它action运行完后运行
    protected function _afterAction() {}

    /**
     * 注册类文件路径
     *
     * @param array|string $className
     * @param string $classFile
     */
    public function regClassFile($className,$classFile=null)
    {
        if (is_array($className)) {
            foreach ($className as $key => $value) {
                $this->regClassFile($key,$value);
            }
        } else {
            $GLOBALS['classFiles'][$className]=$classFile;
        }
    }

    /**
     * 加载模型对象
     *
     * @param string $modelName
     * @param boolean $isFullModelName
     * @return object
     */
    public function model($modelName,$isFullModelName=false)
    {
        $isFullModelName or $modelName.='Model';
        if (isset($this->models[$modelName])) {
            return $this->models[$modelName];
        } else {
            $modelFile=APP_PATH.'/model/'.$modelName.'.php';
            if (!is_file($modelFile)) {
                die("<h1>Invalid Request</h1>\nModel <strong>$modelName</strong> not found!");
            }
            require($modelFile);
            $m=new $modelName();
            $this->models[$modelName]=$m;
            return $m;
        }
    }

    public function db()
    {
        if ($this->db===null) {
            $this->db=new DB(
                App::config('db_host'),
                App::config('db_user'),
                App::config('db_password'),
                App::config('db_database'),
                App::config('db_charset'),
                App::config('db_pconnect')
            );
        }
        return $this->db;
    }//db

    /**
     * 返回cache对象(数据缓存)
     *
     * @return object
     */
    public function cache()
    {
        if ($this->cache===null) {
            $options=array(
                'cacheDir'	=>	strlen(App::config('cache_dir'))?App::config('cache_dir').'/':APP_PATH.'/cache',
                'lifeTime'	=>	App::config('cache_life_time'),
                'dirLevels'	=>	App::config('cache_dir_levels'),
            );
            $this->cache=new Cache($options);
        }
        return $this->cache;
    }

    /**
     * 返回视图对象
     *
     * @return object
     */
    public function view()
    {
        if ($this->view===null) {
            $options=array(
                'templateDir'		=> APP_PATH.'/view',
                'compileDir'		=> strlen(App::config('cache_dir'))?App::config('cache_dir'):APP_PATH.'/cache',
                'cacheDir'			=> strlen(App::config('cache_dir'))?App::config('cache_dir'):APP_PATH.'/cache',
                'cacheDirLevels'	=> App::config('page_cache_dir_levels'),
                'caching'			=> App::config('page_caching'),
                'cacheLifeTime'		=> App::config('page_cache_life_time'),
                'templateFileExt'	=> App::config('template_file_ext'),
            );
            $this->view=new View($options);
        }
        return $this->view;
    }

    /**
     * 读取、输出页面缓存
     *
     * @param string $tplFile
     * @param string $cacheID
     */
    public function fullPageCache($tplFile='',$cacheID='')
    {
        $this->view()->cache($tplFile,$cacheID);
    }

    /**
     * 返回编译后的模板文件路径
     *
     * @param string$file
     * @return string
     */
    public function display($file='')
    {
        return $this->view()->display($file);
    }

    /**
     * 运行控制器
     *
     * @param string $action
     */
    public function run($action)
    {
        $this->_beforAction();
        $this->{$action.'Action'}();
        $this->_afterAction();
    }

    /**
     * 计算程序运行使用时间
     *
     * @return float
     */
    public function expendTime()
    {
        return round(microtime(true)-$GLOBALS['_beginTime'],6);
    }

    /**
     * URL重定向
     *
     * @param string $url
     * @param int $time
     * @param string $msg
     */
    public function redirect($url,$time=0,$msg='')
    {
        //多行URL地址支持
        $url = str_replace(array("\n", "\r"), '', $url);
        if (!headers_sent()) {
            // redirect
            if(0===$time) {
                header("Location: ".$url);
            }else {
                header("refresh:{$time};url={$url}");
                echo($msg);
            }
            exit();
        } else {
            $str	= "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
            if($time!=0)
                $str   .=   $msg;
            exit($str);
        }
    }



}//类定义结束
?>