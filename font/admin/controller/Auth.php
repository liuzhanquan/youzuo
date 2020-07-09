<?php

namespace app\admin\controller;
use \think\Controller;
use \app\common\model\Admin;
use \app\common\model\AdminLog;
use \think\captcha;

class Auth extends Controller{

	public function login(){
		if(!request()->isPost()){
	        return view();
    	}else{
    		$data = request()->param('',null,'htmlspecialchars');
    		if(!captcha_check($data['captcha'])){
				return $this->error('验证码错误！');
	        }
            $user = Admin::where(['username'=>$data['name']])->find();
            if(empty($user)){
                return $this->error('该管理账号不存在');
            }
            if(!sp_compare_password($data['password'],$user->password,$user->authkey)){
                return $this->error('密码错误');
            }
            if (!$user->status) {
                return $this->error('禁止登陆');
            }
            $logInfo = [
                'user_id'=>$user->id,
                'desc'=>'用户登陆',
                'action'=>request()->path(),
                'timestamp'=>date('Y-m-d H:i:s'),
            ];

            $state = AdminLog::insertGetId($logInfo);
            if(!$state){
                return $this->error('登陆失败');
            }else{
                cookie('userId',$user->id);
                cookie('login_time',request()->time());
                return $this->success('登陆成功',url('/'));
            }
    	}
	}

    public function logout(){
        $cookie_admin = cookie('userId');
        if(!request()->isAjax()){
            return json(['code'=>0]);
        }
        if(!empty($cookie_admin)){
            cookie('userId',null);
            return $this->success('退出成功',url('auth/login'));die;
        }else{
            $this->redirect(url('auth/login'));
            exit;
        }
    }
}