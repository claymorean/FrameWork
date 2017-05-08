<?php
namespace App\Http\Controller;

use App\Core\Controller;
class mainController extends Controller{
    protected function _beforAction()
    {
//		$this->fullPageCache();
    }

    public function indexAction()
    {

        $mitem=$this->model('item')->selectAll();

        echo '<pre>';
        var_dump($mitem);
        echo __FILE__.'<br>'.$_SERVER["HTTP_HOST"].'<br>'.$_SERVER["REQUEST_URI"].'<br>'.APP_PATH;
    }

    public function showAction()
    {
        $uid=(int)$_GET['uid'];
        $member=$this->model('member')->find(null,$uid);
        dd($member);

    }
}
