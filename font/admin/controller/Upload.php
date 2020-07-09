<?php
namespace app\admin\controller;
use \app\common\controller\AuthBack;
use \app\common\model\Uploads;

class Upload extends AuthBack{


	public function _initialize(){
        parent::_initialize();
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
                $result = $this->bduploader();
                
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
                $result = $this->bduploader();
                $result['url'] = '/'.$result['url'];
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
            $return['data']['url'] = '/'.$info;
        } else {
            $return['status'] = 0;
            $return['info'] = $info;
        }
        return json($return);
    }
}
