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
use think\Db;
use Chromephp\ChromePhp;

vendor("chromephp.ChromePhp");

//        ChromePhp::log(json_encode());
class OutRegisterController extends BaseController
{
    public function index()
    {
        $_params = $this->get_params(array('file_number', 'oldname', 'escortmobile', 'status', 'starttime', 'endtime'));
        ChromePhp::log(json_encode($_params));
        $where = array();
        if ($_params['file_number']) $where['file_number'] = array('like', "%" . $_params['file_number'] . "%");
        if ($_params['oldname']) $where['oldname'] = array('like', "%" . $_params['oldname'] . "%");
        if ($_params['escortmobile']) $where['escortmobile'] = array('like', "%" . $_params['escortmobile'] . "%");
        if ($_params['status']) $where['status'] = array('like', "%" . $_params['status'] . "%");
        if ($_params['starttime']) $where['outtime'][] = array('egt', $_params['starttime']);
        if ($_params['endtime']) $where['outtime'][] = array('elt', $_params['endtime']);
//        if ($_params['status'] != '') $where['oldman.status'] = $_params['status'];
        $data = $this->loadModel("outregister")->getList('*', $where, null, true);
        $this->assign('data', $data);
        $this->assign('_params', $_params);
        return $this->fetch();

    }

    public function add()
    {
        $staff_info = $this->loadModel('staff')->getInfoByid(session('admin.staff_id'));
        $data['agent'] = $staff_info['fullname'];
        $data['outtime'] = time();
        if (input('submit_add')) $this->save();
        $this->assign('data', $data);
        return $this->fetch();
    }

    public function edit()
    {
        if (input('submit_edit')) $this->save();

        $staff_info = $this->loadModel('staff')->getInfoByid(session('admin.staff_id'));
        $data['backagent'] = $staff_info['fullname'];
        $data['actbacktime'] = time();

        $id = intval(input('id'));
        $outregister_info = $this->loadModel('outregister')->getInfoByid($id);
        $this->assign('id', $id);
        $this->assign('data', $data);
        $this->assign('outregister_info', $outregister_info);

        return $this->fetch();
    }

    public function detail()
    {
        $id = intval(input('id'));
        $outregister_info = $this->loadModel('outregister')->getInfoByid($id);
        $this->assign('id', $id);
        $this->assign('outregister_info', $outregister_info);
        return $this->fetch();
    }

    public function save()
    {
        //oldman
        $_params = $this->get_params(array('id', 'file_number', 'oldname', 'agent', 'type',
            'escortname', 'escortmobile', 'escoridno', 'escoraddress', 'outtime', 'backtime', 'actbacktime',
            'status', 'reason', 'backagent'));
        //oldfamily

        ChromePhp::log($_params);
//        $result = $this->validate($_params, 'oldman.add');
//        if (true !== $result) {
//            // 验证失败 输出错误信息
//            $this->response(PARAMS_ERROR, $result);
//        }
        $data = $_params;
        $id = intval(input('id'));
        if ($id > 0) {
            $outregister = $this->loadModel('outregister')->getInfoByid($id);
//            $oldman = $this->loadModel('oldman')->getInfo("*", array('id' => $id));
            if ($outregister) {
                $rs = $this->loadModel('outregister')->update($data, array('id' => $id));
            }
        } else {
            $outregister_info = $this->loadModel('outregister')->getInfo("*", array('outtime' => $_params['outtime']));
            if ($outregister_info) {
                $this->response(PARAMS_ERROR, '该记录已存在，请勿重复保存');
            }
            $rs = $this->loadModel('outregister')->add($data);
        }
        if ($rs) $this->response(REQUEST_SUCCESS, '保存成功');
//        $this->ajaxOut($rs, 'oldman/index');
    }

    public function delete()
    {
        $_params = $this->get_params(array('id'));
        $this->vaild_params('is_empty', $_params['id'], '请选择一个要删除的项目');

        $rs = $this->loadModel('outregister')->del(array("id" => $_params['id']));

        $this->ajaxOut($rs, 'outregister/index');
    }
}