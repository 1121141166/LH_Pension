<?php
namespace admin\index\validate;

use think\Validate;

class MsgAd extends Validate  {

    protected $rule = [
        'id' => 'require',
        'name'  =>  'require|max:25',
        'image' =>  'require',
    ];

    protected $message  =   [
        'id.require' => 'ID不能为空',
        'name.require' => '请填写名称',
        'name.max'     => '名称最多不能超过25个字符',
        'image.require'   => '请选择图片',
        'image.fileExt'   => '请选择jpg,png的图片',
    ];

    protected $scene = [
        'add'   =>  ['name','image'],
        'edit'  =>  ['name','image'],
        'del'  =>  ['id'],
    ];

}