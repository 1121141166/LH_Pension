<?php
namespace admin\index\controller;
use think\Controller;
use think\Request;

class BaseController extends Controller
{
	public $model;	
	public $url;	//当前url
    public $pre_url;//上一个请求url
	public $ma;
    public $request;
	
	public function _initialize()
	{
        $this->request = Request::instance();
		$this->setUrl();
		$this->isLogin();
		$this->checkActions();
		// dump(session('admin'));
		$this->assign('ma',strtolower($this->request->module()."/".$this->request->controller()));
		$this->assign('session',session('admin'));
		$this->assign('actions',session('actions'));
	}
	
	//判断是否登录
	public function isLogin(){
		$action = strtolower($this->request->module()."/".$this->request->controller()."/".$this->request->action());
		$module = strtolower($this->request->module()."/");
		$nocheck = array('index/base/login','index/base/logout','index/base/code');
		if (!session('admin') && !in_array($action,$nocheck) && !in_array($module,$nocheck))
		{
			header("Location:".url('base/login'));
			exit;
		}
	}
	
	//登录界面
	public function login()
	{
		$username = input('username');
		$password = input('password');
		$code = input('code');
		if (input('login'))
		{
//			if ($code != session('verify')) {
//				$this->ajaxReturn(array('href'=>url('base/login')),'验证码错误！',PARAMS_ERROR);
//				exit;
//			}
			$where = array();
			$where['username'] = $username;
			$where['status'] = 1;
			$join[] = array('staff','staff_id','id');
			$fields['admin'] = '*';
			$fields['staff'] = 'headpic';
			$user = $this->loadModel('admin')->getJoinInfo($fields,$join,$where);
			if (!$user)
			{
                $this->response(PARAMS_ERROR,'用户名不存在');
				exit;
			}
			else
			{
			    if($user['password'] != md5($password)){
                    $this->response(PARAMS_ERROR,'密码错误');
                    exit;
                }
				session('admin',$user);
				session('actions',json_decode($user['actions'],true)?json_decode($user['actions'],true):$user['actions']);
				$this->ajaxOut($user,'index/index');
				exit;
			}
		}
		return $this->fetch('common/login');
	}
	
	public function logout()
	{
		session("admin",null);
		header("Location:".url('base/login'));
		exit;
	}
	
		//权限检查
	public function checkActions()
	{
		$skip_actions = array('base/login','base/code','base/logout','index/index','index/total_statistics','usersaler/search');
		$action = strtolower($this->request->controller()."/".$this->request->action());
		$action_action = strtolower($this->request->controller()."/index");

		$allow_actions = session('actions');

		if ($allow_actions == 'all' || in_array($action,$skip_actions))
		{
			return ;
		}

		if ($allow_actions['actions'] != null && !in_array($action,$allow_actions['actions']) && !in_array($action_action,$allow_actions['actions']))
		{
			if (input('is_ajax'))
			{
				$this->response(INTERNAL_ERROR,'您没有权限使用此功能！');
			}
			else
			{
				echo '<script type="text/javascript">';
				echo 'alert("您没有权限使用此功能！");';
				echo 'window.history.back()';
				echo '</script>';
				exit;
			}
			
		}
	}

	/*
	*	接口响应输出
	*	@param			int		$result_code	响应代码
	*	@param			string	$result_msg		接口响应信息
	*	@param			array	$data					接口数据
	*/
	public function response($result_code,$result_msg,$data = array(),$link='')
	{
		$response = array();
		$response['result']['code'] = $result_code;
		$response['result']['msg'] = $result_msg;
		$response['link'] = $link;
		$response['data'] = $data;
		echo json_encode($response);
		exit;
	}
	
	/*
	*	api输出
	*	@param			array		$data		要显示的接口数据或根据数据判断接口显示的结构体
	*	@param			bool		$show	是否显示数据结构体，如果false只显示result部分，不显示data部分
	*/
	public function ajaxOut($data = array(),$link = '',$show = true,$params = array())
	{
		if (!$data)
		{
			$this->response(INTERNAL_ERROR,'服务器内部错误或数据为空');
		}
		else
		{
			if ($show)
			{
				$this->response(REQUEST_SUCCESS,'请求成功',$data,url($link,$params));
			}
			else
			{
				$this->response(REQUEST_SUCCESS,'请求成功',array(),url($link,$params));
			}
		}
	}	
	
	/*
	*	参数验证
	*	@param		callback		$call			调用函数名
	*	@param		array			$params	调用函数的参数名
	*	@param		string			$msg			提示信息
	*	@param		bool			$rule			验证规则
	*/
	public function vaild_params($call,$params,$msg='',$rule = true)
	{
		if (!is_callable($call))
		{
			throw new Exception($call." can not callable!");
		}
		$params = !is_array($params)?array($params):$params;
		if (call_user_func_array($call,$params) == $rule)
		{
			return true;
		}
		else
		{
			if (input('is_ajax'))
			{
				$this->response(PARAMS_ERROR,$msg);	
			}
			else
			{
				alert_msg($msg,$_SERVER['HTTP_REFERER']);
			}
			
		}
		
	}
	
	public function loadModel($model)
	{
		$this->model[$model] = model($model);
		return $this->model[$model];
	}
	
	/*
	*	获取指定的请求参数
	*	@param			array			$field		需要获取的参数的字段名
	*	@param			string			$method		获取类型，可取值_request,_get,_post
	*/
	public function get_params($field)
	{
		$data = array();
		foreach($field as $k)
		{
			$data[$k] = input($k,'');
		}
		return $data;
	}

	//验证码
	public function code(){
		import('@.Tool.Image');
		Image::verify();
	}
	
	private function setUrl()
	{

		$this->url = "http://".rtrim($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],'&');
        $this->pre_url = $_SERVER['HTTP_REFERER'];
		$this->assign('_url',$this->url);
	}
}
