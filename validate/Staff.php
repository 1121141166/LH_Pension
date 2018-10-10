<?php
namespace admin\index\validate;

use think\Validate;

class Staff extends Validate  {

    protected $rule = [
        'id' => 'require',
        'fullname'  =>  'require|max:25',
        'account' =>  'require',
        'mobile' =>  'require|number|max:11',
    ];

    protected $message  =   [
        'id.require' => 'ID不能为空',
        'fullname.require' => '请填写名字',
        'fullname.max'     => '名字最多不能超过25个字符',
        'account.require' => '请填写yuan',
        'mobile.require'   => '请填写手机号',
        'mobile.num'   => '手机号必须为数字',
        'mobile.max'   => '手机号最长11',
    ];

    protected $scene = [
        'add'   =>  ['fullname','mobile'],
        'edit'  =>  ['fullname','image'],
        'del'  =>  ['id'],
    ];

}