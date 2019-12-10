<?php

namespace app\api\controller\v1;

use hg\CodeMsg;

use think\Db;

use think\Exception;

use think\Request;

use app\api\controller\Send;

use app\api\controller\Oauth;

use think\facade\Cache;

use hg\Code;

use \app\common\model\Config;

use GuzzleHttp\Client;

use hg\ServerResponse;



/**

 * 生成token

 */



class Token

{

    use Send;



    /**

     * 请求时间差

     */

    public static $timeDif = 10000;



    public static $accessTokenPrefix = 'accessToken_';

    public static $refreshAccessTokenPrefix = 'refreshAccessToken_';

    public static $expires = 60 * 60 * 24 * 30;

    public static $refreshExpires = 60 * 60 * 24 * 30;   //刷新token过期时间

    public function _initialize()

    {

        Request::hook('baseConfig', 'initialize');

    }



    /**

     * 测试appid，正式请数据库进行相关验证

     */

    public static $appid = 'tp5restfultest';

    /**

     * appsercet

     */

    public static $appsercet = '123456';



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

        ServerResponse::message(Code::CODE_INTERNAL_ERROR,$appId);

        return false;

    }



    /**
     * 小程序用户登陆授权
     * @param Request $request
     * @return ServerResponse
     */
    public function token(Request $request)
    {
        //echo base64_encode('tp5restfultest:9rF2u5DJbRzqCjXKQnxOhf58tScW3YHp:1');die
        try {
            //参数验证
            $validate = new \app\api\validate\Token;
            $data = $request->param();
            if (!$validate->check($data)) {
                return ServerResponse::message(Code::CODE_UNAUTHORIZED, $validate->getError());
            }
            $app_pay = config('config.site')['weixin'];
            $app_pay = unserialize($app_pay);
            $sessionKey = $this->getSessionKey($app_pay['appid'],$app_pay['secret'],$data['code']);
            if($sessionKey){
                //解密数据
                $pc = new \weixin\WXBizDataCrypt($app_pay['appid'], $sessionKey);
                $errCode = $pc->decryptData($data['encryptedData'], $data['iv'], $userData );
                if($errCode == 0){
                    Db::startTrans();
                    //解密成功
                    $userInfo = json_decode($userData,true);
                    $openid = $userInfo['openId'];
                    //判断用户是否首次登陆
                    $user = Db::name('user')->where(['openid' => $openid])->find();
                    if (!$user) {
                        //添加用户信息
                        $parent_id = $data['parent_id'] ?? 0;
                        $agent_id = $data['agent_ids'] ?? 0;
                        if($parent_id){
                            //判断上级是否是代理
                            $userInfoss = Db::name('user')->where('uid',$parent_id)->find();
                            $agentInfo = Db::name('agent')->where('openid',$userInfoss['openid'])->find();
                            if($agentInfo) $agent_id = $agentInfo['id'];
                        }
                        $addData = [

                            'openid' => $openid,

                            'nickname' => base64_encode($userInfo['nickName']),

                            'headimgurl' => $userInfo['avatarUrl'],

                            'reg_time' => date('Y-m-d H:i:s'),

                            'login_time' => date('Y-m-d H:i:s'),

                            'parent_id'=>$parent_id,
                            'agent_id'=>$agent_id,

                        ];

                        $res = db('user')->insertGetId($addData);

                        if(!$res){

                            Db::rollback();

                            ServerResponse::message(Code::CODE_INTERNAL_ERROR,CodeMsg::USER_CREATE_ERROR);

                        }

                        //记录分销信息

                        if($parent_id){

                            //记录当前级别的信息

                            $r_one = db('user_log')->insertGetId(['uid'=>$parent_id,'level'=>1,'source_id'=>$res,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);

                            if(!$r_one){

                                Db::rollback();

                                ServerResponse::message(Code::CODE_INTERNAL_ERROR,CodeMsg::USER_CREATE_ERROR);

                            }

                            $one = db('user')->where(['uid'=>$parent_id])->find();

                            if($one['parent_id']){

                                //记录二级信息

                                $r_two = db('user_log')->insertGetId(['uid'=>$one['parent_id'],'level'=>2,'source_id'=>$res,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);

                                if(!$r_two){

                                    Db::rollback();

                                    ServerResponse::message(Code::CODE_INTERNAL_ERROR,CodeMsg::USER_CREATE_ERROR);

                                }

                            }

                        }

                        $uid = $res;

                        $user = $addData;

                        $user['uid'] = $uid;

                    } else {

                        //更新用户信息
                        $agent_id = $data['agent_ids'] ?? 0;
                        if($user['agent_id']){
                            $res = db('user')->where(['openid' => $openid])->update(['nickname' => base64_encode($userInfo['nickName']), 'headimgurl' => $userInfo['avatarUrl'], 'login_time' => date('Y-m-d H:i:s')]);
                        }else{
                            $res = db('user')->where(['openid' => $openid])->update(['nickname' => base64_encode($userInfo['nickName']), 'headimgurl' => $userInfo['avatarUrl'], 'login_time' => date('Y-m-d H:i:s'),'agent_id'=>$agent_id]);
                        }


                        if(!$res){

                            ServerResponse::message(Code::CODE_INTERNAL_ERROR,CodeMsg::USER_CREATE_ERROR);

                        }

                        $user['nickname'] = base64_decode($user['nickname']);

                    }

                    $user['appid'] = self::$appid;

                    $accessToken = self::setAccessToken($user);  //传入参数应该是根据手机号查询改用户的数据

                    Db::commit();

                    return ServerResponse::message(Code::CODE_SUCCESS, '', $accessToken);

                }else{

                    ServerResponse::message(Code::CODE_BAD_REQUEST, '数据解密失败');

                }

            }else{

                ServerResponse::message(Code::CODE_BAD_REQUEST, '数据解密失败');

            }

        } catch (Exception $exception) {

            Db::rollback();

            ServerResponse::message(Code::CODE_INTERNAL_ERROR);

        }

    }



    /**

     * 刷新token

     */

    public function refresh($refresh_token='',$appid = '')

    {

        $cache_refresh_token = Cache::get(self::$refreshAccessTokenPrefix.$appid);  //查看刷新token是否存在

        if(!$cache_refresh_token){

            return ServerResponse::message(Code::CODE_UNAUTHORIZED,'refresh_token is null');

            //return self::returnMsg(401,'fail','refresh_token is null');

        }else{

            if($cache_refresh_token !== $refresh_token){

                return ServerResponse::message(Code::CODE_UNAUTHORIZED,'refresh_token is error');

                //return self::returnMsg(401,'fail','refresh_token is error');

            }else{    //重新给用户生成调用token

                $data['appid'] = $appid;

                $accessToken = self::setAccessToken($data);

                return ServerResponse::message(Code::CODE_SUCCESS,'',$accessToken);

                //return self::returnMsg(200,'success',$accessToken);

            }

        }

    }



    /**

     * 参数检测

     */

    public static function checkParams($params = [])

    {

        //时间戳校验

        if(abs($params['timestamp'] - time()) > self::$timeDif){

            return ServerResponse::message(Code::CODE_UNAUTHORIZED,'请求时间戳与服务器时间戳异常','timestamp：'.time());

            //return self::returnMsg(401,'请求时间戳与服务器时间戳异常','timestamp：'.time());

        }



        //appid检测，这里是在本地进行测试，正式的应该是查找数据库或者redis进行验证

        if($params['appid'] !== self::$appid){

            return ServerResponse::message(Code::CODE_UNAUTHORIZED,'appid 错误');

            //return self::returnMsg(401,'appid 错误');

        }



        //签名检测

        $sign = Oauth::makeSign($params,self::$appsercet);

        if($sign !== $params['sign']){

            return ServerResponse::message(Code::CODE_SIGN_FAIL);

            //return self::returnMsg(401,'sign错误','sign：'.$sign);

        }

    }



    /**

     * 设置AccessToken

     * @param $clientInfo

     * @return int

     */

    protected function setAccessToken($clientInfo)

    {

        //生成令牌

        $accessToken = self::buildAccessToken();

        $refresh_token = self::getRefreshToken($clientInfo['appid']);



        $accessTokenInfo = [

            'access_token'  => base64_encode($clientInfo['appid'].':'.$accessToken.':'.$clientInfo['uid']),//访问令牌

            'expires_time'  => time() + self::$expires,      //过期时间时间戳

            'refresh_token' => $refresh_token,//刷新的token

            'refresh_expires_time'  => time() + self::$refreshExpires,      //过期时间时间戳

            'client'        => $clientInfo,//用户信息

        ];

        self::saveAccessToken($accessToken, $accessTokenInfo);  //保存本次token

        self::saveRefreshToken($refresh_token,$clientInfo['appid']);

        return $accessTokenInfo;

    }



    /**

     * 刷新用的token检测是否还有效

     */

    public static function getRefreshToken($appid = '')

    {

        return Cache::get(self::$refreshAccessTokenPrefix.$appid) ? Cache::get(self::$refreshAccessTokenPrefix.$appid) : self::buildAccessToken();

    }



    /**

     * 生成AccessToken

     * @return string

     */

    protected static function buildAccessToken($lenght = 32)

    {

        //生成AccessToken

        $str_pol = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789abcdefghijklmnopqrstuvwxyz";

        return substr(str_shuffle($str_pol), 0, $lenght);



    }



    /**

     * 存储token

     * @param $accessToken

     * @param $accessTokenInfo

     */

    protected static function saveAccessToken($accessToken, $accessTokenInfo)

    {

        //存储accessToken

        cache(self::$accessTokenPrefix . $accessToken, $accessTokenInfo, self::$expires);

    }



    /**

     * 刷新token存储

     * @param $accessToken

     * @param $accessTokenInfo

     */

    protected static function saveRefreshToken($refresh_token,$appid)

    {

        //存储RefreshToken

        cache(self::$refreshAccessTokenPrefix.$appid,$refresh_token,self::$refreshExpires);

    }

}