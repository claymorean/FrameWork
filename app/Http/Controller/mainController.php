<?php
namespace App\Http\Controller;

use App\Core\Controller;
use App\Http\Model\ItemModel;
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
        var_dump($item);
        var_dump($itemq);
        echo __FILE__.'<br>'.$_SERVER["HTTP_HOST"].'<br>'.$_SERVER["REQUEST_URI"].'<br>'.APP_PATH;
    }

    public function showAction()
    {
        $uid=(int)$_GET['uid'];
        $member=$this->model('member')->find(null,$uid);
        dd($member);

    }
}
