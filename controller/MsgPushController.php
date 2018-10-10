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

class MsgPushController extends BaseController {

    public function index(){

        $data = $this->loadModel('MsgPush')->getList('*','','id desc',true);
        $this->assign('data',$data);
        return $this->fetch();
    }

    //添加用户
    public function add()
    {
        if (input('submit')) $this->save();
        $this->assign('data',null);
        return $this->fetch('edit');
    }

    //编辑用户信息
    public function edit()
    {
        if (input('submit')) $this->save();

        $id = intval(input('id'));
        $paychannel = $this->loadModel('MsgPush')->getInfoByid($id);

        $this->assign('data',$paychannel);
        return $this->fetch();
    }

    //编辑用户信息
    public function info()
    {

        $id = intval(input('id'));
        $paychannel = $this->loadModel('MsgPush')->getInfoByid($id);

        $this->assign('data',$paychannel);
        return $this->fetch();
    }

    /*
	*	保存用户信息
	*	@todo 1、图片上传部分 2、上级商户
	*/
    public function save()
    {
        $_params = $this->get_params(array('id','title','status'));

        $data = $_params;

        $id = intval($_params['id']);
        if ($id>0)
        {
            $rs = $this->loadModel('MsgPush')->update($data,array('id'=>$id));
        }
        else
        {
            $data['addtime'] = time();	//添加时间
            $rs = $this->loadModel('MsgPush')->save($data);

        }
        $this->ajaxOut($rs,'MsgPush/index');

    }

    public function delete()
    {
        $params = $this->get_params(array('id'));

        $rs = $this->loadModel('MsgPush')->del(array('id'=>$params['id']));
        $this->ajaxOut($rs,'MsgPush/index');
    }
}