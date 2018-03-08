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
            $modelFile=APP_PATH.'/Http/Model/'.$modelName.'.php';
            if (!is_file($modelFile)) {
                die("<h1>Invalid Request</h1>\nModel <strong>$modelName</strong> not found!");
            }
            $modelName='App\Http\Model\\'.$modelName;
            $m=new $modelName();
            $this->models[$modelName]=$m;
            return $m;
        }
    }

    //可操作的新建数据库db类
    public function db()
    {
        if ($this->db===null) {
            $this->db=new App\Core\Model\DB(
                env('db_host'),
                env('db_user'),
                env('db_password'),
                env('db_database'),
                env('db_charset'),
                env('db_pconnect')
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
                'cacheDir'	=>	strlen(env('cache_dir'))?env('cache_dir').'/':APP_PATH.'/cache',
                'lifeTime'	=>	env('cache_life_time'),
                'dirLevels'	=>	env('cache_dir_levels'),
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
    public function view($file)
    {
        if ($this->view===null) {
            $this->view=new View($file);
        }
        return $this->view;
        // return $this->view->display($viewfile);
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