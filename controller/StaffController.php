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

class StaffController extends BaseController {

    public function index(){
        $_params = $this->get_params(array('starttime','endtime','fullname','account','mobile','status'));
        $where = array();
        $where['is_del'] = '0';
        if ($_params['starttime']) $where['staff.addtime'][] = array('gt',strtotime($_params['starttime']));
        if ($_params['endtime']) $where['staff.addtime'][] = array('lt',strtotime($_params['endtime']));
        if ($_params['fullname']) $where['staff.fullname'] = array('like',"%".$_params['fullname']."%");
        if ($_params['account']) $where['staff.account'] = array('like',"%".$_params['account']."%");
        if ($_params['mobile']) $where['staff.mobile'] = array('like',"%".$_params['mobile']."%");
        if ($_params['status'] != '') $where['staff.status'] = $_params['status'];

        $fields['staff'] = '*';
        $fields['department'] = 'name as department_name';
        $fields['staff_type'] = 'name as staff_type_name';
        $join[] = array('department','department_id','id');
        $join[] = array('staff_type','staff_type_id','id');
        $data = $this->loadModel('staff')->getJoinList($fields,$join,$where,true);
        $this->assign('data',$data);
        $this->assign('_params',$_params);
        return $this->fetch();
    }

    public function add()
    {
        if (input('submit')) $this->save();
        $role = $this->loadModel("role")->getList("*",array());
        $this->assign('role',$role['list']);
        $this->assign('data',null);
        return $this->fetch('edit');
    }

    public function edit()
    {
        if (input('submit')) $this->save();
        $id = intval(input('id'));

        $fields['staff'] = '*';
        $fields['department'] = 'name as department_name';
        $fields['staff_type'] = 'name as staff_type_name';
        $fields['admin'] = 'role_id';
        $join[] = array('department','department_id','id');
        $join[] = array('staff_type','staff_type_id','id');
        $join[] = array('admin','id','staff_id');
        $data = $this->loadModel('staff')->getJoinInfo($fields,$join,array("id"=>$id));
        $role = $this->loadModel("role")->getList("*",array());
        $this->assign('role',$role['list']);
        $this->assign('data',$data);
        return $this->fetch();
    }

    public function save()
    {
        $_params = $this->get_params(array('id','fullname','mobile','account','headpic','department_id','staff_type_id','sex','birthday','status'));
        $result = $this->validate($_params,'staff.add');
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->response(PARAMS_ERROR,$result);
        }

        $data = $_params;

        $path = '/upload/staff/';
        if (!strstr($data['headpic'], $path)) {
            $new_path = $path.date('Ymd').'/'.create_uuid().'.png';
            rename(ROOT_PATH.$path.date('Ymd').'/'.$data['headpic'],ROOT_PATH.$new_path);
            $data['headpic'] = $new_path;
        }

        if ($_params['pid'])
        {
            $parent = $this->loadModel('staff')->getInfo('*',array('id'=>$_params['pid']));
            $this->vaild_params('is_empty',$parent,'您选择的上级部门不存在或已被删除！');
            $data['level'] = $parent['level']+1;
        }
        $id = intval($_params['id']);
        if ($id>0) {
            $fields['staff'] = '*';
            $fields['admin'] = 'role_id';
            $join[] = array('admin','id','staff_id');
            $staff = $this->loadModel('staff')->getJoinInfo($fields,$join,array("id"=>$id));
            $rs = $this->loadModel('staff')->update($data,array('id'=>$id));

            if($rs){
                $admin = $this->loadModel("admin")->getInfo("*",array('username'=>$data['account']));

                $role_id = input("role_id");
                if($staff['role_id'] != $role_id){
                    $role_id = input("role_id");
                    $role = $this->loadModel("role")->getInfo("*", array('id' => $role_id));
                    $data_admin['role_id'] = $role_id;
                    $data_admin['actions'] = $role['actions'];
                }
                if($admin){
                    $data_admin['staff_id'] = $id ? $id : $rs;
                    $data_admin['status'] = $data['status'];
                    $data_admin['username'] = $data['account'];
                    $this->loadModel("admin")->update($data_admin,array('id'=>$admin['id']));
                }else{
                    $data_admin['staff_id'] = $id ? $id : $rs;
                    $data_admin['username'] = $data['account'];
                    $data_admin['password'] = md5('888888');
                    $data_admin['status'] = $data['status'];
                    $this->loadModel("admin")->add($data_admin);
                }
            }
        } else {
            $staff = $this->loadModel('staff')->getInfo("*",array('account'=>$_params['account']));
            if($staff) {
                $this->response(PARAMS_ERROR,'该员工号已存在');
            }
            $data['addtime'] = time();	//添加时间
            $rs = $this->loadModel('staff')->add($data);

            if($rs) {
                $role_id = input("role_id");
                $role = $this->loadModel("role")->getInfo("*", array('id' => $role_id));
                $data_admin['role_id'] = $role_id;
                $data_admin['actions'] = $role['actions'];
                $data_admin['staff_id'] = $id ? $id : $rs;
                $data_admin['username'] = $data['account'];
                $data_admin['password'] = md5('888888');
                $data_admin['status'] = $data['status'];
                $this->loadModel("admin")->add($data_admin);
            }
        }

        $this->ajaxOut($rs,'staff/index');

    }

    public function delete()
    {
        $_params = $this->get_params(array('id'));
        $this->vaild_params('is_empty',$_params['id'],'请选择一个要删除的项目');

        $rs = $this->loadModel('staff')->update(array('is_del'=>'1'),array("id"=>$_params['id']));
        $this->ajaxOut($rs,'staff/index');
    }

    function searchDept(){
        $data = $this->loadModel('department')->getTreeWithCache();
        $this->assign('data',$data);
        return $this->fetch();
    }

    function searchType(){
        $data = $this->loadModel('staffType')->getTree();
        $this->assign('data',$data);
        return $this->fetch();
    }
}