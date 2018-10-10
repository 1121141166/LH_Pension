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

class DepartmentController extends BaseController {

    public function index(){

        $data = $this->loadModel('department')->getTreeWithCache();
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
        $data = db("department")->alias("a")->field('a.*,b.name as pname')->join('department b','a.pid=b.id')->where($where)->find();

        $this->assign('data',$data);
        return $this->fetch();
    }

    public function save()
    {
        $_params = $this->get_params(array('id','name','pid','status','code','sort'));
        $result = $this->validate($_params,'Department.add');
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->response(PARAMS_ERROR,$result);
        }

        $data = $_params;
        if ($_params['pid']) {
            $parent = $this->loadModel('Department')->getInfo('*',array('id'=>$_params['pid']));
            $this->vaild_params('is_empty',$parent,'您选择的上级部门不存在或已被删除！');
            $data['level'] = $parent['level']+1;

        }
        $id = intval($_params['id']);
        if ($id>0) {
            if($id == $_params['pid']){
                $this->response(PARAMS_ERROR,'不能选择自身作为上级部门');
            }
            $rs = $this->loadModel('department')->update($data,array('id'=>$id));
            if($rs) {
                //更新缓存
                $this->loadModel("department")->updateTreeCache($id, $data, 'update');
            }
        } else {

            $dept = $this->loadModel('Department')->getInfo('*',array('name'=>$_params['name']));
            if($dept){
                $this->response(PARAMS_ERROR,'部门名称已存在');
            }
            $dept1 = $this->loadModel('Department')->getInfo('*',array('code'=>$_params['code']));
            if($dept1){
                $this->response(PARAMS_ERROR,'部门编码已存在');
            }
            $data['addtime'] = time();	//添加时间
            $rs = $this->loadModel('department')->add($data);
            if($rs) {
                //更新缓存
                $this->loadModel("department")->setTreeCache();
            }
        }
        $link = input("redirecturl");
        if($link) {
            $link = str_replace("|","/",$link);
        }else{
            $link = 'department/index';
        }
        $this->ajaxOut($rs,$link);
        halt($data);

    }

    public function delete()
    {
        $id = intval(input("id"));
        $this->vaild_params('is_empty',$id,'请选择一个要删除的项目');

        $rs = $this->loadModel('department')->update(array('is_del'=>'1'),array("id"=>$id));
        if($rs) {
            //更新缓存
            $this->loadModel("department")->updateTreeCache($id, '', 'remove');
        }
        $this->ajaxOut($rs,'department/index');
    }

    function searchDept(){
        $data = $this->loadModel('department')->getTreeWithCache();
        $this->assign('data',$data);
        return $this->fetch();
    }
}