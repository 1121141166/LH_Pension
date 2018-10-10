<?php
namespace admin\index\model;
use think\BaseModel;

class ProjectNodesPricipal extends BaseModel  {

    //自定义初始化
    protected function initialize($config='')
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
        $this->connection = $config;
        $this->table = \think\Config::get('database')['prefix'].'project_nodes_pricipal';
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

        if($nodes['approve_type'] == '2'){
            //提交方式为'与'
            $rs = $this->getInfo("*",array('project_nodes_id'=>$nodes['project_nodes_id'],'status'=>array('in','0,1')));
            if(!$rs){
                //判断其余是否都已提交，是则修改状态

                //修改审核单为待审核状态
                $data = array();
                $data['status'] = '1';
                $rs = model("ProjectNodesAudit")->update($data, array("project_nodes_id" => $nodes['project_nodes_id']));
                if (!$rs) {
                    $this->rollback();
                    return false;
                }

                //修改节点为待审核状态
                $data = array();
                $data['status'] = '2';
                $data['approvetime'] = time();
                $rs = model("ProjectNodes")->update($data, array("id" => $nodes['project_nodes_id']));
                if (!$rs) {
                    $this->rollback();
                    return false;
                }
            }
        } else if($nodes['approve_type'] == '1') {
            //提交方式为'或'则修改状态

            //修改审核单为待审核状态
            $data = array();
            $data['status'] = '1';
            $rs = model("ProjectNodesAudit")->update($data, array("project_nodes_id" => $nodes['project_nodes_id']));
            if (!$rs) {
                $this->rollback();
                return false;
            }

            //修改节点为待审核状态
            $data = array();
            $data['status'] = '2';
            $data['approvetime'] = time();
            $rs = model("ProjectNodes")->update($data, array("id" => $nodes['project_nodes_id']));
            if (!$rs) {
                $this->rollback();
                return false;
            }
        }

        $this->commit();
        return true;
    }

}