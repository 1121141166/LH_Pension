<?php
namespace admin\index\validate;

use think\Validate;

class Oldfamily extends Validate  {

    protected $rule = [
        'family_id' => 'require',
        'id' => 'require',
        'name'  =>  'require|max:25',
        'idno'  =>  'require|max:18',
        'mobile' =>  'require|number|max:11',
        'guarder' => 'require',
    ];

    protected $message  =   [
        'id.family_id' => 'Family_ID不能为空',
        'id.require' => '老人ID不能为空',
        'name.require' => '请填写名字',
        'name.max'     => '名字最多不能超过25个字符',
        'idno.require' => '请填写身份证号',
        'idno.max'     => '身份证号不能超过18个字符',
        'mobile.require'   => '请填写联系电话',
        'mobile.num'   => '联系电话必须为数字',
        'mobile.max'   => '联系电话最长11位',
        'guarder.require' => '是否为老人监护人不能为空',
    ];

    protected $scene = [
        'add'   =>  [ 'id','name','mobile','idno','guarder'],
        'edit'  =>  ['id','name','mobile','idno','guarder'],
        'del'  =>  ['family_id'],
    ];

}