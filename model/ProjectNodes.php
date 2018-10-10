<?php
namespace admin\index\model;
use think\BaseModel;

class ProjectNodes extends BaseModel  {

    //自定义初始化
    protected function initialize($config='')
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
        $this->connection = $config;
        $this->table = \think\Config::get('database')['prefix'].'project_nodes';
    }

    public function saveNodes($project,$pricipal_id,$auditor_id){

        $this->startTrans();
        $pricipal = implode(",",$pricipal_id);
        $pricipallist = model("staff")->getList("*",array("id"=>array('in',$pricipal)));
        $pricipalname = '';
        foreach($pricipallist['list'] as $v){
            $pricipalname .= '['.$v['account'].']'.$v['fullname'].',';
        }
        $auditor = implode(",",$auditor_id);
        $auditorlist = model("staff")->getList("*",array("id"=>array('in',$auditor)));
        $auditorname = '';
        foreach($auditorlist['list'] as $v){
            $auditorname .= '['.$v['account'].']'.$v['fullname'].',';
        }
        $project['pricipal_staff'] = trim($pricipalname,',');
        $project['auditor_staff'] = trim($auditorname,',');
        $rs = $this->add($project);
        if (!$rs) {
            $this->rollback();
            return false;
        }

        $project_id = $rs;

        $pricipalarr = array();
        foreach ($pricipal_id as $v){
            $arr = array();
            $arr['project_id'] = $project['project_id'];
            $arr['project_nodes_id'] = $project_id;
            $arr['staff_id'] = $v;
            $pricipalarr[] = $arr;
        }

        $auditarr = array();
        foreach ($auditor_id as $v){
            $arr = array();
            $arr['project_id'] = $project['project_id'];
            $arr['project_nodes_id'] = $project_id;
            $arr['staff_id'] = $v;
            $auditarr[] = $arr;
        }

        if($pricipalarr){
            $rs = model("ProjectNodesPricipal")->saveAll($pricipalarr);
            if (!$rs) {
                $this->rollback();
                return false;
            }
        }

        if($auditarr){
            $rs = model("ProjectNodesAudit")->saveAll($auditarr);
            if (!$rs) {
                $this->rollback();
                return false;
            }
        }

        $this->commit();
        return true;
    }

    public function updateNodes($project,$id,$pricipal_id,$auditor_id){

        $this->startTrans();
        $pricipal = implode(",",$pricipal_id);
        $pricipallist = model("staff")->getList("*",array("id"=>array('in',$pricipal)));
        $pricipalname = '';
        foreach($pricipallist['list'] as $v){
            $pricipalname .= '['.$v['account'].']'.$v['fullname'].',';
        }
        $auditor = implode(",",$auditor_id);
        $auditorlist = model("staff")->getList("*",array("id"=>array('in',$auditor)));
        $auditorname = '';
        foreach($auditorlist['list'] as $v){
            $auditorname .= '['.$v['account'].']'.$v['fullname'].',';
        }
        $project['pricipal_staff'] = trim($pricipalname,',');
        $project['auditor_staff'] = trim($auditorname,',');
        $rs = $this->update($project,array("id"=>$id));
        if (!$rs) {
            $this->rollback();
            return false;
        }

        $rs = model('ProjectNodesAudit')->del(array('project_nodes_id'=>$id));
        if($rs === ''){
            $this->rollback();
            return false;
        }

        $rs = model('ProjectNodesPricipal')->del(array('project_nodes_id'=>$id));
        if($rs === ''){
            $this->rollback();
            return false;
        }

        $project_nodes_id = $id;

        $pricipalarr = array();
        foreach ($pricipal_id as $v){
            $arr = array();
            $arr['project_id'] = $project['project_id'];
            $arr['project_nodes_id'] = $project_nodes_id;
            $arr['staff_id'] = $v;
            $pricipalarr[] = $arr;
        }

        $auditarr = array();
        foreach ($auditor_id as $v){
            $arr = array();
            $arr['project_id'] = $project['project_id'];
            $arr['project_nodes_id'] = $project_nodes_id;
            $arr['staff_id'] = $v;
            $auditarr[] = $arr;
        }

        if($pricipalarr){
            $rs = model("ProjectNodesPricipal")->saveAll($pricipalarr);
            if (!$rs) {
                $this->rollback();
                return false;
            }
        }

        if($auditarr){
            $rs = model("ProjectNodesAudit")->saveAll($auditarr);
            if (!$rs) {
                $this->rollback();
                return false;
            }
        }

        $this->commit();
        return true;
    }
}