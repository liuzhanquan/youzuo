<?php
namespace app\api\controller;

use app\common\model\Goods;
use app\common\model\Staff;
use app\common\services\QemCodeCheck;
use think\Controller;
use think\Request;
use app\common\controller\ApiController;
use hg\ServerResponse;
use hg\Code;
use think\Db;
use think\facade\App;


class Index extends ApiController
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
        $power['power'] = $this->userPower['power'];
        return_ajax(200,'成功', $power );

        
    }

    /**
     * author: Jason
     * 获取员工权限
     */
    public function getPower(){


        return_ajax(200,'成功', [] );

        
    }

    public function checkOrder(){

    	$data = $this->data;
    	$qemCodeCheck = new QemCodeCheck('http://120.76.23.183:838','YOjGScn9tZK2AcKG');
		$codeCheck = $qemCodeCheck->codeCheck($data['keyword']);
		$res = $codeCheck;
		if( $res['code'] != 400 ){

		}else{
			return_ajax(400,$res['msg']);
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