<?php

//加载环境配置
function env($var=''){
    $env=require __DIR__."/config/config.php";
    if($var==''){
        return $env;
    }
    return $env[$var];
}

function dd($var){
    echo '<pre>'.var_dump($var);
    exit;
}