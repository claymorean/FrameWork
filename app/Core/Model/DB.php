<?php
//数据库db类

namespace App\Core\Model;

class DB{

    public $_conn;

    public function __construct($host='',$user='',$pass='',$dbname='',$charset='',$connect=''){
        $host = $host==''?env('db_host'):$host;
        $user = $user==''?env('db_user'):$user;
        $pass = $pass==''?env('db_password'):$pass;
        $dbname = $dbname==''?env('db_database'):$dbname;
        $charset = $charset==''?env('db_charset'):$charset;
        $connect = $connect==''?env('db_pconnect'):$connect;

        try {
            $dsn = sprintf("mysql:host=%s;dbname=%s;charset=".$charset, $host, $dbname);
            $option = array(
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_PERSISTENT => $connect
            );
            $this->_conn = new \PDO($dsn, $user, $pass, $option);
            return $this->_conn;
        } catch (\PDOException $e) {
            return $e->getMessage();
            // exit('错误: ' . $e->getMessage());
        }
    }

}