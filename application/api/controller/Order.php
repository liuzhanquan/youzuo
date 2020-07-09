<?php
namespace app\api\controller;

use app\common\model\Order as OrderItem;
use app\common\model\Goods;
use app\common\model\OrderS;
use app\common\model\OrderSpec;
use app\common\model\Customer;
use app\common\model\CusCategory;
use app\common\model\Category;
use app\common\model\Staff;
use app\common\model\Detection;
use app\common\model\DetectionSon;
use app\common\model\DetectionSpec;
use think\Controller;
use think\Request;
use app\common\controller\ApiController;
use hg\ServerResponse;
use hg\Code;
use think\Db;


class Order extends ApiController
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
     * 复制检测单
     */
    public function copyOrder(){
        if( empty($this->data['id']) ) return_ajax(40001,'参数错误');
        $obj = new OrderItem;
        StaffLog($this->userInfo['id'],'复制检测单【'.$this->data['id'].'】');
        
        $res = $obj->copyOrder($this->data,$this->userInfo,1);
        if( $res ){
            return_ajax(200,'复制检测单成功',$res);
        }else{
            return_ajax(40002,'复制检测单失败',$obj->error());
        }

    }
  
    /**
     * author: Json
     * 检测单详细信息
     */
    public function orderInfo(){
        
        $this->orderInfoApi();

    }

    /**
     * author: Json
     * 获取检测单环节信息
     */
    public function detectionSon(){

        $this->detectionSonApi();
    }

    /**
     * author: Json
     * 获取客户信息
     */
    public function customerList(){
        $limit = 10;
        $page = 1;
        $where[] = ['status','eq',1];
        //if( empty($this->data['id']) ) return_ajax(40001,'参数错误');
        if( $this->data['id'] ){
            $narr = [];
            $info = CusCategory::where('id',$this->data['id'])->field('parent_id')->find();
           
            if( $info['parent_id'] == 0 ){
                $arr = CusCategory::where('parent_id',$this->data['id'])->field('id')->select();
                if($arr){
                    foreach( $arr as $item ){
                        $narr[] = $item['id'];
                    }
                }
            }
            $narr[] = $this->data['id'];
            $where[] = ['cid','in',implode(',',$narr)];
            
        }
        
        if( !empty( $this->data['like'] ) ){
            $where[] = ['customer_sn|customer_name|province|city|county|address', 'like', "%".$this->data['like']."%" ];
        } 
        
        if( !empty( $this->data['limit'] ) ){
            $limit = $this->data['limit'];
        }   
        if( !empty( $this->data['page'] ) ){
            $page = $this->data['page'];
        }  


        $customer = Customer::where($where)->limit(($page-1)*$limit,($page)*$limit)->field("id,cid,customer_sn,customer_name,province,city,county,address,name,phone")->select();

        if( $customer ){
            return_ajax(200,'成功',$customer);
        }else{
            return_ajax(40002,'失败');
        }

    }

    /**
     * 获取检测流程下的环节
     */
    public function getdetectionSon(){
        $data = $this->data;
        $list = DetectionSon::where(['parent_id'=>$data['id'],'status'=>1])->order('sort asc')->field('id,d_son_sn,name')->select();
        
        
        if( $list ){
            return_ajax(200,'成功',$list);
        }else{
            return_ajax(40002,'失败');
        }
    }



    /**
     * author: Json
     * 获取客户信息
     */
    public function goodsList(){
        $limit = 10;
        $page = 1;

        $where[] = ['status','eq',1];
        //if( empty($this->data['id']) ) return_ajax(40001,'参数错误');
        if( $this->data['id'] ){
            $narr = [];
            $info = Category::where('id',$this->data['id'])->field('parent_id')->find();
            if( $info['parent_id'] == 0 ){
                $arr = Category::where('parent_id',$this->data['id'])->field('id')->select();
                if($arr){
                    foreach( $arr as $item ){
                        $narr[] = $item['id'];
                    }
                }
            }
            $narr[] = $this->data['id'];
            $where[] = ['cid','in',implode(',',$narr)];
            
        }
        if( !empty( $this->data['like'] ) ){
            $where[] = ['good_sn|title|content', 'like', "%".$this->data['like']."%" ];
        } 

        if( !empty( $this->data['limit'] ) ){
            $limit = $this->data['limit'];
        }   
        if( !empty( $this->data['page'] ) ){
            $page = $this->data['page'];
        } 
        
        $customer = Goods::where($where)->limit(($page-1)*$limit,($page)*$limit)->field('id,cid,title,good_sn')->select();

        if( $customer ){
            return_ajax(200,'成功',$customer);
        }else{
            return_ajax(40002,'失败');
        }

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
        $state = $obj->saveData1($this->data);
            
        if($state){
            return_ajax(200,'成功');
        }else{
            return_ajax(40001,$obj->getError());
        }


    }


    public function delete(){
            if( empty($this->data['id']) ) return_ajax(40001,'参数错误');
            
            $count = OrderS::where('oid',$this->data['id'])->count();
            if( $count ){
                return_ajax(40001,'订单进行中，无法删除');
            }
            StaffLog($this->userInfo['id'],'删除检测单【'.$this->data['id'].'】信息');
            $res = DB::name('order')->where('id',$this->data['id'])->delete();

            if(!$res){
                return_ajax(40001,'删除失败');
            }
            return_ajax(200,'操作成功');
    }


    // 查询是否有权限添加检测单
    public function checkuseradd(){
        //$model = $this->request->model(); // 控制器
        $topKey = $this->request->controller(); // 控制器
        $action = 'addorder'; // 操作方法

        if( !in_array( $topKey, $this->userPower['model'] ) ){
            return_ajax(40002,'没有权限!',false);
        }
        
        if( !in_array( $topKey.'/'.$action, $this->userPower['action'] ) ){
            return_ajax(40002,'没有权限!!',false);
        }
        
        return_ajax(200,'成功',true);
    }
    
    /**
     * author: Jason
     * 添加检测单
     */
    public function addOrder(){
        $obj = new OrderItem();
        if( empty($this->data['cid']) ) return $this->error('客户不能为空');
        if( empty($this->data['gid']) ) return $this->error('产品不能为空！');
        if( empty($this->data['did']) ) return $this->error('检测流程不能为空！');
        //是否显示， 不传递，默认全部显示
        
        
        //是否必填，不传递，默认非必选
        if( empty($this->data['required_status']) ) $this->data['required_status'] = [];

        if( empty($this->data['gsid']) ){
            $dbInfo = DB::name('detection_son')->where('parent_id',$this->data['did'])->field('id')->select();
            $dsid = [];
            foreach( $dbInfo as $item ){
                $dsid[] = $item['id']."";
            }
            $this->data['gsid'] = $dsid;
        }
        
        
        if(!empty($this->data['id'])){
            // 编辑
            StaffLog($this->userInfo['id'],'修改检测单【'.$this->data['order_sn'].'】信息');
            
            $state = $obj->saveData1($this->data,'edit');
        }else{
            if( empty($this->data['is_show']) ) $this->data['is_show'] = ['composition','supplier','machine','contract_sn','remark','test_type'];
            $state = $obj->saveData1($this->data,'add',$this->userInfo);
            // 新增
            StaffLog($this->userInfo['id'],'新增检测单id【'.$state.'】');
        }
        if($state){
            return_ajax(200,'成功',$state);
        }else{
            return_ajax(40001,$obj->getError());
        }
    }

    

    /**
     * author: Jason
     * 检测单查询
     */
    public function numberGet(){
        if( empty($this->data['order_sn']) ) return_ajax(40001,'参数错误');

        $info = OrderItem::where('order_sn',$this->data['order_sn'])->field('id,order_sn,cid,gid,spec,gsid')->find();
        $gsid = [];
        if( $info ){
            $gsid = implode(',', json_decode( html_entity_decode($info['gsid']), true ) );
        }
        
        $arr = [];
        
        $arr = DetectionSon::where('id','in',$gsid)->field('id,d_son_sn,name,parent_id,type,input_status,input_staff,sort')->order('sort asc')->select();
        $status = 1;
        foreach( $arr as $key => $item ){
            //判断用户是否有权限
            $power = false;
            if( json_html_decode( $item['input_staff'] ) ){
                $power = in_array( $this->userInfo['id'] ,json_html_decode( $item['input_staff'] ));
            }
            if( $item['input_status'] == 0 ){
                $power = true;
            }
            
            $count = DB::name('order_s')->where(['oid'=>$info['id'],'dsid'=>$item['id']])->count();
            if( $count && $power ){
                $num = 0;
                
            }else if( $power && $status == 1 ){
                $num = 1;
                $status = 0;
            }else if( $status == 0 && $power ){
                $num = 2;
            }else{
                $num = 3;
            }
            $arr[$key]['color'] = $num;
            
        }
        if(count($arr)){
            $info['son'] = $arr;
        }
        
        
        return_ajax(200,'成功',$info);

    }
    

    



	/**
	 * 空方法
	 */
	public function _empty()
    {
        return ServerResponse::message(Code::CODE_BAD_REQUEST);
    }
}