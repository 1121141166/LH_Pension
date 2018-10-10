<?php
namespace admin\index\controller;

use admin\index\model\User;
use think\Controller;
use think\db;
use think\exception\ErrorException;
use think\view;
use think\Request;
use admin\index\controller\BaseController;

class IndexController extends BaseController
{

    //控制器初始化
    public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {

        $mid = input('mid',1);
        $menu = $this->setMenu2($mid);

        $this->assign('menu',$menu);
        return $this->fetch();
    }

    //菜单列表
    public function setMenu($pid = '0')
    {
        $menu = model('Module')->getList('*',array('pid'=>$pid,'is_menu'=>'1'),'sort asc');
        $menu = $menu['list'];

        foreach ($menu as $key=>$value){
            $menu[$key]['url'] = url($value['module'].'/'.$value['action'],$value['params']);
            $menu[$key]['list'] = $this->setMenu($value['id']);
        }
        return $menu;
    }

    //菜单列表
    public function setMenu2($pid = '0')
    {
        $menu = $this->loadModel('module')->getList('*',array('pid'=>$pid,'is_menu'=>'1'),'sort asc,id asc');
        $menu = $menu['list'];
        $actions = session('actions');
        foreach ($menu as $key=>$value){
            $url = url($value['module'].'/'.$value['action'],$value['params']);
            $menu[$key]['url'] = $url;
            $menu[$key]['list'] = $this->setMenu2($value['id']);
            if($value['module'] && $value['action'] && $actions != "all"){
                $url_new = strtolower($value['module'].'/'.$value['action']);
                $actions_new = array_values($actions['actions']);
                if(!in_array($url_new,$actions_new)) {
                    unset($menu[$key]);
                }
            }
        }
        return $menu;
    }

    public function _empty($method){
        $this->assign('method',$method);
        return $this->fetch('404');
    }

    //首页数据统计
    function total_statistics(){

        $fields['project_nodes_pricipal'] = 'content,status as pricipal_status';
        $fields['project_nodes'] = '*';
        $where['staff_id'] = session('admin')['staff_id'];
        $where['project_nodes.status'] = array('in','0,1');
        $join[] = array('project_nodes','project_nodes_id','id');
        $pricipal = $this->loadModel("projectNodesPricipal")->getJoinList($fields,$join,$where,'id desc');

        $this->assign("pricipal",$pricipal['list']);

        $fields = $where = $join = array();
        $fields['project_nodes_audit'] = 'content,status as audit_status';
        $fields['project_nodes'] = '*';
        $where['staff_id'] = session('admin')['staff_id'];
        $where['project_nodes.status'] = array('in','0,1,2');
        $join[] = array('project_nodes','project_nodes_id','id');
        $audit = $this->loadModel("projectNodesAudit")->getJoinList($fields,$join,$where,'id desc',true);
        $this->assign("audit",$audit['list']);

        return $this->fetch();
    }

    //修改密码
    public function updatePwd()
    {
        if (input('submit')) $this->savepwd();

        $id = session('admin.id');
        $user = $this->loadModel('admin')->getInfoByid($id);

        $this->assign('user',$user);
        return $this->fetch();
    }

    public function savepwd(){
        $_params = $this->get_params(array('oldpwd','newpwd','newpwd_2'));

        $this->vaild_params('is_empty',$_params['oldpwd'],'请输入旧密码');
        $this->vaild_params('is_empty',$_params['newpwd'],'请输入新密码');
        $this->vaild_params('is_empty',$_params['newpwd_2'],'请输入确认新密码');

        $id = session('user.id');
        $user = $this->loadModel('admin')->getInfoByid($id);
        if($user['password'] != md5($_params['oldpwd'])){
            $this->response(PARAMS_ERROR,'旧密码错误');
        }
        if($_params['newpwd'] != $_params['newpwd_2']){
            $this->response(PARAMS_ERROR,'两次密码不正确，请重新输入');
        }

        $data['password'] = md5($_params['newpwd']);
        $rs = $this->loadModel('admin')->update($data,array('id'=>session('admin')['id']));
        $this->ajaxOut($rs,'updatePwd');
    }
}
