<?php
namespace app\api\controller;

use app\common\model\Goods;
use think\Controller;
use think\Request;
use app\common\controller\ApiController;
use hg\ServerResponse;
use hg\Code;
use think\Db;

$origin = request()->header('Origin');
if (!$origin) $origin = 'codecheck.c.qiema.cc';
header('Access-Control-Allow-Origin:'.$origin);
header('Access-Control-Allow-Credentials:true');
header('Access-Control-Allow-Methods:GET,POST,OPTIONS');
header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
header('Access-Control-Allow-Headers: content-type,token,dealerauth,mstoreauth,Authorization');

class Login
{	
	/**
     * @var \think\Request Request实例
     */
    protected $request;

    protected $clientInfo;

    /**
     * 不需要鉴权方法
     */
    protected $noAuth = [];

	/**
	 * 构造方法
	 * @param Request $request Request对象
	 */
	public function __construct(Request $request)
	{
        
		$this->request = $request;
        //$this->init();
        
		$this->uid = $this->clientInfo['uid'];
        $this->data = $request->param();
        
		//$this->openid = $this->clientInfo['openid'];

	}

	/**
	 * 初始化
	 * 检查请求类型，数据格式等
	 */
	public function init()
	{	
		//所有ajax请求的options预请求都会直接返回200，如果需要单独针对某个类中的方法，可以在路由规则中进行配置
		if($this->request->isOptions()){

			return ServerResponse::message(Code::CODE_BAD_REQUEST);
		}

    }
    


    public function login(){
        $data = input();
        empty($data['username']) ? return_ajax("登录账号不能为空",400) : $username = $data['username'];
        empty($data['password']) ? return_ajax("登录账号不能为空",400) : $password = $data['password'];

		$info = db('staff')->where('phone',$username)->find();

        if( empty($info) ){
			return_ajax(400,'账号不存在');
        }

        if( $info['status'] != 1 ){
			return_ajax(400,'账号已被冻结！');
        }else {
			$state = sp_compare_password($data['password'],$info['password']);
        }
		if( $state ){
			$userinfo = ['id'=>$info['id'],'log_time'=>time(),'sn'=>$info['staff_sn']];
            $userinfo = userencode($userinfo);
			return_ajax( 200, '登录成功', ["token"=>$userinfo,'name'=>$info['name']]);
		}else{
            return_ajax( 400, '密码不正确！');
        }
		return_ajax( 400, '登录失败');
            

    }

  
  /**
     * 递归获取终端代理下面的终端代理
     * @param int $user_id 当前会员
     * @param int $tuser_id 上级会员
     * @return array
     */
    protected function getUser($user_id = 0){
        $agent_id = 0;
        $user = Db::name('user')->where('uid',$user_id)->field('parent_id,agent_id')->find();
        if($user['agent_id']){
            $agent_id = $user['agent_id'];
            //return $user['agent_id'];

        }else{
            //判断当前会员是否有代理，有代理的话直接返回，没有代理的话往上无线查
            if($user['parent_id']){
                $agent_id = $this->getUser($user['parent_id']);
            }
        }

        return $agent_id;

    }

    
  


	/**
	 * 空方法
	 */
	public function _empty()
    {
        return ServerResponse::message(Code::CODE_BAD_REQUEST);
    }
}