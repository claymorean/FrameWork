<?php
namespace App\Controllers;

use App\Views\View;
class HomeController extends Controller{
    public function index(){
        $this->view=View::make('index');
    }
}