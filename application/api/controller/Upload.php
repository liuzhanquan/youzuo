<?php
namespace app\api\controller;
use \app\common\model\Uploads;
use think\facade\Env;
use \app\common\controller\ApiController;
use think\Db;
use Qiniu\Storage\UploadManager;
use Qiniu\Auth;

class Upload extends ApiController{


	public function _initialize(){
        // parent::_initialize();
        $this->checkLogin();
    }
    /**
     * 普通图片上传
    **/
    public function index(){
        $configPath =  env('root_path')."/public/static/js/lib/ueditor/config.json";
        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents($configPath)), true);
        $action = $_GET['action'] ? $_GET['action'] : 'config';
        switch ($action) {
            case 'config':
                $result =  $CONFIG; // 必须输出至页面
            break;

            /* 上传图片 */
            case 'uploadimage':
            /* 上传涂鸦 */
            case 'uploadscrawl':
            /* 上传视频 */
            case 'uploadvideo':
            /* 上传文件 */
            case 'uploadfile':
                $QNstatus = DB::name('config')->where('name','upload')->value('value');
                $url = config('PHOTOPATH');
                if( $QNstatus != 1 ){
                    $result = $this->bduploader();
                }else{
                    $url =  DB::name('config')->where('name','QNcdn')->value('value');
                    $result = $this->QNuploader();
                    
                }
                
                
            break;
            /* 列出图片 */
            case 'listimage':
                $result = $this->getFile();
            break;
            /* 列出文件 */
            case 'listfile':
                $result = '';
            break;

            /* 抓取远程文件 */
            case 'catchimage':
                $result = '';
            break;

            default:
                $result = array(
                    'state'=> '请求地址出错'
                );
            break;
        }
        
        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                return htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                return json(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        } else {
            return json($result);
        }
    }
    /**
     * 普通图片上传
    **/
    public function index2(){
        
        
        $return = array('status' => 1, 'info' => '上传成功', 'data' => array());
        $upObj = new Uploads;
        // $info = $upObj->uploadData3('0');
        $info = $this->QNuploader();
        if ($info) {
            $return['data']['url'] = '/'.$info;
            return_ajax(200,'cg',photo_addpath('/'.$info) );
        } else {
            $return['status'] = 0;
            $return['info'] = $info;
        }
        
        
        

    }

    /**
     * 普通图片上传
    **/
    public function upimg(){
        $config = load_config('application/upload');
        $data = rawPost();
        $logo_data = $data['base64'];
        if(!empty($logo_data)){
            
            $accessKey = DB::name('config')->where('name','QNaccessKey')->value('value');
            $secretKey = DB::name('config')->where('name','QNsecretKey')->value('value');
            $bucket    = DB::name('config')->where('name','QNbucket')->value('value');

            $pathinfo = $logo_data;

            // 构建鉴权对象
            $auth = new Auth($accessKey,$secretKey);

            // 生成上传需要的token
            $token = $auth->uploadToken($bucket);

            // 上传到七牛后保存的文件名
            $filename = date('Y').'/'.date('m').'/'.substr(md5($logo_data),8,5).date('Ymd').rand(0,9999).'.png';
            

            // 初始化UploadManager类
            $uploadMgr = new UploadManager();
            list($ret, $err) = $uploadMgr->putFile($token,$filename,$data['base64']);
            $file['path']= '/'.$filename;
            $file['url']= photo_addpath('/'.$filename);
            if($err !== null){
                return null;
            }else{
                return_ajax(200,'成功',$file);
            }
            
            
        }
        return_ajax(40003,'参数错误');
        

    }

    // public function upimg(){
    //     $config = load_config('application/upload');
    //     $data = rawPost();
    //     $logo_data = $data['base64'];
    //     if(!empty($logo_data)){
    //         //$data = file_get_contents('./1.txt');
    //         $reg = '/data:image\/(\w+?);base64,(.+)$/si';
    //         preg_match($reg,$logo_data,$match_result);

    //             $file_name = time().mt_rand(10000,99999).'.'.$match_result[1];
                
    //             $logo_path = Env::get('root_path') . 'public' . '/' . 'uploads/'.$file_name;
    //             // $logo_path = WEB_PATH.'/uploads/logo/'.$file_name;
    //             $num = file_put_contents($logo_path,base64_decode($match_result[2]));
                
                
    //             if(!empty($num)){
    //                 $imgFile = $logo_path;
            
    //                 if(file_exists($imgFile)){
    //                     $isImg = $imgFile;
    //                     $image = \think\Image::open($isImg);
    //                     $date = date("Ymd");
    //                     $path = \think\facade\Env::get('root_path').'public'.str_replace("\\", '/', '/uploads/'.$date.'/');
                        
    //                     if(!file_exists($path)){
    //                         mkdir ($path,0777,true);
    //                     }
    //                     $newImgName = $path . $file_name;
                        
    //                     $res = $image->thumb($config['thumb_width'],$config['thumb_height'],$config['thumb_type'])->save($newImgName);
    //                     $file['path']= '/uploads/'.$date.'/'.$file_name;
    //                     $file['url']= photo_addpath('/uploads/'.$date.'/'.$file_name);
    //                     unlink($imgFile);
    //                 }else{
    //                     $file='uploadfile/nopic.gif';
    //                 }
    //                 return_ajax(200,'成功',$file);
    //             }else{
    //                 return_ajax(40001,'失败');
    //             }
    //         }else{
    //             return_ajax(40002,'参数错误');
    //         }
    //         return_ajax(40003,'参数错误');
        

    // }



    /**
     * 图片上传七牛云
     */
    protected function QNuploader(){
        $accessKey = DB::name('config')->where('name','QNaccessKey')->value('value');
        $secretKey = DB::name('config')->where('name','QNsecretKey')->value('value');
        $bucket    = DB::name('config')->where('name','QNbucket')->value('value');
        if(empty($_FILES['upfile']['tmp_name'])){
            explode('图片不合法',404);
        }
        
        // 要上传文件的临时文件
        $file = $_FILES['upfile']['tmp_name'];

        $pathinfo = pathinfo($_FILES['upfile']['name']);

        // 通过pathinfo函数获取图片后缀名
        $ext = $pathinfo['extension'];


        // 构建鉴权对象
        $auth = new Auth($accessKey,$secretKey);

        // 生成上传需要的token
        $token = $auth->uploadToken($bucket);

        // 上传到七牛后保存的文件名
        $filename = date('Y').'/'.date('m').'/'.substr(md5($file),8,5).date('Ymd').rand(0,9999).'.'.$ext;
        

        // 初始化UploadManager类
        $uploadMgr = new UploadManager();
        list($ret, $err) = $uploadMgr->putFile($token,$filename,$file);
        
        if($err !== null){
            return null;
        }else{
            return $filename;
        }
        
      
    }


    /**
     * 百度编辑器上传
    **/
    protected function bduploader(){
       
        if(empty($_FILES)){
            $this->redirect('/');
            exit;
        }
        
        switch (htmlspecialchars($_GET['action'])) {
            case 'uploadimage': // 上传图片
                $fieldName = 'upfile';
            break;
            case 'uploadfile': // 上传文件
                $fieldName = 'upfile';
            break;
            case 'uploadvideo': // 上传视频
                $fieldName = 'upfile';
            break;
        }
        $return = array('state' => 'SUCCESS', 'info' => '上传成功');
        $upObj = new Uploads;
        $info = $upObj->uploadData('0','0',$fieldName);
        
        if (filter_var($info, FILTER_VALIDATE_URL)) {
            $return['url'] = $info;
        } else {
            $return['url'] = $info;
        }
        return $return;
    }
    /**
	 * 七牛云上传
	 * @author Azaz QQ:826355918
	 * @DateTime 2019-03-30
	 * @param    [type]
	 * @param    [type]     $file [description]
	 * @param    [type]     $path [description]
	 * @return   [type]           [description]
	 */
	protected function uploadQiniuimg($file,$path){
		$file = Env::get('root_path') .'public'.'/'.$file;
		$data = [
	        'token' => $this->getSigns($path),
	        'file' => new \CURLFile(realpath($file)),
	        'key' => $path
	    ];

	    $curl = curl_init();
	    //curl_setopt($curl, CURLOPT_URL, 'http://po8nvz5xt.bkt.clouddn.com');
		curl_setopt($curl, CURLOPT_URL, 'http://up.qiniu.com');
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	    $data = curl_exec($curl);
	    $error = curl_error($curl);
	    curl_close($curl);
	    return $data;
    }
    protected function getSigns($path) {
        $accessKey = DB::name('config')->where('name','QNaccessKey')->value('value');
        $secretKey = DB::name('config')->where('name','QNsecretKey')->value('value');
        $bucket    = DB::name('config')->where('name','QNbucket')->value('value');
	    $toSign = $this->Encode(json_encode(['scope' => $bucket.':' . $path, 'deadline' => time() + 1200]));
	    $sign = $this->Encode(hash_hmac('sha1', $toSign, $secretKey, true));
	    return sprintf('%s:%s:%s', $accessKey, $sign, $toSign);
    }
    protected function Encode($s) {
	    return str_replace(['+', '/'], ['-', '_'], base64_encode($s));
	}
    /**
     * 列取目录
    **/
    protected function getFile($siteId = '0'){
        $config = load_config('application/upload');
        switch (htmlspecialchars($_GET['action'])) {
            /* 列出文件 */
            case 'listfile':
                $allowFiles = $CONFIG['fileManagerAllowFiles'];
                $listSize = '20'; // 每页数量
                $path = env('root_path') . 'public' . '/' . 'uploads'.'/'; // 路径
                break;
            /* 列出图片 */
            case 'listimage':
            default:
                $allowFiles = [".png", ".jpg", ".jpeg", ".gif", ".bmp"]; // 文件类型
                $listSize = '20'; // 每页数量
                $path = env('root_path') . 'public' . '/' . 'uploads'.'/'; // 路径
        }
        $allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);
        /* 获取参数 */
        $size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $listSize;
        $start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
        $end = $start + $size;

        /* 获取文件列表 */
        // $path = $_SERVER['DOCUMENT_ROOT'] . (substr($path, 0, 1) == "/" ? "":"/") . $path;
        $files = getfiles($path, $allowFiles);
        if (!count($files)) {
            return json_encode(array(
                "state" => "no match file",
                "list" => array(),
                "start" => $start,
                "total" => count($files)
            ));
        }

        /* 获取指定范围的列表 */
        $len = count($files);
        for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
            $list[] = $files[$i];
        }
        //倒序
        //for ($i = $end, $list = array(); $i < $len && $i < $end; $i++){
        //    $list[] = $files[$i];
        //}

        /* 返回数据 */
        $result = array(
            "state" => "SUCCESS",
            "list" => $list,
            "start" => $start,
            "total" => count($files)
        );
        return $result;
    }
    /**
     * 编辑器上传
    **/
    public function editor(){
        if(empty($_FILES)){
            $this->redirect('/');
            exit;
        }
        $return = array('success' => true, 'msg' => '上传成功', 'file_path' => '');
        $upObj = new Uploads;
        $info = $upObj->uploadData('0');
        if ($info) {
            $return['file_path'] = $info;
        } else {
            $return['success'] = 0;
            $return['msg'] = $file->getError();
        }
        return json($return);
    }
    /**
     * 单独图片上传
    **/
    public function alone(){
        if(empty($_FILES)){
            $this->redirect('/');
            exit;
        }
        $return = array('status' => 1, 'info' => '上传成功', 'data' => array());
        $upObj = new Uploads;
        $info = $upObj->uploadData('0');
        if ($info) {
            $return['data']['url'] = $info;
        } else {
            $return['status'] = 0;
            $return['info'] = $info;
        }
        return json($return);
    }
}
