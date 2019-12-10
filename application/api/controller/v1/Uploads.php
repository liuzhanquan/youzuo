<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/4/25
 * Time: 16:16
 */

namespace app\api\controller\v1;
use hg\Code;
use hg\ServerResponse;
use think\Exception;
use app\api\controller\Api;
use think\facade\Env;

class Uploads extends Api
{
    /**
     * 单文件上传
     **/
    public function uploadData(){
        try{
            if ($_FILES) {
                $fileMusic = request()->file('file');
                $upload_path = Env::get('root_path') . 'public' . '/' . 'uploads';
                $upload_file = $_FILES;
                $file_name = explode('.', $upload_file['file']['name']);
                $upload_name = date('Ymd').'/'.rand(100000,999999).$file_name[0];
                $info = $fileMusic->validate(['size'=>1024*1024*50,'ext'=>'gif,png,jpg,jpeg'])->move($upload_path,$upload_name);
                if(!$info){
                    ServerResponse::message(Code::CODE_INTERNAL_ERROR,$fileMusic->getError());
                }
                $fileInfo = $info->getInfo();
                $path = $info->getSaveName();
                $local = '/uploads/'.$path; // 返回路径
                return json(['StatusCode'=>20000,'message'=>'Success','data'=>['url'=>$local]]);
                ServerResponse::message(Code::CODE_SUCCESS,'请求成功',['url'=>$local]);
            }else{
                ServerResponse::message(Code::CODE_INTERNAL_ERROR);
            }
        }catch (Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
        }

    }
}