<?php
namespace admin\index\controller;
use admin\index\controller\BaseController;
class UploadController extends BaseController
{

    //文件上传
    public function upload() {
        $file = $_FILES['file'];//得到传输的数据
        $filename = input('filename');
        //得到文件名称
        $name = $file['name'];
        $type = strtolower(substr($name,strrpos($name,'.')+1)); //得到文件类型，并且都转化成小写
        $allow_type = array('jpg','jpeg','gif','png','xls','xlsx'); //定义允许上传的类型
        //判断文件类型是否被允许上传
        if(!in_array($type, $allow_type)){
            //如果不被允许，则直接停止程序运行
            return ;
        }
        //判断是否是通过HTTP POST上传的
        if(!is_uploaded_file($file['tmp_name'])){
            //如果不是通过HTTP POST上传的
            return ;
        }
        $upload_path = ROOT_PATH.'/upload/'.$filename.'/'.date('Ymd').'/'; //上传文件的存放路径
        if (!is_dir($upload_path)) xmkdir($upload_path);
        //开始移动文件到相应的文件夹
        if(move_uploaded_file($file['tmp_name'],$upload_path.$file['name'])){
            echo "Successfully!";
        }else{
            echo "Failed!";
        }

    }

    //字符转换
    function file_build_path($segments) {
        return join(DIRECTORY_SEPARATOR, $segments);
    }

    //文件上传
    public function upload_file($filename) {
        $file = $_FILES['file'];//得到传输的数据

        //得到文件名称
        $name = $file['name'];
        $type = strtolower(substr($name,strrpos($name,'.')+1)); //得到文件类型，并且都转化成小写
        $allow_type = array('jpg','jpeg','gif','png','xls','xlsx'); //定义允许上传的类型
        //判断文件类型是否被允许上传

        if(!in_array($type, $allow_type)){
            //如果不被允许，则直接停止程序运行
            $data['code'] = '01';
            $data['message'] = '文件类型错误';
            return $data;
        }
        //判断是否是通过HTTP POST上传的
        if(!is_uploaded_file($file['tmp_name'])){
            //如果不是通过HTTP POST上传的
            $data['code'] = '01';
            $data['message'] = '上传方式错误';
            return $data;
        }
        $upload_path = ROOT_PATH.'/upload/'.$filename.'/'.date('Ymd').'/'; //上传文件的存放路径

//        vendor("chromephp.ChromePhp");
//        ChromePhp::log('Hello console!');
//
        if (!is_dir($upload_path)) xmkdir($upload_path);
        //开始移动文件到相应的文件夹
        if(move_uploaded_file($file['tmp_name'],$upload_path.$file['name'])){
            $data['code'] = '00';
            $data['message'] = $upload_path.$file['name'];
            return $data;
        }else{
            $data['code'] = '01';
            $data['message'] = '上传失败';
            return $data;
        }

    }

}
