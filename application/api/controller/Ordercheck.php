<?php
namespace app\api\controller;

use app\common\model\Goods;
use app\common\model\Order as OrderItem;
use app\common\model\OrderS;
use app\common\model\OrderDS;
use app\common\model\OrderSpec;
use app\common\model\DetectionSon;
use app\common\model\DetectionSpec;
use think\Controller;
use think\Request;
use app\common\controller\ApiController;
use hg\ServerResponse;
use hg\Code;
use think\Db;


// class Ordercheck extends ApiController
class Ordercheck
{	
	/**
     * @var \think\Request Request实例
     */
    protected $request;

    protected $clientInfo;
    public $data = array();
    public $config = array();

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
  //       parent::initialize();
		$this->request = $request;
        $this->init();
         $this->data = rawPost();
        $this->setConfig();
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
        

    protected function setConfig(){
        $res = DB::name('config')->where('is_sys',1)->select();
        $photoCdn = DB::name('config')->where('name','QNcdn')->value('value');
        foreach( $res as $item ){
            config($item['name'],$item['value']);
        }
        config('PHOTOPATH',$photoCdn);
    }

        
    public function index(){
        
        
        
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

    public function orderInfo(){
        if( empty($this->data['id']) && empty($this->data['order_sn']) ) return_ajax(40001,'参数错误');

        if( !empty($this->data['id']) ){
            $where['id'] = $this->data['id'];
        }else{
            $where['order_sn'] = $this->data['order_sn'];
        }

        $info = OrderItem::alias('o')
                            ->where($where)
                            ->field('o.id,o.order_sn,o.cid,o.gid,o.did,o.spec,o.gsid,o.supplier,o.machine,o.composition,o.status,o.engding,o.remark,o.created_time')
                            ->find();
        $gsid = [];
        
        if( empty($info['gid']['value']) ){
            return_ajax(201,'检测单信息为空',$info);
        }
        if( $info ){

            if($info['gid']){
                
                $goodinfo = DB::name('goods')->where('id',$info['gid']['value'])->field('photo,content')->find();
                $info['content'] = contentphotopath(html_entity_decode( $goodinfo['content']) );
                $info['photo'] = photo_arr_path(unserialize($goodinfo['photo']));
            }
            if( $info['gsid'] ){
                $gsid = implode(',', json_decode( html_entity_decode($info['gsid']), true ) );
            }else{
                $gsid = [];
            }

            $info['engding_text'] = config('engding_status')[$info['engding']];
            
            $info['status'] = $info['status'] + 1;
        }
        
        $arr = [];

        if(empty($this->data['op'])){
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
                $DS = DB::name('order_d_s')->where(['oid'=>$info['id'],'dsid'=>$item['id']])->field('status,remark')->find();
                $arr[$key]['status'] = $DS['status'];
                $arr[$key]['color'] = $num;
                $arr[$key]['name'] = html_entity_decode($item['name']);
                
            }
            if(count($arr)){
                $info['son'] = $arr;
            }
        }
        
        if($info){
            return_ajax(200,'成功',$info);
        }else{
            return_ajax(40001,'没有订单信息');
        }
    }
    
  
    /**
     * author: Json
     * 获取检测单环节信息
     */
    public function detectionSon(){

        if( empty($this->data['id']) ) return_ajax(40001,'参数错误');
        if( empty($this->data['dsid']) ) return_ajax(40001,'参数错误2');
        //权限检测
        // $this->checkStarrPower();

        $info = OrderItem::where('id',$this->data['id'])->field('id,order_sn,cid,gid,did,spec')->find();
        
        $dSon = DetectionSon::where('id',$this->data['dsid'])->field('id,name,type')->find();
        $info['dsid'] = $this->data['dsid'];
        $info['dsid_name'] = $dSon['name'];
        $info['type'] = $dSon['type'];

        $son = OrderS::alias('o')
                    ->join('staff s','s.id = o.sid')
                    ->where(['oid'=>$this->data['id'],'dsid'=>$this->data['dsid']])
                    ->where('o.status','egt',0)
                    ->field('o.id,o.oid,o.created_time,o.sort,o.status,o.sid,s.name')
                    ->select();
        $info['son'] = $son;
        return_ajax(200,'成功',$info);
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
     * 流程环节表单信息
     * @param int $id
     * @return \think\response\View|void
     */
    public function specApi(){

        if( empty($this->data['id']) ) return_ajax(40001,'参数错误');
        if( empty($this->data['dsid']) ) return_ajax(40001,'参数错误2');
        if( empty($this->data['sort']) && $this->data['sort'] != 0 ) return_ajax(40001,'参数错误2');
        //权限检测
        
        // $this->checkStarrPower();
        if( empty($this->data['sort']) && $this->data['sort'] != 0 ){
            $this->data['sort'] = 999999;
        }
        $info = OrderItem::where('id',$this->data['id'])->field('id,order_sn,cid,gid,did,spec')->find();
        $son = DetectionSon::where('id',$this->data['dsid'])->field('parent_id,d_son_sn,time_status')->find();
        $spec = DetectionSpec::where('d_son_sn',$son['d_son_sn'])->find();
        
        $list = [];
        $created_time = '';
        if( !empty($spec) ){
            $ctime = DB::name('order_s')->where(['oid'=> $this->data['id'],'dsid'=> $this->data['dsid']])->field('created_time')->find();
            if( $ctime ){
                $created_time = $ctime['created_time'];
            }
            
            $list = json_decode($spec['spec'],true);
            $list = spec_data_select($list ,$son['d_son_sn']);
            foreach( $list as $key=>$item ){
               
                $val  = OrderSpec::where(['oid'=>$this->data['id'],'class_name'=>$item['name'],'sort'=>$this->data['sort']])->column('content');
                
                

                
                if( !empty( $val ) ){
                    if( $item['type'] === 'checkbox' ){
                        $val[0] = json_decode($val[0],true);
                    }

                    if( $item['type'] === 'picker' ){
                        $val[0] = json_html_decode($val[0]);
                        if( !empty($val[0]['province']) ){
                            $arr[] = $val[0]['province'];
                            $arr[] = $val[0]['city'];
                            $arr[] = $val[0]['county'];
                            $val[0] = $arr;
                        }
                        
                    }

                    
                    $list[$key]['value'] = $val[0];
                    if( $item['type'] === 'www' ){
                        $list[$key]['value'] = html_entity_decode($val[0]);
                    }
                    if( $item['type'] === 'file' ){
                        $list[$key]['show'] = [];
                        $list[$key]['show'] =   json_decode( html_entity_decode($val[0]), true );
                        // $list[$key]['value'] = photo_arr_path( $list[$key]['show'] );
                        $list[$key]['value'] = [];
                        $new = [];
                        if( $list[$key]['show'] ){
                            foreach(photo_arr_path( $list[$key]['show'] ) as $i){
                                $new['url'] = $i;
                                $list[$key]['value'][] = $new;
                            }
                        }
                        
                    }


                    if( $item['type'] === 'textarea' ){
                        $list[$key]['value'] = contentphotopath2( html_entity_decode($val[0]) ) ;
                    }
                }else{
                    if( $item['type'] === 'radio' ){
                        $list[$key]['value'] = $item['placeholder'][0];
                    }else{
                      $list[$key]['value'] = '';  
                    }
                    
                }
                
            }
        }
        $info['did'] = $son['parent_id'];
        $info['oid'] = $this->data['id'];
        $info['dsid'] = $this->data['dsid'];
        $info['time_status'] = $son['time_status'];
        $info['created_time'] = $created_time;
        $info['sort'] = $this->data['sort'];
        $info['list'] = $list;

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