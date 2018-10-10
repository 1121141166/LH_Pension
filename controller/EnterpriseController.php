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

class EnterpriseController extends BaseController {

    public function index(){
        if (input('submit')) $this->save();
        $data = $this->loadModel('enterprise')->getInfo('*');
        $this->assign('data',$data);
        return $this->fetch('edit');
    }
    
    /*
	*	保存用户信息
	*	@todo 1、图片上传部分 2、上级商户
	*/
    public function save()
    {
        $_params = $this->get_params(array('id','name','logo','contact','phone','email','address','status'));
        $result = $this->validate($_params,'enterprise.add');
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->response(PARAMS_ERROR,$result);
        }

        $data = $_params;

        $path = '/upload/enterprise/';
        if (!strstr($data['logo'], $path)) {
            $new_path = $path.date('Ymd').'/'.create_uuid().'.png';
            rename(ROOT_PATH.$path.date('Ymd').'/'.$data['logo'],ROOT_PATH.$new_path);
            $data['logo'] = $new_path;
        }

        $id = intval($_params['id']);
        if ($id>0)
        {
            $rs = $this->loadModel('enterprise')->update($data,array('id'=>$id));
        }
        else
        {
            $data['addtime'] = time();	//添加时间
            $rs = $this->loadModel('enterprise')->save($data);

        }
        $this->ajaxOut($rs,'enterprise/index');

    }
}