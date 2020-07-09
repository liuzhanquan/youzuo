<?php
namespace app\common\controller;

use \think\Controller;
use \app\common\model\Admin;
use app\common\model\Order as OrderItem;
use app\common\model\Goods;
use app\common\model\OrderS;
use app\common\model\OrderSpec;
use app\common\model\Customer;
use app\common\model\Staff;
use app\common\model\DetectionItem;
use app\common\model\DetectionSon;
use app\common\model\DetectionSpec;
use think\Db;

/**
 * ApiController 入口文件基类，需要控制权限的控制器都应该继承该类
 */
class ApiController extends Controller{

	public $userInfo = array();
	public $userPower = array();
	public $data = array();
	public $config = array();
    public $power = array();
    //权限验证跳过模块
    public $skinPow = ['Staff','Index','Upload','Ordercheck'];

	public function initialize(){
        
        parent::initialize();
        $this->request = request();
        $this->data = rawPost();
        $this->setConfig();
        $this->checkLogin();
    }
    
    protected function setConfig(){
        $res = DB::name('config')->where('is_sys',1)->select();
//        $photoCdn = DB::name('config')->where('name','QNcdn')->value('value');
        foreach( $res as $item ){
            config($item['name'],$item['value']);
        }
//        config('PHOTOPATH',$photoCdn);
    }
	/**
	 * 登录验证
	 * @author Json
	 * @time   2019-12-18T13:15:43+0800
	 * @return [type]
	 */
	protected function checkLogin(){
        
        $data = request()->header('',null,'htmlspecialchars');
        if ( request()->isPost() ){
            if( empty( $data['userinfo'] ) ){
                return_ajax(40001,'请先登录');
            }

            $this->userInfo = userdecode($data['userinfo']);

        }else{
            return_ajax(40001,'禁止访问');
        }

	}




    /**
     * 删除方法
     * @return bool
     */
    public function del(){
        if(request()->isAjax()){
            $data = $this->request->param();
            $table = $this->table($data['table']);
            AdminLog($this->admin['id'],'删除了'.$table.'【 id ： '.$this->data['id'].'】');
            $res = DB::name($data['table'])->where('id',$data['id'])->delete();

            if(!$res){
                return $this->error('删除失败');
            }
            return $this->success('操作成功');
            
        }

    }

    /**
     * 删除操作记录返回
     * @return string
     */
    public function table($table){
        $name = '';
        switch( $table ){
            case 'goods':
                $name = '产品【'.$table.'】';
                break;

            case 'category':
                $name = '产品分类【'.$table.'】';
                break;

            case 'customer':
                $name = '客户【'.$table.'】';
                break;

            case 'cus_category':
                $name = '客户分类【'.$table.'】';
                break;

            case 'staff':
                $name = '员工【'.$table.'】';
                break;

            case 'sta_category':
                $name = '员工分类【'.$table.'】';
                break;

            case 'detection':
                $name = '检测流程列表【'.$table.'】';
                break;
            
            case 'detection_son':
                $name = '检测环节【'.$table.'】';
                break;

            default: 
                $name = $table;
                break;

        }
        return $name;
    }


    /**
     * 
     * sort排序修改
     * 
     */
    public function modifysort(){
        if(request()->isAjax()){
            $data = $this->request->param();
            $table = $this->table($data['table']);
            AdminLog($this->admin['id'],'修改了'.$table.'【 id ： '.$this->data['id'].'】排序');
            if( $data['field'] == 'sort' ){
                $update['sort'] = $data['value'];
            
                $res = DB::name($data['table'])->where('id',$data['id'])->update($update);
            }
            if( empty($res) ){
                return $this->error('删除失败');
            }
            return $this->success('操作成功');
            
        }

    }



    /**
     * author: Jason
     * 检测员工是否有权限查看修改订单
     */
    public function checkStarrPower(){
        $info = DetectionSon::where('id',$this->data['dsid'])->field('input_status,input_staff')->find();

        if( $info['input_status'] ){
             $power = json_html_decode( $info['input_staff'] );
             if( !in_array( $this->userInfo['id'], $power ) ){
                return_ajax(40001,'您没有权限!');
             }
        }
    }


    public function indexApi(){
        $where = [];
        $where[] = ['o.status','>',-1];
        $limit = 10;
        $page = 1;
        if( !empty($this->data['start_time']) && !empty($this->data['end_time']) ){
            $where[] = ['o.created_time','between',$this->data['start_time'].','.$this->data['end_time']];
        }else{
            if( !empty($this->data['start_time']) ){
                $where[] = ['o.created_time','>=',$this->data['start_time']];
            }
    
            if( !empty($this->data['end_time']) ){
                $where[] = ['o.created_time','<=',$this->data['end_time']];
            }
        }

        if( !empty( $this->data['detection'] ) ){
            $where[] = ['o.did', 'eq', ($this->data['detection']) ];
        }  
        if( !empty( $this->data['status'] ) ){
            $where[] = ['o.status', 'eq', ($this->data['status']-1) ];
        } 

        if( !empty( $this->data['like'] ) ){
            $where[] = ['o.order_sn|g.title|c.customer_name|d.name|o.remark', 'like', "%".$this->data['like']."%" ];
        } 


        if( !empty( $this->data['limit'] ) ){
            $limit = $this->data['limit'];
        }   
        if( !empty( $this->data['page'] ) ){
            $page = $this->data['page'];
        }   

        if( empty( $this->data['like'] )   ){
            $data = OrderItem::alias('o')
                            ->where($where)
                            ->order('o.created_time desc')
                            ->field('o.id,o.order_sn,o.cid,o.gid,o.gsid,o.s_type,o.did,o.spec,o.supplier,o.machine,o.composition,o.status,o.engding,o.remark,o.created_time,o.updated_time')
                            ->limit(($page-1)*$limit,($page)*$limit)
                            ->select();
        }else{
            $data = OrderItem::alias('o')
                            ->join('goods g','g.id = o.gid')
                            ->join('customer c','c.id = o.cid')
                            ->join('detection d','d.id = o.did')
                            ->where($where)
                            ->order('created_time desc')
                            ->field('o.id,o.order_sn,o.cid,o.gid,o.gsid,o.s_type,o.did,o.spec,o.supplier,o.machine,o.composition,o.status,o.engding,o.remark,o.created_time,o.updated_time')
                            ->limit(($page-1)*$limit,($page)*$limit)
                            ->select();
        }
        

        foreach( $data as $key=>$item ){
            $data[$key]['gsid'] = json_decode( html_entity_decode($item['gsid']) );
            $data[$key]['status_text'] = config('order_status')[$item['status']];
            $data[$key]['engding_text'] = config('engding_status')[$item['engding']];
            $current = $this->current($item['id'],$item['gsid']);
            $data[$key]['current'] = $current['name'];
            $data[$key]['status'] = $item['status']+1;
            // true 信息录入，false 查看

            $data[$key]['order_status'] = $this->orderStatusInfo($data[$key]['status']);
        }
        return_ajax(200,'成功', $data);
        
        
    }


    /**
     * author: Jason
     * 根据订单值，返回按钮信息
     */
    public function orderStatusInfo($status){

        $info = true;
        
        if( $status == 3 ){
            $info = false;
        }

        return $info;
    }


    /**
     * author: Jason
     * 查询订单当前还接，返回当前环节名称
     */
    public function current( $oid, $gsid ){

        //dump($gsid);exit();
        if( !is_array( $gsid ) ){
            $gsid = explode(',',$gsid);
        }
        $gsid = implode(',', $gsid );

        $arr = DetectionSon::where('id','in',$gsid)->field('id,d_son_sn,name,parent_id,type,input_status,input_staff,sort')->order('sort asc')->select();
        $status = 1;
        $power_status = 1;
        $res = [];
        $res['power'] = false;
        $res['name'] = '未开始';
        foreach( $arr as $key => $item ){
            //判断用户是否有权限
            $num = 0;
            $power = false;
            if( json_html_decode( $item['input_staff'] ) ){
                $power = in_array( $this->userInfo['id'] ,json_html_decode( $item['input_staff'] ));
            }
            if( $item['input_status'] == 0 ){
                $power = true;
            }
            
            $count = DB::name('order_s')->where(['oid'=>$oid,'dsid'=>$item['id']])->count();
            if( empty($count)  && $power_status ){
                $power_status = 0;
                $num = 0;
                $res['name'] = $item['name'];
            }else if( $power && $status ){
                $num = 1;
                $status = 0;
                $res['power'] = true;
                
            }
            $DS = DB::name('order_d_s')->where(['oid'=>$oid,'dsid'=>$item['id']])->field('status,remark')->find();
            $arr[$key]['status'] = $DS['status'];
            $arr[$key]['color'] = $num;
            
        }
        
        if(count($arr)){
            $info['son'] = $arr;
        }

        return $res;
    }

    /**
     * author: Json
     * 获取检测单环节信息
     */
    public function detectionSonApi(){

        if( empty($this->data['id']) ) return_ajax(40001,'参数错误');
        if( empty($this->data['dsid']) ) return_ajax(40001,'参数错误2');
        //权限检测
        $this->checkStarrPower();

        $info = OrderItem::where('id',$this->data['id'])->field('id,order_sn,cid,gid,did,spec')->find();
        
        $dSon = DetectionSon::where('id',$this->data['dsid'])->field('id,name,type')->find();
        $info['dsid'] = $this->data['dsid'];
        $info['dsid_name'] = $dSon['name'];
        $info['type'] = $dSon['type'];

        $son = OrderS::where(['oid'=>$this->data['id'],'dsid'=>$this->data['dsid']])->where('status','egt',0)->field('id,created_time,sort')->select();
        $info['son'] = $son;
        return_ajax(200,'成功',$info);
    }



    /**
     * author: Json
     * 检测单详细信息
     */
    public function orderInfoApi(){
        if( empty($this->data['id']) && empty($this->data['order_sn']) ) return_ajax(40001,'参数错误');

        if( !empty($this->data['id']) ){
            $where['id'] = $this->data['id'];
        }else{
            $where['order_sn'] = $this->data['order_sn'];
        }

        $info = OrderItem::where($where)->field('id,order_sn,cid,gid,did,spec,gsid,supplier,machine,composition,status,engding,remark,created_time,contract_sn,test_type,is_show')->find();
        
        $gsid = [];
        if( $info && $info['gsid'] ){
            $gsid = implode(',', json_decode( html_entity_decode($info['gsid']), true ) );
            
             $arr =  json_html_decode($info['gsid']);
             $narr = [];
            foreach($arr as $key=>$item){
                $narr[] = (int)$item;
            }
            $info['gsid'] = $narr;
        }

        $info['engding_text'] = config('engding_status')[$info['engding']];
        
        $info['is_show_arr'] = isShowStatus($info['is_show']);
        if( empty($info['gsid']) ){
            $info['gsid'] = [];
        }else{
            
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
                    // 红色，有权限流程未到
                    // $num = 2;
                    // 黄色，有权限未录入
                    $num = 1;
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
            $info['status'] = $info['status'] + 1;
            return_ajax(200,'成功',$info);
        }else{
            return_ajax(40001,'没有订单信息');
        }
        

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
        
        $this->checkStarrPower();
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

    public function unlinkPhoto(){
        $path =['/uploads/20191223/157706812416769.jpeg','/uploads/20191223/157706787273069.jpeg'];
            
        unlinkPhoto($path);

    }

     /**
     * 表单信息删除
     */
    public function specdelApi($id = 0,$dsid = 0,$sort = 0){

        if( empty($id) ) return_ajax(40001,'参数错误1');
        if( empty($dsid) ) return_ajax(40001,'参数错误2');

        $res = DB::name('order_s')->where(['oid'=>$id,'dsid'=>$dsid,'sort'=>$sort])->update(['status'=>-1]);
        DB::name('order_spec')->where( ['oid'=>$id,'dsid'=>$dsid,'sort'=>$sort] )->delete();
        if( $res ){
            return_ajax(200,'删除成功');
        }else{
            return_ajax(40001,'删除失败');
        }
    }





}
