<?php
namespace admin\index\model;
use think\BaseModel;

class Department extends BaseModel  {

    //自定义初始化
    protected function initialize($config='')
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
        $this->connection = $config;
        $this->table = \think\Config::get('database')['prefix'].'department';
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
        $where['is_del'] = '0';
        $category = db('department')->field('*')->where($where)->order('sort asc, id asc')->select();
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

    /*
	*	获取分类树
	*/
    function getTree2($pid = 0,$status='')
    {
        $where['pid'] = $pid;
        if($status != '') {
            $where['status'] = $status;
        }
        $where['is_del'] = '0';
        $category = $this->getList("id,name as text",$where,'sort asc, id asc');
        if ($category['list'])
        {
            foreach($category['list'] as $key => $value)
            {
                $ca = array();
                $ca = $this->getTree2($value['id'],$status);
                if($ca['list']) {
                    $category['list'][$key]['nodes'] = $ca['list'];
                }
            }
        }
        return $category;
    }

    /*
	*	更新分类树缓存
	*/
    function updateTreeCache($id,$data,$action = 'update')
    {
        $cache = $this->getTreeWithCache();
        if ($action == 'update')
        {
            $cache[$id] = $data;
        }
        elseif ($action=='remove')
        {
            unset($cache[$id]);
        }
        cache('deptTree',$cache);
    }

    /*
	*	设置分类树缓存
	*/
    function setTreeCache()
    {
        $tree = $this->getTree();
        // dump($tree);
        cache('deptTree',$tree);
    }

    /*
    *	通过缓存获取分类树
    */
    function getTreeWithCache()
    {
        if (!cache('deptTree'))
        {
            $this->setTreeCache();
        }
        return cache('deptTree');
    }
}