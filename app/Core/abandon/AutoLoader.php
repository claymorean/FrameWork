<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/27
 * Time: 14:56
 * 废弃 使用composer自动加载
 */
Class AutoLoader
{
    /** @var bool Whether the autoloader has been registered. */
    private static $registered = false;

    /**
     *
     * @param bool $prepend Whether to prepend the autoloader instead of appending
     */
    static public function register($prepend = false) {
        if (self::$registered === true) {
            return;
        }

        spl_autoload_register(array(__CLASS__, 'autoload'), true, $prepend);
        self::$registered = true;
    }

    /**
     * Handles autoloading of classes.
     *
     * @param string $class A class name.
     */
    static public function autoload($class) {
        $fileName = __DIR__ . strtr(substr($class, 9), '\\', '/') . '.php';
        if (file_exists($fileName)) {
            require $fileName;
        }
    }
}