<?php
namespace admin\index\validate;

use think\Validate;

class Oldman extends Validate  {

    protected $rule = [
        'id' => 'require',
        'name'  =>  'require|max:25',
        'idno'  =>  'require|max:18',
        'mobile' =>  'require|number|max:11',
        'address' => 'require',
        'agent' => 'require',
    ];

    protected $message  =   [
        'id.require' => 'ID不能为空',
        'name.require' => '请填写名字',
        'name.max'     => '名字最多不能超过25个字符',
        'idno.require' => '请填写身份证号',
        'idno.max'     => '身份证号不能超过18个字符',
        'mobile.require'   => '请填写联系电话',
        'mobile.num'   => '联系电话必须为数字',
        'mobile.max'   => '联系电话最长11位',
        'address.require' => '家庭住址不能为空',
        'agent.require' => '经办人不能为空',
    ];

    protected $scene = [
        'add'   =>  ['name','mobile','idno','address','agent'],
        'edit'  =>  ['name','idno','address','agent'],
        'del'  =>  ['id'],
    ];

}