<?php
namespace app\api\controller;

use app\common\model\Staff as StaffItem;
use think\Controller;
use think\Request;
use app\common\controller\ApiController;
use hg\ServerResponse;
use hg\Code;
use think\Db;


class Staff extends ApiController
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
        parent::initialize();
		$this->request = $request;
        $this->init();
        
        
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
    
    public function index(){
        

        
    }


    /**
     * author: Jason
     *  获取员工个人信息
     */
    public function getInfo(){
        
        $info = StaffItem::where('id',$this->userInfo['id'])->field('id,name,phone,email,photo,cid,staff_sn,timestamp')->find();

        if( empty($info['photo']) ){
            $info['photo'] = '/static/images/userheader.png';
        }
        
        return_ajax(200,'成功',$info);
        
    }

    /**
     * author: Jason
     * 员工个人信息修改
     * 
     */
    public function modify(){

        $obj = new StaffItem();
        if( !empty($this->data['phone']) ){
            if( !phoneNum($this->data['phone']) ) return_ajax(40002,'手机号码不正确');
        }
        $info = StaffItem::get($this->userInfo['id']);

        if( $this->data['password'] == ''){
            $this->data['password'] = $info['password'];
        }else{
            $oldpassword = sp_password($this->data['oldpassword']);
            if( $oldpassword !== $info['password'] ){
                return_ajax(40001,'原密码不正确！');
            }
            $this->data['password'] = sp_password($this->data['password']);
        }
        $res = $obj->modifyInfo($this->data, $this->userInfo);
        if( $res ){
            return_ajax(200,'修改成功');
        }else{
            return_ajax(40002,$obj->error);
        }
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