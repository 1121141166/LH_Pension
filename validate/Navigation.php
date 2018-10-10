<?php
namespace admin\index\validate;

use think\Validate;

class Navigation extends Validate  {

    protected $rule = [
        'name'  =>  'require',
        'url'  =>  'require',
        'sort'  =>  'require',
    ];

    protected $message  =   [
        'name.require' => '请填写导航栏名称',
        'url.require'     => '请填写导航栏地址',
        'sort.require' => '请填写导航栏排序',
    ];

    protected $scene = [
        'add'   =>  ['name','url','sort'],
        'edit'   =>  ['name','url','sort'],
    ];

}