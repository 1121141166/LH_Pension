<?php
namespace admin\index\model;
use think\BaseModel;

class ProjectNodesAudit extends BaseModel  {

    //自定义初始化
    protected function initialize($config='')
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
        $this->connection = $config;
        $this->table = \think\Config::get('database')['prefix'].'project_nodes_audit';
    }

    public function approveNodes($datap,$nodes){

        $this->startTrans();
        $datap['status'] = 2;
        $datap['submittime'] = time();
        $rs = $this->update($datap,array("id"=>$nodes['id']));
        if (!$rs) {
            $this->rollback();
            return false;
        }

        if($nodes['audit_type'] == '1'){
            //提交方式为'或'

            //修改节点状态为已审核
            $data = array();
            $data['status'] = '3';
            $data['audittime'] = time();
            $rs = model("projectNodes")->update($data,array('id'=>$nodes['project_nodes_id']));
            if (!$rs) {
                $this->rollback();
                return false;
            }

            $project = model("project")->getInfo('*',array('id'=>$nodes['project_id']));
            if(!$project){
                $this->rollback();
                return false;
            }

            $data = array();
            $data['finish_nodes'] = $project['finish_nodes'] +1;
            $rs = model("project")->update($data,array('id'=>$nodes['project_id']));
            if (!$rs) {
                $this->rollback();
                return false;
            }

            //判断其他节点是否都已审核，都已审核则修改项目状态为待提交
            $rs = model("projectNodes")->getInfo("*",array('project_id'=>$nodes['project_id'],'status'=>array("in",'0,1,2')));
            if(!$rs){
                $data = array();
                $data['status'] = '2';
                $data['approvetime'] = time();
                $rs = model("project")->update($data,array('id'=>$nodes['project_id']));
                if (!$rs) {
                    $this->rollback();
                    return false;
                }
            }
        } else if($nodes['audit_type'] == '2') {
            $rs = $this->getInfo("*",array('project_nodes_id'=>$nodes['project_nodes_id'],'status'=>array("in",'0,1')));
            if(!$rs){
                //判断其余是否都已提交，是则修改状态
                $data = array();
                $data['status'] = '3';
                $data['audittime'] = time();
                $rs = model("projectNodes")->update($data,array('id'=>$nodes['project_nodes_id']));
                if (!$rs) {
                    $this->rollback();
                    return false;
                }

                $project = model("project")->getInfo('*',array('id'=>$nodes['project_id']));
                if(!$project){
                    $this->rollback();
                    return false;
                }

                $data = array();
                $data['finish_nodes'] = $project['finish_nodes'] +1;
                $rs = model("project")->update($data,array('id'=>$nodes['project_id']));
                if (!$rs) {
                    $this->rollback();
                    return false;
                }

                //判断其他节点是否都已审核，都已审核则修改项目状态为待提交
                $rs = model("projectNodes")->getInfo("*",array('project_id'=>$nodes['project_id'],'status'=>array("in",'0,1,2')));
                if(!$rs){
                    $data = array();
                    $data['status'] = '2';
                    $data['approvetime'] = time();
                    $rs = model("project")->update($data,array('id'=>$nodes['project_id']));
                    if (!$rs) {
                        $this->rollback();
                        return false;
                    }
                }
            }
        }

        $this->commit();
        return true;
    }
}