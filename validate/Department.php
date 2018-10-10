<?php
namespace admin\index\validate;

use think\Validate;

class Department extends Validate  {

    protected $rule = [
        'id' => 'require',
        'name'  =>  'require',
        'code' =>  'require|number',
    ];

    protected $message  =   [
        'id.require' => 'ID不能为空',
        'name.require' => '请填写部门名称',
        'code.require'   => '请填写部门编码',
    ];

    protected $scene = [
        'add'   =>  ['name','code'],
        'edit'   =>  ['name','code'],
        'del'  =>  ['id'],
    ];

}