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

class BlogController extends BaseController {

    public function index(){
        $data = $this->loadModel('Blog')->getList('*','','id desc',true);
        $this->assign('data',$data);
        return $this->fetch();
    }

    //添加用户
    public function add() {
        if (input('submit')) $this->save();
        $this->assign('data',null);
        return $this->fetch('edit');
    }

    //编辑用户信息
    public function edit() {
        if (input('submit')) $this->save();

        $id = intval(input('id'));
        $paychannel = $this->loadModel('Blog')->getInfo('*',array('id'=>$id));

        $this->assign('data',$paychannel);
        return $this->fetch();
    }

    //编辑用户信息
    public function info(){

        $id = intval(input('id'));
        $paychannel = $this->loadModel('Blog')->getInfo('*',array('id'=>$id));

        $this->assign('data',$paychannel);
        return $this->fetch();
    }

    /*
	*	保存用户信息
	*	@todo 1、图片上传部分 2、上级商户
	*/
    public function save() {
        $_params = $this->get_params(array('id','title','short_desc','image','content','status'));
        $result = $this->validate($_params,'Blog.add');
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->response(PARAMS_ERROR,$result);
        }
        $data = $_params;
        $data['content'] = html_entity_decode($_params['content']);
        $path = '/upload/blog/';
        if (!strstr($data['image'], $path)) {
            $new_path = $path.date('Ymd').'/'.create_uuid().'.png';
            rename(ROOT_PATH.$path.date('Ymd').'/'.$data['image'],ROOT_PATH.$new_path);
            $data['image'] = $new_path;
        }
        $id = intval($_params['id']);
        if ($id>0) {
            $rs = $this->loadModel('Blog')->update($data,array('id'=>$id));
        } else {
            $data['addtime'] = time();	//添加时间
            $rs = $this->loadModel('Blog')->save($data);
        }
        $this->ajaxOut($rs,'Blog/index');

    }

    public function delete() {
        $params = $this->get_params(array('id'));

        $rs = $this->loadModel('Blog')->del(array('id'=>$params['id']));
        $this->ajaxOut($rs,'Blog/index');
    }
}