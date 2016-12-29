<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/26
 * Time: 10:48
 */
Class Model extends Query{
    protected $_model;
    protected $_table;
    protected static $_instance="";

    public static function getInstance(){
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function __construct()
    {
        parent::__construct(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        // 连接数据库
        //$this->connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        // 获取模型类名
        $this->_model = get_class($this);

        // 删除类名最后的 Model 字符
        $this->_model = substr($this->_model, 0, -5);

        // 数据库表名与类名一致
        $this->_table = strtolower($this->_model);
    }
}