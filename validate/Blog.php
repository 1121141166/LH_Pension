<?php
namespace admin\index\validate;

use think\Validate;

class Blog extends Validate  {

    protected $rule = [
        'id' => 'require',
        'title'  =>  'require',
        'image' =>  'require',
        'short_desc' =>  'require',
        'content' =>  'require',
    ];

    protected $message  =   [
        'id.require' => 'ID不能为空',
        'title.require' => '请填写名称',
        'image.require'   => '请选择图片',
        'image.fileExt'   => '请选择jpg,png的图片',
        'short_desc.require' => '请填写简短描述',
        'content.require' => '请填写博文内容',
    ];

    protected $scene = [
        'add'   =>  ['title','image','short_desc','content'],
        'edit'  =>  ['title','image','short_desc','content'],
        'del'  =>  ['id'],
    ];

}