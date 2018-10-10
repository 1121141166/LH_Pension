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

class ProjectController extends BaseController {

    //发布项目
    public function publish(){

        if (input('submit')) $this->save();
        return $this->fetch();
    }

    //发布项目
    public function editProject(){
        if (input('submit')) $this->save();

        $id = intval(input('id'));

        $where['initiator_id'] = session('admin')['staff_id'];
        $where['id'] = $id;
        $data = $this->loadModel("project")->getInfo('*',$where);
        $data['begintime'] = date("Y-m-d H:i:s",$data['begintime']);
        $data['endtime'] = date("Y-m-d H:i:s",$data['endtime']);

        $fields = $join = array();
        $fields['project_audit'] = '*';
        $fields['staff'] = 'fullname';
        $join[] = array('staff','staff_id','id');
        $audit = $this->loadModel("projectAudit")->getJoinList($fields,$join,array("project_id"=>$data['id']));

        $fields = $join = array();
        $fields['project_follow'] = '*';
        $fields['staff'] = 'fullname';
        $join[] = array('staff','staff_id','id');
        $follow = $this->loadModel("projectFollow")->getJoinList($fields,$join,array("project_id"=>$data['id']));

        $data['auditlist'] = $audit['list'];
        $data['followlist'] = $follow['list'];
        $this->assign('data',$data);
        return $this->fetch();
    }

    //我发布的项目
    public function myPublishProject(){

        $_params = $this->get_params(array('title','status'));
        $where['initiator_id'] = session('admin')['staff_id'];
        $where['is_del'] = '0';
        if($_params['title']){
            $where['title'] = array('like','%'.$_params['title'].'%');
        }
        if($_params['status'] != ''){
            $where['status'] = $_params['status'];
        }

        $join[] = array('');
        $data = $this->loadModel("project")->getList('*',$where,'priority asc,id desc',true);
        foreach($data['list'] as $key => $value){
            $data['list'][$key]['rate'] = round($value['finish_nodes']/$value['nodes']*100);
        }
        $this->assign("data",$data);
        $this->assign("_params",$_params);
        return $this->fetch();
    }

    //我已完成的项目
    public function myFinishProject(){

        $where['initiator_id'] = session('admin')['staff_id'];
        $where['status'] = '3';
        $data = $this->loadModel("project")->getList('*',$where,'priority asc,id desc',true);
        $this->assign("data",$data);
        return $this->fetch();
    }

    /**
     * 我负责的项目节点
     * @return mixed
     */
    public function myPricipalProject(){

        $fields['project_nodes_pricipal'] = 'content';
        $fields['project_nodes'] = '*';
        $where['staff_id'] = session('admin')['staff_id'];

        $join[] = array('project_nodes','project_nodes_id','id');
        $data = $this->loadModel("projectNodesPricipal")->getJoinList($fields,$join,$where,'id desc',true);
        $this->assign("data",$data);
        return $this->fetch();
    }

    /**
     * 我审核的项目
     * @return mixed
     */
    public function myAuditProject(){

        $fields['project_nodes_audit'] = 'content';
        $fields['project_nodes'] = '*';
        $where['staff_id'] = session('admin')['staff_id'];

        $join[] = array('project_nodes','project_nodes_id','id');
        $data = $this->loadModel("projectNodesAudit")->getJoinList($fields,$join,$where,'id desc',true);
        $this->assign("data",$data);
        return $this->fetch();
    }

    /**
     * 我关注的项目
     * @return mixed
     */
    public function myFollowProject(){

        $fields['project'] = '*';
        $where['staff_id'] = session('admin')['staff_id'];

        $join[] = array('project','project_id','id');
        $data = $this->loadModel("projectFollow")->getJoinList($fields,$join,$where,'id desc',true);
        $this->assign("data",$data);
        return $this->fetch();
    }

    public function projectNodes() {

        $id = intval(input("id"));
//        $fields['project'] = '*';
//        $fields['staff'] = 'fullname as final_auditor_name';
//        $where['initiator_id'] = session('admin')['staff_id'];
//        $join[] = array('staff','final_auditor_id','id');
        $where['project_id'] = $id;
        $where['is_del'] = '0';
        $data = $this->loadModel("projectNodes")->getList('*',$where,'begintime asc,id asc');
        $this->assign("data",$data);
        $this->assign("project_id",$id);
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

    //发布项目
    public function addNodes(){

        if (input('submit')) $this->saveNodes();
        $this->assign("project_id",input("project_id"));
        return $this->fetch();
    }

    //发布项目
    public function editNodes(){
        if (input('submit')) $this->saveNodes();

        $id = intval(input('id'));

        $where['id'] = $id;
        $data = $this->loadModel("projectNodes")->getInfo('*',$where);
        $data['begintime'] = date("Y-m-d H:i:s",$data['begintime']);
        $data['endtime'] = date("Y-m-d H:i:s",$data['endtime']);

        $fields = $join = array();
        $fields['project_nodes_audit'] = '*';
        $fields['staff'] = 'fullname';
        $join[] = array('staff','staff_id','id');
        $auditorlist = $this->loadModel("projectNodesAudit")->getJoinList($fields,$join,array("project_nodes_id"=>$data['id']));

        $fields = $join = array();
        $fields['project_nodes_pricipal'] = '*';
        $fields['staff'] = 'fullname';
        $join[] = array('staff','staff_id','id');
        $pricipallist = $this->loadModel("projectNodesPricipal")->getJoinList($fields,$join,array("project_nodes_id"=>$data['id']));

        $data['auditorlist'] = $auditorlist['list'];
        $data['pricipallist'] = $pricipallist['list'];
        $this->assign('data',$data);
        return $this->fetch();
    }

    /**
     * 保存项目
     */
    public function save(){
        $_params = $this->get_params(array('id','title','introduction','image','priority','audit_type','begintime','endtime'));
        $result = $this->validate($_params,'Project.add');
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->response(PARAMS_ERROR,$result);
        }

        $final_auditor_id = input("final_auditor_id/a");
        $follower_id = input("follower_id/a");
        if(!$final_auditor_id){
            $this->response(PARAMS_ERROR,"请添加最终审核人");
        }
        if(!$follower_id){
            $this->response(PARAMS_ERROR,"请至少添加一位关注人");
        }

        $data = $_params;

        $path = '/upload/project/';
        if (!strstr($data['image'], $path)) {
            $new_path = $path.date('Ymd').'/'.create_uuid().'.png';
            rename(ROOT_PATH.$path.date('Ymd').'/'.$data['image'],ROOT_PATH.$new_path);
            $data['image'] = $new_path;
        }

        $data['introduction'] = html_entity_decode($_params['introduction']);
        $data['begintime'] = strtotime($_params['begintime']);
        $data['endtime'] = strtotime($_params['endtime']);
//        $data['final_auditor_id'] = implode(",",$final_auditor_id);
//        $data['follower_id'] = implode(",",$follower_id);
        $id = intval($_params['id']);
        if ($id>0) {
            $rs = $this->loadModel('Project')->updateProject($data,$id,$final_auditor_id,$follower_id);
        } else {
            $data['initiator_id'] = session('admin')['staff_id'];
            $data['addtime'] = time();	//添加时间
            $rs = $this->loadModel('Project')->saveProject($data,$final_auditor_id,$follower_id);
        }

        $this->ajaxOut($rs,'project/mypublishproject');
    }

    public function saveNodes(){
        $_params = $this->get_params(array('id','project_id','title','introduction','status','approve_type','audit_type','begintime','endtime'));
        $result = $this->validate($_params,'ProjectNodes.add');
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->response(PARAMS_ERROR,$result);
        }

        $pricipal_id = input("pricipal_id/a");
        $auditor_id = input("auditor_id/a");
        if(!$pricipal_id){
            $this->response(PARAMS_ERROR,"请至少添加一位负责人");
        }
        if(!$auditor_id){
            $this->response(PARAMS_ERROR,"请至少添加一位审核人");
        }

        $project = $this->loadModel("project")->getInfo("*",array("id"=>$_params['project_id']));
        if(!$project){
            $this->response(PARAMS_ERROR,"项目不存在");
        }

        $data = $_params;
        $data['introduction'] = html_entity_decode($_params['introduction']);
        $data['begintime'] = strtotime($_params['begintime']);
        $data['endtime'] = strtotime($_params['endtime']);
//        $data['pricipal_id'] = implode(",",$pricipal_id);
//        $data['auditor_id'] = implode(",",$auditor_id);
        $id = intval($_params['id']);
        if ($id>0) {
            $rs = $this->loadModel('ProjectNodes')->updateNodes($data,$id,$pricipal_id,$auditor_id);
        } else {
            $data['addtime'] = time();	//添加时间
            $rs = $this->loadModel('ProjectNodes')->saveNodes($data,$pricipal_id,$auditor_id);

            $datap['nodes'] = $project['nodes'] + 1;
            $this->loadModel("project")->update($datap,array("id"=>$_params['project_id']));
        }

        $this->ajaxOut($rs,'project/projectNodes',true,['id'=>$_params['project_id']]);
    }

    public function searchFinalStaff(){

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

    public function searchFollowStaff(){

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

    public function searchPricipalStaff(){

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

    public function searchAuditStaff(){

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

    public function deletePro()
    {
        $id = intval(input("id"));
        $this->vaild_params('is_empty',$id,'请选择一个要删除的项目');

        $rs = $this->loadModel('project')->update(array('is_del'=>'1'),array("id"=>$id));

        $this->ajaxOut($rs,'project/mypublishproject');
    }

    public function deleteNodes()
    {
        $id = intval(input("id"));
        $this->vaild_params('is_empty',$id,'请选择一个要删除的节点');

        $rs = $this->loadModel('projectNodes')->update(array('is_del'=>'1'),array("id"=>$id));

        $this->ajaxOut($rs,'project/projectNodes');
    }
}