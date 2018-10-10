<?php
namespace admin\index\model;
use think\BaseModel;

class StaffType extends BaseModel  {

    //自定义初始化
    protected function initialize($config='')
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
        $this->connection = $config;
        $this->table = \think\Config::get('database')['prefix'].'staff_type';
    }

    /*
	*	获取分类树
	*/
    function getTree($pid,$status='')
    {
        static $tree = array();
        if($pid) $where['pid'] = $pid;
        if($status != '') {
            $where['status'] = $status;
        }

        $category = db('staffType')->field('*')->where($where)->order('sort asc, id asc')->select();
        if ($category)
        {
            foreach($category as $value)
            {
                $tree[$value['id']] = $value;
                $this->getTree($value['id'],$status);
            }
        }
        return $tree;
    }
}