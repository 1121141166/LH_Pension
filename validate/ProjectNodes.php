<?php
namespace admin\index\validate;

use think\Validate;

class ProjectNodes extends Validate  {

    protected $rule = [
        'id' => 'require',
        'project_id' => 'require',
        'title'  =>  'require',
        'introduction' =>  'require',
        'begintime' =>  'require',
        'endtime' =>  'require',
    ];

    protected $message  =   [
        'id.require' => 'ID不能为空',
        'project_id.require' => '项目ID不能为空',
        'title.require' => '请填写项目标题',
        'introduction.require' => '请填写项目介绍',
        'begintime.require'   => '请选择项目开始时间',
        'endtime.require'   => '请选择项目结束时间',
    ];

    protected $scene = [
        'add'   =>  ['title','project_id','introduction','begintime','endtime'],
        'edit'  =>  ['title','project_id','introduction','begintime','endtime'],
        'del'  =>  ['id'],
    ];

}