<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/24
 * Time: 16:07
 */
namespace admin\index\controller;
use admin\index\controller\BaseController;

class ModuleController extends BaseController {

    public function index(){

        $channel = $this->loadModel('Module')->getTree();
        $this->assign('channel',$channel);
        return $this->fetch();
    }

    //添加用户
    public function add()
    {
        if (input('submit')) $this->save();

        $module = $this->loadModel('Module')->getTree(0);
        $this->assign('module',$module);
        $this->assign('data',null);
        return $this->fetch('edit');
    }

    //编辑功能信息
    public function edit()
    {
        if (input('submit')) $this->save();

        $id = intval(input('id'));
        $Module = $this->loadModel('Module')->getInfoByid($id);
        $module = $this->loadModel('Module')->getTree(0);
        $this->assign('module',$module);
        $this->assign('data',$Module);
        return $this->fetch();
    }

    /*
	*	保存功能信息
	*	@todo 1、图片上传部分 2、上级商户
	*/
    public function save()
    {
        $id = intval(input('id'));
        $pid_level = input('pid_level');
        $_params = $this->get_params(array('name','module','action','params','is_menu','sort'));
        $this->vaild_params('is_empty',$_params['name'],'请填写名称！');
        // $this->vaild_params('is_empty',$_params['module'],'请填写模块名！');
        // $this->vaild_params('is_empty',$_params['action'],'请填写操作！');

        $temp = explode(',',$pid_level);
        $data = $_params;
        $data['pid'] = $temp[0];
        $data['level'] = $temp[1];
        $data['addtime'] = time();
        $rs = !$id?$this->loadModel('Module')->save($data):$this->loadModel('Module')->update($data,array('id'=>$id));

        $this->ajaxOut($rs,'Module/index');

    }

    public function delete()
    {
        $id = intval(input('id'));
        $this->vaild_params('compare',array($id,0),'请选择要删除的项！');

        $rs = $this->loadModel('Module')->del(array('id'=>$id));
        $this->ajaxOut($rs,'Module/index');
    }
}