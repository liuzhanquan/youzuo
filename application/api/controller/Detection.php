<?php
namespace app\api\controller;

use app\common\model\Order as OrderItem;
use app\common\model\Goods;
use app\common\model\OrderS;
use app\common\model\OrderSpec;
use app\common\model\Customer;
use app\common\model\Staff;
use app\common\model\Detection as DetectionItem;
use app\common\model\DetectionSon;
use app\common\model\DetectionSpec;
use think\Controller;
use think\Request;
use app\common\controller\ApiController;
use hg\ServerResponse;
use hg\Code;
use think\Db;


class Detection extends ApiController
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
        $this->indexApi();
        
    }

    
    /**
     * author: Json
     * 检测单详细信息
     */
    public function orderInfo(){
        
        $this->orderInfoApi();

    }

    /**
     * 流程环节表单信息
     * @param int $id
     * @return \think\response\View|void
     */
    public function spec(){
        $this->specApi();
    }
    
    /**
     * author: Json
     * 获取检测单环节信息
     */
    public function detectionSon(){

        $this->detectionSonApi();
    }
  

    /**
     * author: Jason
     * 自定义表单录入
     */
    public function specadd(){
        if( empty($this->data['oid']) ) return_ajax(40001,'参数错误');
        if( empty($this->data['did']) ) return_ajax(40001,'参数错误2');
        if( empty($this->data['dsid']) ) return_ajax(40001,'参数错误2');
        if( empty($this->data['sort']) && $this->data['sort'] != 0 ) return_ajax(40001,'参数错误4');
        
        
        StaffLog($this->userInfo['id'],'添加检测单表单信息【'.$this->data['oid'].'-'.$this->data['did'].'-'.$this->data['sort'].'】');
        $obj = new OrderSpec;
        
        $state = $obj->saveData1($this->data,$this->userInfo);
            
        if($state){
            return_ajax(200,'成功');
        }else{
            return_ajax(40001,$obj->getError());
        }


    }

    /**
     * 复制检测流程
    */
    public function copy( $id = 0 ){
            if( empty($this->data['id']) ){
                return $this->error('参数错误！');
            }

            $obj = new DetectionItem;
            StaffLog($this->userInfo['id'],'复制检测流程【'.$this->data['id'].'】');
            $state = $obj->copy($this->data['id']);

            if($state){
                return_ajax(200,'成功');
            }else{
                return_ajax(40001,$obj->getError());
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