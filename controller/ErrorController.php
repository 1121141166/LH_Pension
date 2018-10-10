<?php
namespace admin\index\controller;

use think\Controller;

class ErrorController extends Controller
{

    public function _empty($method){
        $this->assign('method',$method);
        return $this->fetch('Index/404');
    }
}
