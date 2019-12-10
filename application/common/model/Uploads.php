<?php
namespace app\common\model;
use \think\Model;
use think\facade\Env;
use \think\Request;
use app\common\model\Config;

class Uploads extends Model{

    protected $error = '';
    protected $dbConfig = array();
    /**
     * 上传数据
     * @return array 文件信息
     */
    public function uploadData($siteId = '0',$type = '0',$name='file'){
    	$config = load_config('application/upload');
    	$file = request()->file($name);
    	$size = $config['upload_size']*1024*1024;
    	switch ($type) {
    		case '1':
					$upload_path = Env::get('root_path') . 'public' . '/' . 'uploads';
					$upload_file = $_FILES;
					$file_name = explode('.', $upload_file[$name]['name']);
					$upload_name = $file_name[0];
					$info = $file->validate(['size'=>$size,'ext'=>$config['upload_type']])->move($upload_path,$upload_name);
					if(!$info){
						return $file->getError();
					}
					$path = '/'.$info->getSaveName();
    			break;
    		
    		default:
					$upload_path = Env::get('root_path') . 'public' . '/' . 'uploads';
					$info = $file->validate(['size'=>$size,'ext'=>$config['upload_type']])->move($upload_path);
					if($info){
						$path = $info->getSaveName();//获取上传文件名
						// 缩略图
						if($config['thumb_status']){
							$image = \think\Image::open($upload_path.'/'.$path);
							$path = $info->getSaveName().'_'.$config['thumb_width'].'x'.$config['thumb_height'].'.'.$info->getExtension();;
							$image->thumb($config['thumb_width'],$config['thumb_height'],$config['thumb_type'])->save($upload_path.'/'.$path);
						}
						// 水印
						if($config['watermark_status']){
							$image = \think\Image::open($upload_path.'/'.$path);
							if($config['watermark_type'] == '1'){
								// 图片水印
								$image->water(Env::get('root_path').'font/shuiyin.png',$config['watermark_local'])->save($upload_path.'/'.$path);
							} else if($config['watermark_type'] == '2'){
								// 文字水印
								$image->text($config['watermark_text'],Env::get('root_path') .'font/font.ttf',$config['watermark_text_size'],$config['watermark_text_color'],$config['watermark_local'])->save($upload_path.'/'.$path);
							}
						}
						$local = 'uploads/'.$path; // 返回路径
						$path = $local; // 返回路径
					}else{
						return $file->getError();
					}
    			break;
    	}
    	// 文件入库
    	$dbConfig = Config::where('name','upload')->value('value');
    	$this->dbConfig = unserialize($dbConfig);
    	if($this->dbConfig['type'] == '2'){
    		$upJson = $this->uploadQiniu($path,$path);
    		$upJson = json_decode($upJson,true);
    		if(isset($upJson['key'])){
    			$path = '/'.$upJson['key'];
    		}else{
    			$path = '/'.$path;
    		}
    	}

		$fileInfo = $info->getInfo();
   //  	$file_data = [
			// 'name'=>$fileInfo['name'],
			// 'type'=>$fileInfo['type'],
			// 'size'=>$fileInfo['size'],
			// 'path'=>$path,
			// 'local'=>$local,
			// 'timestamp'=>request()->time(),
   //  	];
   //  	db('uploads')->insert($file_data);
    	return $path;
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
	protected function uploadQiniu($file,$path){
		$file = Env::get('root_path') .'public'.'/'.$file;
		$data = [
	        'token' => $this->getSign($path),
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
	protected function Encode($s) {
	    return str_replace(['+', '/'], ['-', '_'], base64_encode($s));
	}

	protected function getSign($path) {

	    $toSign = $this->Encode(json_encode(['scope' => $this->dbConfig['bucket'].':' . $path, 'deadline' => time() + 1200]));
	    $sign = $this->Encode(hash_hmac('sha1', $toSign, $this->dbConfig['sk'], true));
	    return sprintf('%s:%s:%s', $this->dbConfig['ak'], $sign, $toSign);
	}
    /**
     * 生成推广海报
     * @param $siteId
     * @param $uid
     */
    public function getQrcode($siteId,$uid) {

    }
}
	