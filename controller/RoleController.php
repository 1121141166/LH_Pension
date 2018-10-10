<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/24
 * Time: 16:07
 */
namespace admin\index\controller;
use admin\index\controller\BaseController;

class RoleController extends BaseController {

    private $role = array('1'=>'管理员','2'=>'客服');
    private $menu;

    function index()
    {
        $data = $this->loadModel('role')->getList('*',array(),'id asc',true);
        $this->assign('data',$data);
        return $this->fetch();
    }

    function add()
    {
        if (input('submit'))
        {
            $this->save();
        }
        return $this->fetch('edit');
    }

    function edit()
    {
        $id = intval(input('id'));
        if (input('submit')) $this->save($id);
        $admin = $this->loadModel('role')->getInfoByid($id);
        $this->assign('data',$admin);
        return $this->fetch('edit');
    }

    private function save($id = 0)
    {
        $_params = $this->get_params(array('name','submit'));
        $data = array();
        $this->vaild_params('is_empty',$_params['name'],'请填写账号名');

        $data['name'] = trim($_params['name']);
        if ($id)
        {
            $rs = $this->loadModel('role')->update($data,array('id'=>$id));
        }
        else
        {
            $data['addtime'] = time();
            $rs = 	$this->loadModel('role')->save($data);
        }
        $this->ajaxOut($rs,'role/index');
    }

    /*
    *	权限设置
    */
    function privilege()
    {
        $adminid = intval(input('id'));
        $admin = $this->loadModel('role')->getInfo('*',array('id'=>$adminid));
        if (input('submit'))
        {
            $actions = input('post.actions/a');
            $actions = array('actions'=>$actions);
            $actions = strtolower(json_encode($actions));
            $data = array('actions'=>$actions);
            $rs = $this->loadModel('role')->update($data,array('id'=>$admin['id']));
            $this->ajaxOut($rs,'role/index',true,array('id'=>$admin['id']));
        }

        $admin_actions = json_decode($admin['actions'],true);
        $module = $this->setMenu();
        $this->assign('admin_actions',$admin_actions);
        $this->assign('adminid',$adminid);
        $this->assign('module',$module);
        return $this->fetch();
    }

    function delete()
    {
        $id = intval(input('id'));
        $this->vaild_params('compare',array($id,0),'请选择要删除的项！');
        $rs = $this->loadModel('role')->del(array('id'=>$id));
        $this->ajaxOut($rs,'role/index');
    }

    //菜单列表
    private function setMenu($pid = '0')
    {
        static $module;
        $menu = model('Module')->where(array('pid'=>$pid))->select();
        if ($menu)
        {
            foreach($menu as $value)
            {
                $module[$pid][] = $value;
                $this->setMenu($value['id']);
            }
        }
        return $module;
    }
}