<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/24
 * Time: 16:57
 */

namespace Core;

class View
{
    private $file='';
    public $_tVar=[];

    public $templateDir='';
	public $leftTag = '<{';
	public $rightTag = '}>';
	public $compileDir = 'cache';
	public $compiledFileExt = '.tmp.php';
	public $templateFileExt = '.html';	//当display() cache() 不使用参数时使用
	public $caching = false;
	public $cacheDir = 'cache';
	public $cacheDirLevels = 0;			//缓存目录层次
	public $cacheFileExt = '.TemplateCache.php';
	public $cacheLifeTime = 3600; 		// 单位 秒
	public $cacheID;
	public $forceCompile = false;
	public $lang=array();

	private $cacheFile;				//缓存文件，在_saveCache()中使用
	private $realCacheID;			//通过计算得出的缓存ID
	
	const MAX_CACHE_DIR_LEVELS=16;	//最大缓存目录层次数量
	
    public function __construct($file)
    {
        $config=array(
                'templateDir'		=> strlen(env('view_dir'))?env('view_dir'):APP_PATH.'/Http/Views',
                'compileDir'		=> strlen(env('cache_dir'))?env('cache_dir'):APP_PATH.'/Http/Cache',
                'cacheDir'			=> strlen(env('cache_dir'))?env('cache_dir'):APP_PATH.'/Http/Cache',
                'cacheDirLevels'	=> env('page_cache_dir_levels'),
                'caching'			=> env('page_caching'),
                'cacheLifeTime'		=> env('page_cache_life_time'),
                'templateFileExt'	=> env('template_file_ext'),
            );
        foreach ($config as $key=>$val) {
			$this->$key = $val;
		}
        $this->file=$file;
    }
	
    //给指定页面传参
    public function assign($name,$array){
        if(is_string($name)){
            $this->tVar[$name]=$array;
        }else{
            foreach($name as $key =>$val){
                $this->tVar[$key] = $val;
            }
        }
    }
	
	/**
	 * 返回模板文件完整路径
     * 支持admin.admin 解析为 admin/admin.view.php
	 *
	 * @param string $file
	 * @return string
	 */
	private function getTemplateFile($file='')
	{
        if(strpos($file,'.')){
            $sondir='';
            $dir=explode('.',$file);
            foreach($dir as $key=>$val){
                $sondir.='/'.$val;
            }
            $file=$this->templateDir.$sondir.'.view.php';
        }else{
            $file=$this->templateDir.'/'.$file.'.view.php';
        }
		return $file;
	}

    /**
     * 替换
	 * 在$this->compile()中替换$foo.var为数组格式$foo['var']
	 *
	 */
	private function compile_replace($str)
	{
		$str=preg_replace('/(\$[a-z_]\w*)\.([\w]+)/',"\\1['\\2']",$str);
		return $this->leftTag.$str.$this->rightTag;
	}
	
	/**
	 * 编译模板文件
	 *
	 * @param string $file
	 * @return string
	 */
	private function compile($file)
	{
		$fullTplPath=$this->getTemplateFile($file);
		$compiledFile=$this->compileDir.'/'.md5($fullTplPath).$this->compiledFileExt;
		if ($this->forceCompile || !is_file($compiledFile) || filemtime($compiledFile)<=filemtime($fullTplPath)) {
			$content=file_get_contents($fullTplPath);
			$leftTag=preg_quote($this->leftTag);
			$rightTag=preg_quote($this->rightTag);
			$search=array(
				'/'.$leftTag.'include ([\w\.\/-]+)'.$rightTag.'/i',			//导入子模板
				'/'.$leftTag.'(\$[a-z_]\w*)\.(\w+)'.$rightTag.'/i',			//将模板标签<{$foo.var}>修改为数组格式<{$foo['var']}>
				'/'.$leftTag.'(.+?\$[a-z_]\w*\.\w+.+?)'.$rightTag.'/ie',	//将模板标签中的$foo.var修改为数组格式$foo['var']
				'/'.$leftTag.'(else if|elseif) (.*?)'.$rightTag.'/i',
				'/'.$leftTag.'for (.*?)'.$rightTag.'/i',
				'/'.$leftTag.'while (.*?)'.$rightTag.'/i',
				'/'.$leftTag.'(loop|foreach) (.*?) as (.*?)'.$rightTag.'/i',
				'/'.$leftTag.'if (.*?)'.$rightTag.'/i',
				'/'.$leftTag.'else'.$rightTag.'/i',
				'/'.$leftTag."(eval) (.*?)".$rightTag.'/is',
				'/'.$leftTag.'\/(if|for|loop|foreach|while)'.$rightTag.'/i',
				'/'.$leftTag.'((( *(\+\+|--) *)*?(([_a-zA-Z][\w]*\(.*?\))|\$((\w+)((\[|\()(\'|")?\$*\w*(\'|")?(\)|\]))*((->)?\$?(\w*)(\((\'|")?(.*?)(\'|")?\)|))){0,})( *\.?[^ \.]*? *)*?){1,})'.$rightTag.'/i',
				'/'.$leftTag.'\%([\w]+)'.$rightTag.'/',						//多语言
                // '/\$([\w\.\/-]+)//',
                // '/\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/i'  // \s* 滤出所有空格、回车符的字符
			);
			$replace=array(
				'<?php include($this->display("\\1"));?>',
				$this->leftTag."\\1['\\2']".$this->rightTag,
				"\$this->compile_replace('\\1')",
				'<?php }else if (\\2){ ?>',
				'<?php for (\\1) { ?>',
				'<?php $__i=0; while (\\1) {$__i++; ?>',
				'<?php $__i=0; foreach ((array)\\2 as \\3) { $__i++; ?>',
				'<?php if (\\1){ ?>',
				'<?php }else{ ?>',
				'<?php \\2; ?>',
				'<?php } ?>',
				'<?php echo \\1;?>',
				'<?php echo $this->lang["\\1"];?>',
                // '$this->_tVar["\\1"]',
                // '$this->_tVar["${1}"]'
			);
			$content=preg_replace($search,$replace,$content);
			file_put_contents($compiledFile,$content,LOCK_EX);
		}
		return $compiledFile;
	}

    	
	/**
	 * 返回编译后的模板文件完整路径
	 *
	 * @param string $file
	 * @return string
	 */
	public function display()
	{
        $tplFile=$this->getTemplateFile($this->file);

		if(!file_exists($tplFile)){
			return false;
		}
        $compileFile=$this->compile($this->file);
        include $compileFile;

        return $compileFile;
	}
	
	/**
	 * 根据是否使用缓存，输出缓存文件内容
	 *
	 * @param string $tplFile
	 * @param string $cacheID
	 */
	public function cache($tplFile,$cacheID='')
	{
		$this->cacheID=$cacheID;
		$cacheFile=$this->getCacheFileName($file,$cacheID);
		if ($this->cached($file,$cacheID)) {
			readfile($cacheFile);
			exit;
		} elseif ($this->caching) {
			ob_start(array(&$this,'_saveCache'));
			$this->cacheFile=$cacheFile;
		}
	}

	
    //缓存
    /**
	 * 判断缓存文件是否有效
	 *
	 * @param string $file
	 * @param string $cacheID
	 * @return boolean
	 */
	public function cached($file='',$cacheID='')
	{
		$file=$this->getTemplateFile($file);
		$this->cacheID=$cacheID;
		$cachefile=$this->getCacheFileName($file,$cacheID);
		if ($this->caching && is_file($cachefile) && (filemtime($cachefile)+$this->cacheLifeTime)>time()) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 获取缓存文件完整路径
	 *
	 * @param string $file
	 * @param string $cacheID
	 * @return string
	 */
	private function getCacheFileName($file,$cacheID)
	{
		if (!strlen($this->realCacheID)) {
			$this->realCacheID=$cacheID!=''?$cacheID:$_SERVER['SCRIPT_NAME'].$_SERVER['QUERY_STRING'];
			$this->realCacheID.=$this->templateDir.$file.APP_NAME;
		}
		$md5id=md5($this->realCacheID);
		$this->cacheDirLevel=$this->getCacheDirLevel($md5id);
		return $this->cacheDir.$this->cacheDirLevel.'/'.$md5id.$this->cacheFileExt;
	}
	
	/**
	 * 获取缓存目录层次
	 *
	 */
	private function getCacheDirLevel($md5id)
	{
		$levels=array();
		$levelLen=2;
		for ($i=0; $i<$this->cacheDirLevels; $i++) {
			$levels[]='TepmlateCache_'.substr($md5id,$i*$levelLen,$levelLen);
		}
		return !count($levels) ? '' : '/'.implode('/',$levels);
	}

	/**
	 * 回调函数，供cache()函数使用
	 *
	 * @param string $output
	 * @return string
	 */
	public function _saveCache($output)
	{
		$cacheDir=$this->cacheDir.$this->cacheDirLevel;
		is_dir($cacheDir) or mkdir($cacheDir,0777,true);
		file_put_contents($this->cacheFile,$output,LOCK_EX);
		return $output;
	}

}