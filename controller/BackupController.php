<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/24
 * Time: 16:07
 */
namespace admin\index\controller;
use admin\index\controller\BaseController;
use Sqlbackup\Sqlbackup;

class BackupController extends BaseController {

    public function tablelist() {

        $tables = db()->query("show table status");
        $this->assign("tables",$tables);
        return $this->fetch();
    }

    //数据库表备份操作
    function tables_back()
    {

        $tables = input('post.tables/a');

        if($tables){
            $backup = new Sqlbackup();
            $file_name = date('YmdH').'_'.rand(1000,9999).'_'.rand(1000,9999).'.sql';
            $backup->back($tables,$file_name);
            $rs = $backup->back($tables,$file_name);
            $this->ajaxOut($rs,'Backup/backlist');
        }else{
            $this->response(INTERNAL_ERROR,'请选择要备份的数据表');
        }

    }
    //备份列表页
    function backlist()
    {
        $database_path = config("SQLBACKUP");
        $files = glob($database_path . '*.sql');
        $list = array();
        foreach ($files as $v){
            $arr['file'] = $v;
            $arr['name'] = basename($v);
            $list[] = $arr;
        }
        $this->assign('files',$list);
        return $this->fetch();
    }

    //备份下载操作
    function down()
    {
        $database_path = config("SQLBACKUP");
        $backs = input("back");
        $filename = $database_path.$backs;
        if(file_exists($filename)&&is_file($filename))
        {
            $length = filesize($filename);
        }
        else{
            die('下载文件不存在！');
        }

        $type = mime_content_type($filename);

        //发送Http Header信息 开始下载
        header("Pragma: public");
        header("Cache-control: max-age=1800");
        //header('Cache-Control: no-store, no-cache, must-revalidate');
        header("Expires: " . gmdate("D, d M Y H:i:s",time()+1800) . "GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s",time()) . "GMT");
        header("Content-Disposition: attachment; filename=".$backs);
        header("Content-Length: ".$length);
        header("Content-type: ".$type);
        header('Content-Encoding: none');
        header("Content-Transfer-Encoding: binary" );
        readfile($filename);
    }

    // 备份删除操作
    function back_del()
    {
        $database_path = config("SQLBACKUP");;
        $backs = input("back");
        if(!empty($backs)){
            if(is_array($backs))
            {
                foreach ($backs as $back) {
                    unlink($database_path.$back);
                }
            }
            else unlink($database_path.$backs);
            $this->ajaxOut(1,'Backup/backlist');
        }else{
            $this->response(INTERNAL_ERROR,'请选择要删除的文件');
        }
    }

    //备份恢复
    function back_recover()
    {
        $msg = array('error','恢复失败!');
        $back = input("back");
        $database_path = config("SQLBACKUP");;
        if(is_file($database_path.$back)){
            $backup = new Sqlbackup();
            $sqls = $backup->parseSql($database_path.$back);
            $rs = $backup->install($sqls);
            $this->ajaxOut($rs,'Backup/backlist');
        }else{
            $this->response(INTERNAL_ERROR,'备份文件不存在');
        }

    }
}