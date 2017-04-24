<?php
namespace App;

class mainController extends Controller{
    protected function _beforAction()
    {
//		$this->fullPageCache();
    }

    public function indexAction()
    {
        $item=new ItemModel();
        $itemq=$item->selectAll();
        echo "<pre>";
        var_dump($itemq);
        echo __FILE__.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"].'<br>'.APP_PATH;
    }

    public function showAction()
    {
        $uid=(int)$_GET['uid'];
        $member=$this->model('member')->find(null,$uid);
        pp($member);

    }
}
