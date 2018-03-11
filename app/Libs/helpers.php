<?php
/**
 * Created by PhpStorm.
 * User: senuer
 * Date: 2018/3/8
 * Time: 13:04
 */
if (! function_exists('asset')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function asset($file){
        return $_SERVER['SERVER_NAME'].$file;
    }
}