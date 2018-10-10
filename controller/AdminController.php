<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/24
 * Time: 16:07
 */
namespace admin\index\controller;
use admin\index\controller\BaseController;

class AdminController extends BaseController {

    private $role = array('1'=>'管理员','2'=>'客服');
    private $menu;

    function index()
    {
        $data = $this->loadModel('admin')->getList('*',array(),'id asc',true);
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
        $admin = $this->loadModel('admin')->getInfoByid($id);
        $this->assign('data',$admin);
        return $this->fetch('edit');
    }

    private function save($id = 0)
    {
        $_params = $this->get_params(array('username','password','re-password','role_id','status','submit'));
        $data = array();
        $this->vaild_params('is_empty',$_params['username'],'请填写账号名');
        $this->vaild_params('is_empty',$_params['password'],'请填写账号密码');
        $this->vaild_params('is_empty',$_params['re-password'],'请重复填写账号密码');
        if (!$id)
        {
            $this->vaild_params('eq',array($_params['password'],$_params['re-password']),'两次输入密码不一致，请重新输入');
            $data['password'] = md5($_params['password']);
        }
        else
        {
            if (!empty($_params['password']))
            {
                $this->vaild_params('eq',array($_params['password'],$_params['re-password']),'两次输入密码不一致，请重新输入');
                $data['password'] = md5($_params['password']);
            }
        }
        $data['username'] = trim($_params['username']);
        $data['role_id']	 = intval($_params['role_id']);
        if ($data['role_id']) $data['role_name'] = $this->role[$data['role_id']];
        $data['status'] = intval($_params['status']);
        if ($id)
        {
            $rs = $this->loadModel('Admin')->update($data,array('id'=>$id));
        }
        else
        {
            $rs = 	$this->loadModel('Admin')->save($data);
        }
        $this->ajaxOut($rs,'Admin/index');
    }

    /*
    *	权限设置
    */
    function privilege()
    {
        $username = input('username');
        $admin = $this->loadModel('Admin')->getInfo('*',array('username'=>$username));
        if (input('submit'))
        {
            $actions = input('post.actions/a');
            $actions = array('actions'=>$actions);
            $actions = strtolower(json_encode($actions));
            $data = array('actions'=>$actions);
            $rs = $this->loadModel('Admin')->update($data,array('id'=>$admin['id']));
            $this->ajaxOut($rs,'staff/index',true);
        }

        $admin_actions = json_decode($admin['actions'],true);
        $module = $this->setMenu();
        $this->assign('admin_actions',$admin_actions);
        $this->assign('username',$username);
        $this->assign('module',$module);
        return $this->fetch();
    }

    function delete()
    {
        $id = intval(input('id'));
        $this->vaild_params('compare',array($id,0),'请选择要删除的项！');
        $rs = $this->loadModel('Admin')->del(array('id'=>$id));
        $this->ajaxOut($rs,'admin/index');
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