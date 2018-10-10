<?php
namespace admin\index\model;
use think\BaseModel;

class Project extends BaseModel  {

    //自定义初始化
    protected function initialize($config='')
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
        $this->connection = $config;
        $this->table = \think\Config::get('database')['prefix'].'project';
    }

    public function saveProject($project,$final_auditor_id,$follower_id){

        $this->startTrans();
        $final_auditor = implode(",",$final_auditor_id);
        $finallist = model("staff")->getList("*",array("id"=>array('in',$final_auditor)));
        $finalname = '';
        foreach($finallist['list'] as $v){
            $finalname .= '['.$v['account'].']'.$v['fullname'].',';
        }
        $follower = implode(",",$follower_id);
        $followlist = model("staff")->getList("*",array("id"=>array('in',$follower)));
        $followname = '';
        foreach($followlist['list'] as $v){
            $followname .= '['.$v['account'].']'.$v['fullname'].',';
        }
        $project['final_auditor_staff'] = trim($finalname,',');
        $project['follower_staff'] = trim($followname,',');
        $rs = $this->add($project);
        if (!$rs) {
            $this->rollback();
            return false;
        }

        $project_id = $rs;

        $finalarr = array();
        foreach ($final_auditor_id as $v){
            $arr = array();
            $arr['project_id'] = $project_id;
            $arr['staff_id'] = $v;
            $finalarr[] = $arr;
        }

        $followarr = array();
        foreach ($follower_id as $v){
            $arr = array();
            $arr['project_id'] = $project_id;
            $arr['staff_id'] = $v;
            $followarr[] = $arr;
        }

        if($finalarr){
            $rs = model("ProjectAudit")->saveAll($finalarr);
            if (!$rs) {
                $this->rollback();
                return false;
            }
        }

        if($followarr){
            $rs = model("ProjectFollow")->saveAll($followarr);
            if (!$rs) {
                $this->rollback();
                return false;
            }
        }

        $this->commit();
        return true;
    }

    public function updateProject($project,$id,$final_auditor_id,$follower_id){

        $this->startTrans();
        $final_auditor = implode(",",$final_auditor_id);
        $finallist = model("staff")->getList("*",array("id"=>array('in',$final_auditor)));
        $finalname = '';
        foreach($finallist['list'] as $v){
            $finalname .= '['.$v['account'].']'.$v['fullname'].',';
        }
        $follower = implode(",",$follower_id);
        $followlist = model("staff")->getList("*",array("id"=>array('in',$follower)));
        $followname = '';
        foreach($followlist['list'] as $v){
            $followname .= '['.$v['account'].']'.$v['fullname'].',';
        }
        $project['final_auditor_staff'] = trim($finalname,',');
        $project['follower_staff'] = trim($followname,',');
        $rs = $this->update($project,array("id"=>$id));
        if (!$rs) {
            $this->rollback();
            return false;
        }

        $rs = model('ProjectAudit')->del(array('project_id'=>$id));
        if($rs === ''){
            $this->rollback();
            return false;
        }

        $rs = model('ProjectFollow')->del(array('project_id'=>$id));
        if($rs === ''){
            $this->rollback();
            return false;
        }

        $project_id = $id;

        $finalarr = array();
        foreach ($final_auditor_id as $v){
            $arr = array();
            $arr['project_id'] = $project_id;
            $arr['staff_id'] = $v;
            $finalarr[] = $arr;
        }

        $followarr = array();
        foreach ($follower_id as $v){
            $arr = array();
            $arr['project_id'] = $project_id;
            $arr['staff_id'] = $v;
            $followarr[] = $arr;
        }

        if($finalarr){
            $rs = model("ProjectAudit")->saveAll($finalarr);
            if (!$rs) {
                $this->rollback();
                return false;
            }
        }

        if($followarr){
            $rs = model("ProjectFollow")->saveAll($followarr);
            if (!$rs) {
                $this->rollback();
                return false;
            }
        }

        $this->commit();
        return true;
    }
}