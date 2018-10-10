<?php
namespace admin\index\validate;

use think\Validate;

class Enterprise extends Validate  {

    protected $rule = [
        'id' => 'require',
        'name'  =>  'require|max:25',
        'logo' =>  'require',
    ];

    protected $message  =   [
        'id.require' => 'ID不能为空',
        'name.require' => '请填写名称',
        'name.max'     => '名称最多不能超过25个字符',
        'logo.require'   => '请选择图片',
        'logo.fileExt'   => '请选择jpg,png的图片',
    ];

    protected $scene = [
        'add'   =>  ['name','logo'],
        'edit'  =>  ['name','logo'],
        'del'  =>  ['id'],
    ];

}