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

class MyAuditProjectController extends BaseController {


    /**
     * 我负责的项目节点
     * @return mixed
     */
    public function index(){
        $_params = $this->get_params(array('title','status'));
        $fields['project_audit'] = 'staff_id';
        $fields['project'] = '*';
        $where['staff_id'] = session('admin')['staff_id'];
        $where['project.is_del'] = '0';
        if($_params['title']){
            $where['project.title'] = array('like','%'.$_params['title'].'%');
        }
        if($_params['status'] != ''){
            $where['project.status'] = $_params['status'];
        }
        $join[] = array('project','project_id','id');
        $data = $this->loadModel("projectAudit")->getJoinList($fields,$join,$where,'id desc');
        foreach($data['list'] as $key => $value){
            $data['list'][$key]['rate'] = round($value['finish_nodes']/$value['nodes']*100);
        }
        $this->assign("data",$data);
        $this->assign("_params",$_params);
        return $this->fetch();
    }

    /**
     * 项目详情
     */
    public function details(){
        $id = input("id");
        $fields['project'] = '*';
        $fields['staff'] = 'fullname';
        $where['id'] = $id;
        $join[] = array('staff','initiator_id','id');
        $data = $this->loadModel("project")->getJoinInfo($fields,$join,$where);

        $nodes = $this->loadModel("projectNodes")->getList("*",array('project_id'=>$id),'id asc');
        foreach ($nodes['list'] as $k=>$v){
            $sql = "select a.*,b.fullname,b.account from (SELECT *,'2' as type FROM pm_project_nodes_audit WHERE project_nodes_id = ".$v['id']." and status = 2 union 
                   SELECT *,'1' as type FROM pm_project_nodes_pricipal WHERE project_nodes_id = ".$v['id']." and status = 2) a 
                   left outer join pm_staff b on a.staff_id = b.id order by a.submittime asc";

            $nodes['list'][$k]['list'] = db()->query($sql);
        }
        $this->assign("data",$data);
        $this->assign("nodes",$nodes['list']);
        return $this->fetch();
    }

    /**
     * 提交项目
     */
    public function submitProject(){
        $id = input("id");
        $fields['project_audit'] = 'staff_id';
        $fields['project'] = '*';
        $where['staff_id'] = session('admin')['staff_id'];
        $where['project_id'] = $id;
        $join[] = array('project','project_id','id');
        $data = $this->loadModel("projectAudit")->getJoinInfo($fields,$join,$where);

        if(input("submit")){
            if($data['status'] == '3'){
                $this->response(INTERNAL_ERROR,'该项目已审核，请勿重复审核');
            }
            $content = input("content");
            $datap['content'] = html_entity_decode($content);
            $datap['status'] = '3';
            $rs = $this->loadModel('project')->update($datap,array('id'=>$data['id']));
            $this->ajaxOut($rs,'myAuditProject/index');
        }

        $this->assign("data",$data);
        return $this->fetch();
    }

}