<?php
return [
    #系统参数
    'default_controller'	=>	'main',				//默认控制器名
    'default_action'		=>	'index',			//默认action名
    'view_dir'              =>  '',                  //视图目录，为空时默认在APP下面的views
    'cache_dir'				=>	'',					//缓存目录，为空时默认在APP下面的cache
    'compile_include_files'	=>	true,				//编译、缓存引入的文件
    'session_start'			=>	false,				//启动session
    'default_timezone'		=>	'Asia/Shanghai',	//服务器所在时区
    #页面、模板参数
    'page_charset'			=>	'utf-8',			//页面编码
    'page_caching'			=>	false,				//使用页面缓存
    'page_cache_life_time'	=>	3600,				//页面缓存有效期，单位：秒
    'page_cache_dir_levels'	=>	0,					//页面缓存目录层次数量
    'template_file_ext'		=>	'.html',			//模板文件后缀名
    #缓存参数
    'cache_dir_levels'		=>	0,					//缓存目录层次数量
    'cache_life_time'		=>	3600,				//默认缓存有效期，单位：秒
    #mysql参数
	'db_type'				=>	'mysql',
    'db_host'				=>	'localhost',		//mysql主机
    'db_user'				=>	'root',				//mysql用户名
    'db_password'			=>	'root',				//mysql密码
    'db_database'			=>	'todo',				//mysql数据库名
    'db_charset'			=>	'utf8',				//mysql编码
    'db_pconnect'			=>	false,				//是否使用长链接
    'db_table_prefix'		=>	'',					//mysql表前缀
];