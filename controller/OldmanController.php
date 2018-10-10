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
class OldmanController extends BaseController
{
    public function index()
    {
        $_params = $this->get_params(array('file_number', 'name', 'mobile', 'idno', 'starttime', 'endtime'));
        $where = array();
        if ($_params['file_number']) $where['file_number'] = array('like', "%" . $_params['file_number'] . "%");
        if ($_params['name']) $where['name'] = array('like', "%" . $_params['name'] . "%");
        if ($_params['idno']) $where['idno'] = array('like', "%" . $_params['idno'] . "%");
        if ($_params['mobile']) $where['mobile'] = array('like', "%" . $_params['mobile'] . "%");
        if ($_params['starttime']) $where['intime'][] = array('egt', $_params['starttime']);
        if ($_params['endtime']) $where['intime'][] = array('elt', $_params['endtime']);
//        if ($_params['status'] != '') $where['oldman.status'] = $_params['status'];
        $data = $this->loadModel("oldman")->getList('*', $where, null, true);
        $this->assign('data', $data);
        $this->assign('_params', $_params);
        return $this->fetch();

    }

    public function add()
    {
        if (input('submit_oldman')) $this->save();
        $_SESSION["oldid"] = null;
        $staff = $this->loadModel("staff")->getList("*", array());
        $this->assign('staff', $staff['list']);

        $this->assign('oldman_info', null);
        return $this->fetch('edit');
    }

    public function edit()
    {
        if (input('submit_oldman')) $this->save();
//        oldman_start
//        获取staff表的fullname的列
        $staff = $this->loadModel("staff")->getList("*", array());
        $this->assign('staff', $staff['list']);
//        通过老人ID，获取老人基本信息表指定ID数据
        $id = intval(input('id'));
        $_SESSION['oldid'] = $id;
//        dump($id);
//        dump($_SESSION['oldid']);
//        dump($id);
        $oldman_info = $this->loadModel('oldman')->getInfoByid($id);
//        计算老人现在年龄
        $oldman_info['age'] = date("Y") - substr($oldman_info['birth'], 0, 4);
//        oldman_end


        $this->assign('id', $id);
        if ($_SESSION['oldid'] == 0) {
            $this->assign('oldman_info', null);
        } else {
            $this->assign('oldman_info', $oldman_info);
        }

        return $this->fetch();
    }

    public function save()
    {
        //oldman
        $_params = $this->get_params(array('id', 'file_number', 'headpic', 'name', 'idno', 'mobile', 'birth',
            'sex', 'marital_status', 'nation', 'blood_tpye', 'political_status', 'retire_job', 'address',
            'family_members', 'huji_address', 'old_type', 'religious_belief', 'education', 'work_ability', 'pocket_book',
            'contract_date', 'intime', 'agent', 'status'));
        //oldfamily

//        ChromePhp::log($_params);
        $result = $this->validate($_params, 'oldman.add');
        if (true !== $result) {
            // 验证失败 输出错误信息
            $this->response(PARAMS_ERROR, $result);
        }
        $data = $_params;
        $path = '/upload/oldman/';
        if (!strstr($data['headpic'], $path)) {
            $new_path = $path . date('Ymd') . '/' . create_uuid() . '.png';
            rename(ROOT_PATH . $path . date('Ymd') . '/' . $data['headpic'], ROOT_PATH . $new_path);
            $data['headpic'] = $new_path;
        }

        $id = intval(input('id'));
        if ($id > 0) {
            $oldman = $this->loadModel('oldman')->getInfoByid($id);
//            $oldman = $this->loadModel('oldman')->getInfo("*", array('id' => $id));
            if ($oldman) {
                $rs = $this->loadModel('oldman')->update($data, array('id' => $id));
            }
        } else {
            $oldman = $this->loadModel('oldman')->getInfo("*", array('file_number' => $_params['file_number']));
            if ($oldman) {
                $this->response(PARAMS_ERROR, '该档案编号已存在');
            }
            $data['addtime'] = time();    //添加时间

            $rs = $this->loadModel('oldman')->add($data);
//            $id=$this->loadmodel('oldman')->save($data);

            ChromePhp::log($rs);
            $_SESSION['oldid'] = $rs;
            ChromePhp::log($_SESSION['oldid']);
        }
        if ($rs) $this->response(REQUEST_SUCCESS, '保存成功');
//        $this->ajaxOut($rs, 'oldman/index');
    }

    public function delete()
    {
        $_params = $this->get_params(array('id'));
        $this->vaild_params('is_empty', $_params['id'], '请选择一个要删除的项目');

        $rs = $this->loadModel('oldman')->del(array("id" => $_params['id']));

//        $family_list = $this->loadModel("oldfamily")->getList("*", array('id' => $_params['id']));
//        $this->loadModel('oldfamily')->del(array("family_id" => $family_list['id']));
        $this->ajaxOut($rs, 'oldman/index');
    }

    public function indexfamily()
    {
//      用老人id查找family_list
        if (intval(input('id')) > 0) {
            $id = intval(input('id'));
        } else {
            $id = intval($_SESSION['oldid']);
        }
//        dump($id);
//        dump($_SESSION['oldid']);
//        dump($id);
        $oldman_info = $this->loadModel('oldman')->getInfoByid($id);
        $family_list = $this->loadModel("oldfamily")->getList("*", array('id' => $id));

        $this->assign('id', $id);
        $this->assign('family_list', $family_list);
        $this->assign('oldman_info', $oldman_info);
        return $this->fetch();

    }

    public function addfamily()
    {
        if (input('submit_family')) $this->savefamily();
//        dump($_SESSION['oldid']);
        $array = $this->request->param();
        $this->assign('id', $array['id']);
        return $this->fetch('addfamily');
    }

    public function editfamily()
    {
        if (input('submit_family')) $this->savefamily();
        $family_id = intval(input('family_id'));
        $family_info = $this->loadModel('oldfamily')->getInfo('*', array('family_id' => $family_id));
        $this->assign('family_info', $family_info);

        return $this->fetch();
    }

    public function savefamily()
    {
        $_params = $this->get_params(array('family_id', 'id', 'name', 'mobile', 'idno', 'relationship',
            'sex', 'address', 'guarder'));

//        ChromePhp::log($_params);
        $result = $this->validate($_params, 'oldfamily.add');
        if (true !== $result) {
            // 验证失败 输出错误信息
            $this->response(PARAMS_ERROR, $result);
        }
        $data = $_params;

        $family_id = intval(input('family_id'));
        if ($family_id > 0) {
            $oldfamily = $this->loadModel('oldfamily')->getInfo('*', array('family_id' => $family_id));
//            $oldman = $this->loadModel('oldman')->getInfo("*", array('id' => $id));
            if ($oldfamily) {
                $rs = $this->loadModel('oldfamily')->update($data, array('family_id' => $family_id));
            }
        } else {
            $oldfamily = $this->loadModel('oldfamily')->getInfo("*", array('name' => $_params['name']));
            if ($oldfamily) {
                $this->response(PARAMS_ERROR, '该家属已存在,姓名重复');
            }
            $data['addtime'] = time();    //添加时间
            $rs = $this->loadModel('oldfamily')->addfamily($data);
//                dump(model('oldman')->getLastSql());
        }
        if ($rs) $this->response(REQUEST_SUCCESS, '保存成功');
//        $this->ajaxOut($rs, 'oldman/indexfamily');
    }

    public function deletefamily()
    {
        $_params = $this->get_params(array('family_id'));
        $this->vaild_params('is_empty', $_params['family_id'], '请选择一个要删除的项目');
        $rs = $this->loadModel('oldfamily')->del(array("family_id" => $_params['family_id']));

        $this->ajaxOut($rs, 'oldman/indexfamily');

    }

    public function edithealthinfo()
    {
        if (input('submit_health')) $this->savehealth();
        if (intval(input('id')) > 0) {
            $id = intval(input('id'));
        } else {
            $id = intval($_SESSION['oldid']);
        }
//        dump($id);
//        dump($_SESSION['oldid']);
        if ($id) {
            $healthinfo = $this->loadModel('healthinfo')->getInfo("*", array('id' => $id));
            $oldman_info = $this->loadModel('oldman')->getInfoByid($id);
            $this->assign('id', $id);
            $this->assign('healthinfo', $healthinfo);
            $this->assign('oldman_info', $oldman_info);
        }
        return $this->fetch();
    }

    public function savehealth()
    {
        $_params = $this->get_params(array('condtion_id', 'id', 'selfcarecondtion', 'vision', 'listen', 'hospital',
            'doctor', 'mobile', 'drugallergy', 'medicalhistory'));

//        ChromePhp::log($_params);
//        $result = $this->validate($_params, 'healthinfo.add');
//        if (true !== $result) {
//            // 验证失败 输出错误信息
//            $this->response(PARAMS_ERROR, $result);
//        }
        $data = $_params;
//        ChromePhp::log($data);
//        ChromePhp::log(json_encode());
        $condtion_id = intval(input('condtion_id'));
        if ($condtion_id > 0) {
            $healthinfo = $this->loadModel('healthinfo')->getInfo('*', array('condtion_id' => $condtion_id));
            if ($healthinfo) {
                $rs = $this->loadModel('healthinfo')->update($data, array('condtion_id' => $condtion_id));
            }
        } else {
            $rs = $this->loadModel('healthinfo')->addhealth($data);
        }

        if ($rs) $this->response(REQUEST_SUCCESS, '保存成功');
//        $this->ajaxOut($rs, 'oldman/edithealthinfo');
    }
}