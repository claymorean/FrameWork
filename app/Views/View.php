<?php
namespace App\Views;
class View{
    const VIEW_PATH=APP_PATH.'Views';

    public $view;
    public $data;

    public function __construct($view)
    {
        $this->view = $view;
    }

    public static function make($viewName=null)
    {
        # code...
        if($viewName){
            $filePath=self::getViewPath($viewName);
            if(is_file($filePath)){
                return new View($filePath);
            }else{
                return new Exception('File not exist');
            }
        }else{
            return new Exception('Name of view must be exist');
        }
    }

    public function with($key,$value=null){
        if(is_array($key)){
            $this->$data=$key;
        }else{
            $this->$data[$key]=$value;
        }
        return $this;
    }

    public static function getViewPath($viewName)
    {
        $filePath=str_replace('.','/',$viewName);
        return self::VIEW_PATh.'/'.$filePath.'.view.php';
    }

    public function __call($method, $parameters)
    {
        // ● PascalCase
        // ● camelCase
        // ● snake_case
        if (starts_with($method, 'with'))
        {
            return $this->with(camelCase(substr($method, 4)), $parameters[0]);
        }
        throw new BadMethodCallException("方法 [$method] 不存在！.");
    }
}