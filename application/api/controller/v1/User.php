<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/8/5
 * Time: 14:58
 */

namespace app\api\controller\v1;
use app\backman\model\Agent;
use app\common\model\User as UserModel;
use hg\Code;
use hg\ServerResponse;
use app\api\controller\Api;
use think\Db;
use think\facade\Env;
use app\common\model\Order;
use think\facade\Request;
use GuzzleHttp\Client;
use app\common\model\UserLog;
use app\common\model\UserCommission;
class User extends Api
{

    /**
     * 分享商品围观
     */
    public function watch(){
        try{
            $agent_id = request()->post('agent_ids') ?? 0;
            $goods_id = request()->post('goods_id') ?? 0;
            $uid = $this->uid;
            $info = Db::name('goods_watch')->where(['agent_id'=>$agent_id,'goods_id'=>$goods_id,'user_id'=>$uid])->find();
            if(!$info){
                //创建数据
                $data = ['agent_id'=>$agent_id,'goods_id'=>$goods_id,'user_id'=>$uid];
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['updated_at'] = date('Y-m-d H:i:s');
                Db::name('goods_watch')->insertGetId($data);
            }
            return json(['StatusCode'=>20000,'message'=>'请求成功']);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
        }
    }


    /**
     * 用户团队
     */
    public function team(\app\common\model\User $user,UserLog $userLog){
        try{
            $level = $this->data['level'] ?? 1;
            $list = $userLog->where(['uid'=>$this->uid,'level'=>$level])->paginate(10,false,['query'=>request()->param()])->toArray();
            $data = $list['data'];
            if(!empty($data)){
                foreach ($data as $key=>$value){
                    $userInfo = $user->where(['uid'=>$value['source_id']])->field('headimgurl,nickname,reg_time')->find();
                    $data[$key]['user'] = $userInfo;
                }
            }
            $list['data'] = $data;
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$list]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR);
        }
    }

    /**
     * 会员分佣记录/代理分佣记录
     * @return \think\response\Json
     */
    public function commission(UserCommission $userCommission){
        try{
            $type = $this->data['type'] ?? 1;
            $agent_id = $this->data['agent_id'] ?? 0;
            //$where['user_id'] = $this->uid;
            $where['type'] = $type;
            if($type == 2){
                $where['agent_id'] = $agent_id;
            }else{
                $where['user_id'] = $this->uid;
            }
            $list = $userCommission->where($where)->field('user_id,money,tuser_id,agent_id,created_at')->order('created_at desc')->paginate(10,false,['query'=>request()->param()])->toArray();
            $data = $list['data'];
            if(!empty($data)){
                foreach ($data as $key=>$value){
                    $userInfo = \app\common\model\User::where(['uid'=>$value['user_id']])->field('headimgurl,nickname,reg_time')->find();
                    $userInfo2 = \app\common\model\User::where(['uid'=>$value['tuser_id']])->field('headimgurl,nickname,reg_time')->find();
                    $data[$key]['user'] = $userInfo;
                    $data[$key]['tuser'] = $userInfo2;
                    if($type == 2){
                        if($value['agent_id'] && !$value['tuser_id']){
                            $data[$key]['user_name'] = '推荐奖励';
                        }else{
                            $data[$key]['user_name'] = $userInfo['nickname'];
                        }
                    }
                }
            }
            $list['data'] = $data;
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$list]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR);
        }
    }

    protected function getSessionKey($appId,$secret,$code){
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$appId.'&secret='.$secret.'&js_code='.$code.'&grant_type=authorization_code';
        $client = new Client();
        $response = $client->request('GET', $url);
        $body = $response->getBody();
        $res = $body->getContents();
        $res = \GuzzleHttp\json_decode($res);
        if(isset($res->session_key)){
            //存储微信session_key,redis
            return $res->session_key;
        }
        ServerResponse::message(Code::CODE_INTERNAL_ERROR,$res->errmsg);
        return false;
    }
    /**
     * 获取用户信息详情
     * @param Request $request
     */
    public function details(Request $request,UserModel $userModel){
        //try{
            $user = $userModel->where(['uid'=>$this->uid])->find();
            //用户待付款订单，已支付订单，已取消订单，
            $user['dfk'] = Order::where(['uid'=>$this->uid,'status'=>'0'])->count('id');
            $user['dfh'] = Order::where(['uid'=>$this->uid,'status'=>'4'])->count('id');
            $user['dsh'] = Order::where(['uid'=>$this->uid,'status'=>'3'])->count('id');
            $user['yqx'] = Order::where(['uid'=>$this->uid,'status'=>'1'])->count('id');
            $user['ywc'] = Order::where(['uid'=>$this->uid,'status'=>'2'])->count('id');
            $user['team_num'] = UserLog::where(['uid'=>$this->uid])->count('id');
            $user['commission_num'] = UserCommission::where(['user_id'=>$this->uid])->where('type',1)->count('id');
            $user['commission_money'] = UserCommission::where(['user_id'=>$this->uid])->where('type',1)->sum('money');
            $user['agent'] = Db::name('agent')->where('id',$user['agent_id'])->field('phone,name')->find() ?? [];
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$user]);
            ServerResponse::message(Code::CODE_SUCCESS,'',['info'=>$user]);
//        }catch (\Exception $exception){
//            ServerResponse::message(Code::CODE_INTERNAL_ERROR);
//        }
    }

    /**
     * 获取小程序微信手机号码
     * @return mixed
     */
    public function getUserPhone() {
        try{
            $code = Request::instance()->post();
            $app_pay = config('config.site')['weixin'];
            $app_pay = unserialize($app_pay);
            $sessionKey = $this->getSessionKey($app_pay['appid'],$app_pay['secret'],$code['code']);
            if($sessionKey){
                //解密数据
                $pc = new \weixin\WXBizDataCrypt($app_pay['appid'], $sessionKey);
                $errCode = $pc->decryptData($code['encryptedData'], $code['iv'], $userData );
                if($errCode == 0){
                    //获取成功，绑定手机号码
                    $phone = $userData['phoneNumber'];
                    $res = \app\common\model\User::where(['uid'=>$this->uid])->update(['phone'=>$phone]);
                    if(!$res){
                        return json(['StatusCode'=>50000,'message'=>'绑定失败']);
                    }
                }else{
                    return json(['StatusCode'=>50000,'message'=>'绑定失败']);
                }
                return json(['StatusCode'=>20000,'message'=>'绑定成功']);
            }else{
                return json(['StatusCode'=>50000,'message'=>'绑定失败']);
            }
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR);
        }
    }

    /**
     * 获取分销小程序二维码
     * @param Request $request
     * @param UserModel $userModel
     */
    public function qrCode(Request $request,UserModel $userModel){
        //try{
            $uid = $this->uid;
            $path = Env::get('root_path') . "public";
            $file = "/uploads/user/qrcode_{$uid}.png";
            if(!file_exists($path.$file)){
                //生成图片
                $file = $this->getShopQrcode($uid);
            }
            //ServerResponse::message(Code::CODE_INTERNAL_ERROR,'小程序未正式上线！无法获取小程序码');
            $file = request()->domain().str_replace("\\", '/', $file);
            ServerResponse::message(Code::CODE_SUCCESS,'',['url'=>$file]);
//        }catch (\Exception $exception){
//            ServerResponse::message(Code::CODE_INTERNAL_ERROR);
//        }
    }

    /**
     * 生成商品小程序二维码
     */
    protected function getShopQrcode($uid){
        $user = Db::name('user')->where(['uid'=>$uid])->find();
        $fiel = Env::get('root_path') . "public/uploads/user/weixin" . ".jpg";
        $postdata['scene']="{$uid}--";
        $postdata['width']=430;
        $postdata['page']='pages/index/index';

        $postdata['auto_color']=false;

        $postdata['line_color']=['r'=>'0','g'=>'0','b'=>'0'];
        $postdata['is_hyaline']=false;
        $post_data = json_encode($postdata);

        $url="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$this->get_token();
        $result=$this->http_post($url,$post_data);
        if(json_decode($result,true)){
            if(isset($result['errcode'])){
                exit('错误');
            }
        }
// 保存二维码
        $res = file_put_contents($fiel,$result);
        $path = Env::get('root_path') . 'public/uploads/user/';
        $pathUser = Env::get('root_path') . "public/uploads/user/user_".$uid.'.jpg';
        if(!file_exists($pathUser)){
            //用户头像存在
            $image = $user['headimgurl'];
            $file = $this->download_remote_pic($image,$path,$uid);
            $avatar = $path.$file;
        }else{
            $avatar = $pathUser;
        }

        $avatarYuan = $this->yuanjiao($avatar,Env::get('root_path') . 'public/uploads/user/');
        //生成新的店铺小程序二维码
        if (!empty($avatar)) {
            $imgArr['qrcode']['left'] = 115;
            $imgArr['qrcode']['top'] = 115;
            $imgArr['qrcode']['width'] = "200";
            $imgArr['qrcode']['height'] = "200";
            $imgArr['qrcode']['opacity'] = "100";
            $imgArr['qrcode']['url'] = $avatarYuan;
            $imgArr['qrcode']['stream'] = 0;
            $imgArr['qrcode']['center'] = 0;
        }
        $config = array(
            'text' => [],//文字
            'image' => $imgArr,//图片
            'background' => $fiel, //背景图-》对应的就是小程序二维码
        );
        // 生成文件
        $returnFile = Env::get('root_path') . "public/uploads/user/qrcode_" .$uid . ".png";
        createPoster2($config, $returnFile);
        return "/uploads/user/qrcode_" .$uid. ".png";
    }

    protected function download_remote_pic($url,$path,$uid){
        $header = [
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:45.0) Gecko/20100101 Firefox/45.0',
            'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
            'Accept-Encoding: gzip, deflate',
        ];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $data = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($code == 200) {//把URL格式的图片转成base64_encode格式的！
            $imgBase64Code = "data:image/jpeg;base64," . base64_encode($data);
        }
        $img_content=$imgBase64Code;//图片内容
        //echo $img_content;exit;
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $img_content, $result)) {
            $type = $result[2];//得到图片类型png?jpg?gif?
            $file = 'user_'.$uid.".jpg";
            $new_file = $path.$file;
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $img_content)))) {
                return $file;
            }
        }
    }


    /*
     * 将图片切成圆角
     */
    protected function yuanjiao($imgpath,$path){
        $ext= pathinfo($imgpath);
        $dest_path = $path.uniqid().'.png';
        $src_img = null;
        switch($ext['extension']) {
            case 'jpg':
                $src_img = imagecreatefromjpeg($imgpath);
                break;
            case 'png':
                $src_img = imagecreatefrompng($imgpath);
                break;
        }
        $wh= getimagesize($imgpath);
        $w= $wh[0];
        $h= $wh[1];
        $w= min($w, $h);
        $h=$w;
        $img = imagecreatetruecolor($w, $h);
//这一句一定要有
        imagesavealpha($img, true);
//拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
        imagefill($img, 0, 0, $bg);
        $r = $w / 2; //圆半径
        $y_x = $r; //圆心X坐标
        $y_y = $r; //圆心Y坐标
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($src_img, $x, $y);
                if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                    imagesetpixel($img, $x, $y, $rgbColor);
                }
            }
        }
        imagesavealpha($img, true);
        imagepng($img, $dest_path);
        imagedestroy($img);
        // unlink($url);
        return $dest_path;
    }

    protected function http_post($url, $param, $post_file = false)
    {

        $oCurl = curl_init();

        if (stripos($url, "https://") !== FALSE) {

            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);

            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);

            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1

        }

        if (PHP_VERSION_ID >= 50500 && class_exists('\CURLFile')) {

            $is_curlFile = true;

        } else {

            $is_curlFile = false;

            if (defined('CURLOPT_SAFE_UPLOAD')) {

                curl_setopt($oCurl, CURLOPT_SAFE_UPLOAD, false);

            }

        }

        if (is_string($param)) {

            $strPOST = $param;

        } elseif ($post_file) {

            if ($is_curlFile) {

                foreach ($param as $key => $val) {

                    if (substr($val, 0, 1) == '@') {

                        $param[$key] = new \CURLFile(realpath(substr($val, 1)));

                    }

                }

            }

            $strPOST = $param;

        } else {

            $aPOST = array();

            foreach ($param as $key => $val) {

                $aPOST[] = $key . "=" . urlencode($val);

            }

            $strPOST = join("&", $aPOST);

        }

        curl_setopt($oCurl, CURLOPT_URL, $url);

        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($oCurl, CURLOPT_POST, true);

        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);

        $sContent = curl_exec($oCurl);

        $aStatus = curl_getinfo($oCurl);

        curl_close($oCurl);

        if (intval($aStatus["http_code"]) == 200) {

            return $sContent;

        } else {

            return false;

        }

    }

    // 获取access_token

    protected function get_token()
    {

//        $config = unserialize($this->baseConfig['app_pay']);
//        $authname = 'wechat_access_token' . $config['appid'];
//        $token = cache($authname);
//        if ($token) {
//
//            return $token;
//
//        }

        $api = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx634330702a3ffecd&secret=abe0791a91e7b3e4b57d5f8f353a71a3";

        $res = curl_get_contents($api);

        $json = json_decode($res, true);
        $access_token = $json['access_token'];

        $expire = $json['expires_in'] ? intval($json['expires_in']) - 100 : 7100;

        //cache($authname, $json['access_token'], $expire);


        return $access_token;

    }
}