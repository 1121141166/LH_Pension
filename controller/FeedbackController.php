<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/24
 * Time: 16:07
 */
namespace admin\index\controller;
use admin\index\controller\BaseController;
use think\Validate;

class FeedbackController extends BaseController {

    public function index(){

        $data = $this->loadModel('feedback')->getList('*',array("is_del"=>"0"),'id desc',true);
        $this->assign('data',$data);
        return $this->fetch();
    }

    public function add()
    {
        if (input('submit')) $this->save();
        $this->assign('data',null);
        return $this->fetch('edit');
    }

    public function edit()
    {
        if (input('submit')) $this->save();

        $id = intval(input('id'));

        $fields['feedback'] = '*';
        $fields['user_buyer'] = 'nickname as ub_name';
        $join[] = array("user_buyer","ubid","id");
        $data = $this->loadModel('feedback')->getJoinInfo($fields,$join,array("id"=>$id));

        $this->assign('data',$data);
        return $this->fetch();
    }

    public function save()
    {
        $_params = $this->get_params(array('id','name','status','type'));
        $this->vaild_params('is_empty',$_params['name'],'请填写酒店类型名称');

        $data = $_params;
        $id = intval($_params['id']);
        if ($id>0)
        {
            $rs = $this->loadModel('feedback')->update($data,array('id'=>$id));
        }
        else
        {
            $data['addtime'] = time();	//添加时间
            $rs = $this->loadModel('feedback')->add($data);
        }
        $this->ajaxOut($rs,'feedback/index');

    }

    public function delete()
    {
        $_params = $this->get_params(array('id'));
        $this->vaild_params('is_empty',$_params['id'],'请选择一个要删除的项目');

        $rs = $this->loadModel('feedback')->update(array('is_del'=>'1'),array("id"=>$_params['id']));
        $this->ajaxOut($rs,'feedback/index');
    }
}