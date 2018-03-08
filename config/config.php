<?php
return [
    #系统参数
    'default_controller'	=>	'main',				//默认控制器名
    'default_action'		=>	'index',			//默认action名
    'view_dir'              =>  '',                 //视图目录，为空时默认在APP下面的views
    'cache_dir'				=>	'',					//缓存目录，为空时默认在APP下面的cache
    'compile_include_files'	=>	true,				//编译、缓存引入的文件
    'session_start'			=>	true,				//启动session
    'default_timezone'		=>	'Asia/Shanghai',	//服务器所在时区
];