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

class MyPricipalProjectController extends BaseController {


    /**
     * 我负责的项目节点
     * @return mixed
     */
    public function index(){

        $_params = $this->get_params(array('ptitle','title','audit_status','starttime','endtime','starttime1','endtime1'));
        if($_params['ptitle']){
            $where['project.title'] = array('like','%'.$_params['ptitle'].'%');
        }
        if($_params['title']){
            $where['project_nodes.title'] = array('like','%'.$_params['title'].'%');
        }
        if($_params['audit_status'] != ''){
            $where['project_nodes_pricipal.status'] = $_params['audit_status'];
        }
        if($_params['starttime']){
            $where['project_nodes.begintime'][] = array("egt",strtotime($_params['starttime']));
        }
        if($_params['endtime']){
            $where['project_nodes.begintime'][] = array("elt",strtotime($_params['endtime']));
        }
        if($_params['starttime1']){
            $where['project_nodes.endtime'][] = array("egt",strtotime($_params['starttime1']));
        }
        if($_params['endtime1']){
            $where['project_nodes.endtime'][] = array("elt",strtotime($_params['endtime1']));
        }
        $fields['project_nodes_pricipal'] = 'content,status as pricipal_status';
        $fields['project_nodes'] = '*';
        $fields['project'] = 'title as ptitle';
        $where['staff_id'] = session('admin')['staff_id'];
        $where['project.is_del'] = '0';
        $where['project_nodes.is_del'] = '0';
//        $where['project_nodes.status'] = array('in','0,1');
        $join[] = array('project_nodes','project_nodes_id','id');
        $join[] = array('project','project_id','id');
        $data = $this->loadModel("projectNodesPricipal")->getJoinList($fields,$join,$where,'id desc');

        $this->assign("data",$data);
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
    public function submitNodes(){

        $id = input("id");
        $fields['project_nodes'] = '*';
        $fields['project_nodes_pricipal'] = 'id,project_nodes_id,content';
        $where['staff_id'] = session('admin')['staff_id'];
        $where['project_nodes_id'] = $id;
        $join[] = array('project','project_id','id');
        $join[] = array('project_nodes','project_nodes_id','id');
        $data = $this->loadModel("projectNodesPricipal")->getJoinInfo($fields,$join,$where);
        if(input("submit")){
            $content = input("content");
            $datap['content'] = html_entity_decode($content);
            $rs = $this->loadModel('projectNodesPricipal')->approveNodes($datap,$data);
            $this->ajaxOut($rs,'myPricipalProject/index');
        }

        $this->assign("data",$data);
        return $this->fetch();
    }

}