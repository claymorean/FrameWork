<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/26
 * Time: 16:23
 */
include "config/config.php";
include "core/Model/Query.php";
include "core/Model/Model.php";
include "app/Model/ItemModel.php";
$item=new ItemModel();
$itemq=$item->selectAll();
echo "<pre>";
var_dump($itemq);
