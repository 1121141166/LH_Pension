<?php
namespace admin\index\validate;

use think\Validate;

class Project extends Validate  {

    protected $rule = [
        'id' => 'require',
        'title'  =>  'require',
        'introduction' =>  'require',
        'priority' =>  'require',
        'begintime' =>  'require',
        'endtime' =>  'require',
    ];

    protected $message  =   [
        'id.require' => 'ID不能为空',
        'title.require' => '请填写项目标题',
        'introduction.require' => '请填写项目介绍',
        'priority.require'   => '请选择项目紧急度',
        'begintime.require'   => '请选择项目开始时间',
        'endtime.require'   => '请选择项目结束时间',
    ];

    protected $scene = [
        'add'   =>  ['title','introduction','priority','begintime','endtime'],
        'edit'  =>  ['title','introduction','priority','begintime','endtime'],
        'del'  =>  ['id'],
    ];

}