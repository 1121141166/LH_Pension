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

class StaffTypeController extends BaseController {

    public function index(){

        $data = $this->loadModel('staffType')->getTree();
        $this->assign('data',$data);
        return $this->fetch();
    }

    public function add()
    {
        if (input('submit')) $this->save();
        $this->assign('data',null);
        $this->assign("redirecturl",input("redirecturl"));
        return $this->fetch('edit');
    }

    public function edit()
    {
        if (input('submit')) $this->save();
        $id = intval(input('id'));
        $where['a.id'] = $id;
        $data = db("staffType")->alias("a")->field('a.*,b.name as pname')->join('staff_type b','a.pid=b.id','left outer')->where($where)->find();
        $this->assign('data',$data);
        return $this->fetch();
    }

    public function save()
    {
        $_params = $this->get_params(array('id','name','pid','status','code','sort'));
        $result = $this->validate($_params,'staffType.add');
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->response(PARAMS_ERROR,$result);
        }

        $data = $_params;
        if ($_params['pid']) {
            $parent = $this->loadModel('staffType')->getInfo('*',array('id'=>$_params['pid']));
            $this->vaild_params('is_empty',$parent,'您选择的上级部门不存在或已被删除！');
            $data['level'] = $parent['level']+1;
        }
        $id = intval($_params['id']);
        if ($id>0) {
            if($id == $_params['pid']){
                $this->response(PARAMS_ERROR,'不能选择自身作为上级部门');
            }
            $rs = $this->loadModel('staffType')->update($data,array('id'=>$id));
        } else {
            $type = $this->loadModel('staffType')->getInfo('*',array('name'=>$_params['name']));
            if($type){
                $this->response(PARAMS_ERROR,'部门名称已存在');
            }
            $type1 = $this->loadModel('staffType')->getInfo('*',array('code'=>$_params['code']));
            if($type1){
                $this->response(PARAMS_ERROR,'部门编码已存在');
            }
            $data['addtime'] = time();	//添加时间
            $rs = $this->loadModel('staffType')->add($data);
        }
        $link = input("redirecturl",'staffType/index');
        $link = str_replace("|","/",$link);
        $this->ajaxOut($rs,$link);

    }

    public function delete()
    {
        $_params = $this->get_params(array('id'));
        $this->vaild_params('is_empty',$_params['id'],'请选择一个要删除的项目');

        $rs = $this->loadModel('staffType')->del(array("id"=>$_params['id']));
        $this->ajaxOut($rs,'staffType/index');
    }

    function searchType(){
        $data = $this->loadModel('staffType')->getTree();
        $this->assign('data',$data);
        return $this->fetch();
    }
}