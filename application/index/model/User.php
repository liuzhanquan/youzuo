<?php

namespace app\index\model;

use think\facade\Cache;
use app\common\library\wechat\WxUser;
use app\common\model\User as UserModel;
use app\common\exception\BaseException;
use app\common\model\Config;

class User extends UserModel{

	private $token;

    /**
     * 获取用户信息
     * @param $token
     * @return null|static
     * @throws \think\exception\DbException
     */
	public function getUser($token){

        return self::detail(['openid' => Cache::get($token)['openid']]);
	}
    /**
     * 用户登录
     * @param array $post
     * @return string
     * @throws BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function login($post)
    {
        if(!isset($post['code'])){
            throw new BaseException(['msg' => '参数传递错误']);
        }
        // 微信登录 获取session_key
        $session = $this->wxlogin($post['code']);
        // 自动注册用户
        $referee_id = isset($post['referee_id']) ? $post['referee_id'] : null;
        $userInfo = json_decode(htmlspecialchars_decode($post['user_info']), true);
        $user_id = $this->register($session['openid'], $userInfo, $referee_id);
        // 生成token (session3rd)
        $this->token = $this->token($session['openid']);
        // 记录缓存, 7天
        Cache::set($this->token, $session, 86400 * 7);
        return $user_id;
    }

	/**
     * 生成用户认证的token
     * @param $openid
     * @return string
     */
    private function token($openid)
    {
        return md5($openid . 'token_salt');
    }

    /**
     * 获取token
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }
    /**
     * 微信登录
     * @param $code
     * @return array|mixed
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    private function wxlogin($code)
    {
        // 获取当前小程序信息
        $config = Config::where('name','weixin')->value('value');
        $wxConfig = unserialize($config);
        // 微信登录 (获取session_key)
        $WxUser = new WxUser($wxConfig['appid'], $wxConfig['secret']);
        if (!$session = $WxUser->sessionKey($code))
            throw new BaseException(['msg' => 'session_key 获取失败']);
        return $session;
    }
    /**
     * 自动注册用户
     * @param $open_id
     * @param $data
     * @param int $referee_id
     * @return mixed
     * @throws \Exception
     * @throws \think\exception\DbException
     */
    private function register($open_id, $data, $referee_id = null)
    {
        // 查询用户是否已存在
        $user = self::detail(['openid' => $open_id]);
        $model = $user ?: $this;
        $data['openid'] = $open_id;
        // 用户昵称
        $data['nickname'] = preg_replace('/[\xf0-\xf7].{3}/', '', $data['nickname']);
        try {
            $this->startTrans();
            // 保存/更新用户记录
            if (!$model->allowField(true)->save($data))
                throw new BaseException(['msg' => '用户注册失败']);
            // 记录推荐人关系
            if (!$user && $referee_id > 0)
                // RefereeModel::createRelation($model['uid'], $referee_id);
                
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw new BaseException(['msg' => $e->getMessage()]);
        }
        return $model['uid'];
    }
}