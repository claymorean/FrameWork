<?php
/*-----------------------------------------------------------------------
 | @author big cat
 | @date   2017年7月11日 19:16:57
 |-----------------------------------------------------------------------
 | IOC 容器
 | 1、自动解析依赖     自动的对依赖进行解析，注入
 | 2、支持单例注入     可以将依赖绑定为单例，实现全局的依赖同步
 | 3、关联参数传值     标量参数采用关联传值，可设定默认值
 | 备注：关联传参才舒服, ($foo = 'foo', $bar), 跳过 foo 直接给 bar 传值多舒服
 |-----------------------------------------------------------------------
 */

class IOCContainer
{
    /**
     * 单例模式的注入类实例
     * 比如我们希望整个生命周期内 Request 类为单例模式 可幂等访问
     * @var array
     */
    public static $singletonInstances = array();

    /**
     * 绑定需要做单例注入的类实例
     * @param  [type] $class_name    [description]
     * @param  [type] $classInstance [description]
     * @return [type]                [description]
     */
    public static function singleton($class_name, $classInstance)
    {
        if ( ! is_object($classInstance) ) {
            throw new Exception("Object need!", 4043);
        }

        static::$singletonInstances[$class_name] = $classInstance;
    }

    /**
     * 获取类实例
     * 通过反射获取构造参数
     * 返回对应的类实例
     * @param  [type] $class_name [description]
     * @return [type]             [description]
     */
    public static function getInstance($class_name)
    {
        //方法参数分为 params 和 default_values
        //如果一个开放构造类作为依赖注入传入它类，我们应该将此类注册为全局单例服务
        $params = static::getParams($class_name);
        return (new ReflectionClass($class_name))->newInstanceArgs($params['params']);
    }

    /**
     * 反射方法参数类型
     * 对象参数：构造对应的实例 同时检查是否为单例模式的实例
     * 标量参数：返回参数名 索引路由参数取值
     * 默认值参数：检查路由参数中是否存在本参数 无则取默认值
     * @param  [type] $class_name [description]
     * @param  string $method     [description]
     * @return [type]             [description]
     */
    public static function getParams($class_name, $method = '__construct')
    {
        $params_set['params'] = array();
        $params_set['default_values'] = array();

        //反射检测类是否显示声明或继承父类的构造方法
        //若无则说明构造参数为空
        if ($method == '__construct') {
            $classRf = new ReflectionClass($class_name);
            if ( ! $classRf->hasMethod('__construct') ) {
                return $params_set;
            }
        }

        //反射方法 获取参数
        $methodRf = new ReflectionMethod($class_name, $method);
        $params = $methodRf->getParameters();

        if ( ! empty($params) ) {
            foreach ($params as $key => $param) {
                if ( $paramClass = $param->getClass() ) {// 对象参数 获取对象实例
                    $param_class_name = $paramClass->getName();
                    if ( array_key_exists($param_class_name, static::$singletonInstances) ) {
                        $params_set['params'][] = static::$singletonInstances[$param_class_name];
                    } else {
                        $params_set['params'][] = static::getInstance($param_class_name);
                    }
                } else { // 标量参数 获取变量名作为路由映射 包含默认值的记录默认值
                    $param_name = $param->getName();

                    if ( $param->isDefaultValueAvailable() ) {// 是否包含默认值
                        $param_default_value = $param->getDefaultValue();
                        $params_set['default_values'][$param_name] = $param_default_value;
                    }

                    $params_set['params'][] = $param_name;
                }
            }
        }

        return $params_set;
    }

    public static function run($class_name, $method, array $params = array())
    {
        if ( ! class_exists($class_name) ) {
            throw new Exception($class_name . "not found!", 4040);
        }

        if ( ! method_exists($class_name, $method) ) {
            throw new Exception($class_name . "::" . $method . " not found!", 4041);
        }

        $classInstance = static::getInstance($class_name);

        $method_params = static::getParams($class_name, $method);

        $singletonInstances = static::$singletonInstances;

        $method_params = array_map(function ($param) use ($params, $method_params, $singletonInstances) {
            if ( is_object($param) ) { //对象要检测是否为全局单例服务
                $param_class_name = get_class($param);
                if ( array_key_exists($param_class_name, $singletonInstances) ) {
                    return $singletonInstances[$param_class_name];
                }

                return $param;
            }

            if ( array_key_exists($param, $params) ) {// 映射传递路由参数
                return $params[$param];
            }

            if ( array_key_exists($param, $method_params['default_values']) ) { // 默认值
                return $method_params['default_values'][$param];
            }

            throw new Exception($param . ' is necessary parameters', 4042); // 路由中没有的则包含默认值
        }, $method_params['params']);

        return call_user_func_array([$classInstance, $method], $method_params);
    }
}

class Foo
{
    public $msg = "foo nothing to say!";

    public function index()
    {
        $this->msg = "foo hello, modified by index method!";
    }
}

class Bar
{
    public $msg = "bar nothing to say!";

    public function index()
    {
        $this->msg = "bar hello, modified by index method!";
    }
}

class BigCatController
{
    public $foo;
    public $bar;

    // 这里自动注入一次 Foo 和 Bar 的实例
    public function __construct(Foo $foo, Bar $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    // 这里再注入一次 同时还解析了标量参数 映射为对应的参数关联传值
    // 所以这里的参数你完全可以乱序的定义，你只要保证 route 参数中存在必要参数即可
    // 默认值参数可以直接省略
    public function index($name = "big cat", Foo $foo, $sex = 'male', $age, Bar $bar)
    {
        // $this->foo $foo 注入为单例模式的 Foo 的实例 所以它们是同一实例对象
        $this->foo->index();
        echo $this->foo->msg . PHP_EOL;
        echo $foo->msg . PHP_EOL;

        // $this->bar $bar 则为两个不同的 Bar 实例 不会相互影响
        $this->bar->index();
        echo $this->bar->msg . PHP_EOL;

        echo $bar->msg . PHP_EOL;

        return "name " . $name . ', age ' . $age . ', sex ' . $sex . PHP_EOL;
    }
}

// ======================================  运行容器  ======================================
$route = [
    'controller' => BigCatController::class,
    'action'     => 'index',
    'params'     => [
        'name' => 'big cat',
        'age'  => 27
    ]
];

try {
    // 单例绑定
    IOCContainer::singleton(Foo::class, new Foo());

    $result = IOCContainer::run($route['controller'], $route['action'], $route['params']);
    echo $result;
} catch (Exception $e) {
    echo $e->getMessage();
}