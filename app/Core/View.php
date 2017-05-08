<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/24
 * Time: 16:57
 */

namespace App\Core;


class View
{
    protected $_vname;
    public function __construct($array=[])
    {
        if(empty($array)){
            $array=array(
                'templateDir'		=> APP_PATH.'/Http/Views',
                'compileDir'		=> strlen(env('cache_dir'))?env('cache_dir'):APP_PATH.'/cache',
                'cacheDir'			=> strlen(env('cache_dir'))?env('cache_dir'):APP_PATH.'/cache',
                'cacheDirLevels'	=> env('page_cache_dir_levels'),
                'caching'			=> env('page_caching'),
                'cacheLifeTime'		=> env('page_cache_life_time'),
                'templateFileExt'	=> env('template_file_ext'),
            );
        }
    }

    public function assign($name,$array){
        if(is_array($name)) {
            foreach ($name as $key => $value){
                $key=$value;
            }
        }elseif(is_object($name)){
            foreach($name as $key =>$val)
                $this->tVar[$key] = $val;
        }else {
            $name=$array;
        }
    }

    //解析模板文件<{之类
    public function fetch(){
        // 模板文件解析标签
        tag('view_template',$templateFile);
        // 模板文件不存在直接返回
        if(!is_file($templateFile)) return NULL;
        // 页面缓存
        //ob_start();
        //页面缓存开始的标志，此函数一下的内容直至ob_end_flush()或者ob_end_clean()都保存在页面缓存中；
        //ob_get_contents();
        //用来获取页面缓存中的内容,获取到以后呢,我们就可以想怎么处理这些内容都行了,过滤字段啦,匹配内容啦,都可以~~~
        //ob_end_flush();
        //表示页面缓存结束,并且经我验证,缓存的内容将输出到当前页面上,也就是可以显示缓存内容.
        //ob_implicit_flush(0);
        if('php' == strtolower($this->_vname)) { // 使用PHP原生模板
            // 模板阵列变量分解成为独立变量
            // extract从数组中将变量导入到当前的符号表 即替换相同键的值
            extract($this->tVar, EXTR_OVERWRITE);
            // 直接载入PHP模板
            include $templateFile;
        }else{
            // 视图解析标签
            $params = array('var'=>$this->tVar,'file'=>$templateFile);
            tag('view_parse',$params);
        }
        // 获取并清空缓存
        //$content = ob_get_clean();
        // 内容过滤标签
        tag('view_filter',$content);
        // 输出模板文件
        return $content;
    }
    public function tfecth()
    {

    }
    
    public function display($templateFile=APP_PATH.'/Http/Views/index.view.php',$charset='',$contentType=''){
        G('viewStartTime');
        // 视图开始标签
        tag('view_begin',$templateFile);
        // 解析并获取模板内容
        $content = $this->fetch($templateFile);
        // 输出模板内容
        $this->show($content,$charset,$contentType);
        // 视图结束标签
        tag('view_end');
    }

}