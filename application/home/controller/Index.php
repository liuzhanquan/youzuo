<?php
namespace app\home\controller;

use app\common\model\Goods;
use app\common\model\Staff;
use app\common\services\CodeLogInDb;
use app\common\services\QemCodeCheck;
use app\common\model\Order;
use think\Controller;
use think\Request;
use app\common\controller\ApiController;
use hg\ServerResponse;
use hg\Code;
use think\Db;
use think\facade\App;


class Index extends Controller
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
        
		$this->uid = $this->clientInfo['uid'];
        
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


        return view();
    }

    /**
     * author: Jason
     * 获取员工权限
     */
    public function getPower(){


        return_ajax(200,'成功', [] );

        
    }

    // 核销录入订单信息
    public function checkOrder(){

    	$data = input();
	    $userInfo = userdecode($data['userInfo']);
		$orderM = new CodeLogInDb( $userInfo );
		$res = $orderM->order($data['keyword']);
		return_ajax($res['code'],$res['msg'],$res['data']);


    }

    // 获取核销录入信息
	public function orderList(){
    	$limit = input('limit',4);
    	$order = new Order();
    	$list = $order->where('staff_id',$this->userInfo['id'])->order('create_time desc')->field('id, goods_id, code, create_time')->paginate($limit)->toArray();
    	return_ajax(200,'成功', ['data'=>$list['data'],'count'=>$list['total']]);
	}

	public function getUserInfo(){
    	$info = Db::name('staff')->where('id',$this->userInfo['id'])->find();
    	if( $info ){
    		return_ajax(200, '成功', $info);
	    }else{
    		return_ajax(400, '用户不存在');
	    }
	}

	// 修改密码
	public function modifyPass(){

		$obj = new Staff();
		$info = Staff::get($this->userInfo['id']);

		if( $this->data['password'] == ''){
			$this->data['password'] = $info['password'];
			$this->data['password_show'] = $info['password_show'];
		}else{
			$oldpassword = sp_password($this->data['oldpassword']);
			if( $oldpassword !== $info['password'] ){
				return_ajax(40001,'原密码不正确！');
			}
			$this->data['password_show'] = $this->data['password'];
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
	 * 空方法
	 */
	public function _empty()
    {
        return ServerResponse::message(Code::CODE_BAD_REQUEST);
    }
}