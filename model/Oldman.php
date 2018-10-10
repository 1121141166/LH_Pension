<?php
namespace admin\index\model;
use think\BaseModel;

class Oldman extends BaseModel  {

//自定义初始化
protected function initialize($config='')
{
    //需要调用`Model`的`initialize`方法
    parent::initialize();
    //TODO:自定义的初始化
    $this->connection = $config;
    $this->table = \think\Config::get('database')['prefix'].'oldman';
}
}