<?php
namespace app\admin\controller;
use app\backman\model\Meal;
use \app\common\controller\AuthBack;
use \app\common\model\Category;
use app\common\model\Comment;
use \app\common\model\Order as OrderItem;
use \app\common\model\OrderS;
use \app\common\model\Customer;
use \app\common\model\Goods;
use \app\common\model\Staff;
use \app\common\model\Detection;
use \app\common\model\DetectionSon;
use \app\common\model\DetectionSpec;
use \app\common\model\OrderSpec;
use \app\backman\model\Delivery;
use \app\backman\model\AgentLevel;
use app\common\model\User;
use think\Db;

class Order extends AuthBack{


    public function initialize(){
        parent::initialize();
    }

    public function index(){
        $like       = isset($_GET['like']) ? $_GET['like'] : '';
        $customer   = isset($_GET['customer']) ? $_GET['customer'] : '';
        $goods      = isset($_GET['goods']) ? $_GET['goods'] : '';
        $detection  = isset($_GET['detection']) ? $_GET['detection'] : '';
        $status     = isset($_GET['status']) ? $_GET['status'] : '';
        $start_time = isset($_GET['start_time']) ? $_GET['start_time'] : '';
        $end_time   = isset($_GET['end_time']) ? $_GET['end_time'] : '';

        $where[] = ['o.id','neq',0];
        if(!empty(input('like'))){
            $where[] = ['o.order_sn','like',"%{".input('like')."}%"];
        }

        if( !empty(input('customer')) ){
            $where[] = ['o.cid','eq',input('customer')];
        }
        if( !empty(input('goods')) ){
            $where[] = ['o.gid','eq',input('goods')];
        }
        if( !empty(input('detection')) ){
            $where[] = ['o.did','eq',input('detection')];
        }
        if( !empty(input('status')) || input('status') === '0' ){
            $where[] = ['o.status','eq',input('status')];
        }
        
        if( !empty(input('start_time')) && !empty(input('end_time')) ){
            $where[] = ['o.created_time','between',input('start_time').','.input('end_time')];
        }else{
            if( !empty(input('start_time')) ){
                $where[] = ['o.created_time','>=',input('start_time')];
            }
    
            if( !empty(input('end_time')) ){
                $where[] = ['o.created_time','<=',input('end_time')];
            }
        }
        
        $totalNum   = OrderItem::alias('o')
                        ->join('customer c','c.id = o.cid')
                        ->join('goods g','g.id = o.gid')
                        ->join('detection d','d.id = o.did')
                        ->where($where)
                        ->count();
        $customer   = Customer::where('status',1)->field('id,customer_name')->select();
        $goods   = Goods::where('status',1)->field('id,title')->select();
        $detection   = Detection::where('status',1)->field('id,name')->select();
        if(request()->isAjax()){
            $page = isset($_GET['page']) ? $_GET['page'] : '1';
            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
            $startNum = ($page - 1) * $limit;
            $totalPage = ceil($totalNum/$limit);
            
            
            $list = OrderItem::alias('o')
                                ->join('customer c','c.id = o.cid')
                                ->join('goods g','g.id = o.gid')
                                ->join('detection d','d.id = o.did')
                                ->where($where)
                                ->field('o.id,o.order_sn,o.cid,o.gid,o.did,o.gsid,o.sid,o.s_type,o.spec,o.supplier,o.machine,o.composition,o.status,o.engding,o.remark,o.is_show,o.created_time,o.updated_time')
                                ->limit($startNum.','.$limit)
                                ->order('o.id desc')
                                ->select();
            if(empty($list)){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage,'count'=>$totalNum]);
            }elseif($page > $totalPage){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage+1,'count'=>$totalNum]);
            }else{
                $nList = array();
                if(!empty($list)){
                    foreach($list as $k=>$vo){
                        $nList[$k] = $vo;
                        $nList[$k]['customer_name'] = $vo['cid']['text'];
                        $nList[$k]['goods_title'] = $vo['gid']['text'];
                        $nList[$k]['detection_name'] = $vo['did']['text'];
                        $nList[$k]['staff'] = ($vo['s_type'] == 1 ? '员工:':'管理员:') .$vo['sid'];
                        $nList[$k]['op'] = url('option',['id'=>$vo['id']]);
                        $nList[$k]['lc'] = url('lcindex',['id'=>$vo['id'],'did'=>$vo['did']['value']]);
                    }
                }
                $return = [
                    'code'=>0,
                    'msg'=>'',
                    'count'=>$totalNum,
                    'data'=>$nList
                ];
                return json($return);
            }
        }else{
            
            $this->assign('totalNum',$totalNum);
            $this->assign('customer',$customer);
            $this->assign('detection',$detection);
            $this->assign('goods',$goods);
            return view();
        }
    }


    public function lcindex($id = 0, $did = 0){
        $model = new Detection;
        $model = new DetectionSon;
       
        $where['parent_id'] = $did;
        $info = OrderItem::get($id);
        $totalNum = DetectionSon::where($where)->count();
        if(request()->isAjax()){
            $page = isset($_GET['page']) ? $_GET['page'] : '1';
            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
            $startNum = ($page - 1) * $limit;
            $totalPage = ceil($totalNum/$limit);
            $info = OrderItem::where('id',$id)->value('gsid');
            $info = json_decode( html_entity_decode($info), true );
            
            if( $info ){
                foreach( $info as $item ){
                    $arr[] = $item;
                }
                $where['id'] = $arr;
                
            }
            $list = DetectionSon::where($where)->limit($startNum.','.$limit)->order('sort asc')->select();

            if(empty($list)){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage,'count'=>$totalNum]);
            }elseif($page > $totalPage){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage+1,'count'=>$totalNum]);
            }else{
                $nList = array();
                if(!empty($list)){
                    foreach($list as $k=>$vo){
                        $arr = [];
                        if( $vo['input_status'] ){
                            $staff = Staff::where('id','in',json_decode(html_entity_decode($vo['input_staff']),true))->field('name')->select();
                            foreach( $staff as $item ){
                                $arr[] = $item['name'];
                            }
                        }else{
                            $arr[] = '<span style="color:blue;">全部员工</span>';
                        }
                        //查询检测单下环节的信息
                        $DS = DB::name('order_d_s')->where(['oid'=>$id,'dsid'=>$vo['id']])->field('status,remark,updated_time')->find();
                        //重新组装数组
                        $nList[$k] = $vo;
                        $nList[$k]['status'] = $DS['status'];
                        $nList[$k]['updated_time'] = $DS['updated_time'];
                        $nList[$k]['input_staff'] = implode(',',$arr);
                        $nList[$k]['op'] = url('lcoption',['pid'=>$id,'id'=>$vo['id']]);
                        $nList[$k]['spec'] = url('spec',['id'=>$id,'sid'=>$vo['id']]);
                        $nList[$k]['content'] = html_entity_decode($vo['content']);
                    }
                }
                $return = [
                    'code'=>0,
                    'msg'=>'',
                    'count'=>$totalNum,
                    'data'=>$nList
                ];
                return json($return);
            }
        }else{
            $this->assign('totalNum',$totalNum);
            $this->assign('info',$info);
            return view();
        }
    }


    /**
     * 
     */
    public function getdetectionSon(){
        if( request()->isAjax() ){
            $data = $this->request->param();
            $list = DetectionSon::where(['parent_id'=>$data['id'],'status'=>1])->order('sort asc')->field('id,d_son_sn,name')->select();
            $nlist = [];
            
            
            $return = [
                'code'=>0,
                'msg'=>'',
                'data'=>$list
            ];
            return json($return);
        }
    }

   /**
    * 检测单添加修改
    * 
    */
    public function option($id = 0){
        if(!request()->isPost()){
            $customer   = Customer::where('status',1)->field('id,customer_name')->select();
            $goods      = Goods::where('status',1)->field('id,title')->select();
            $detection  = Detection::where('status',1)->field('id,name')->select();
            $info = OrderItem::get($id);
            $this->assign('info',$info);
            $this->assign('customer',$customer);
            $this->assign('detection',$detection);
            $this->assign('goods',$goods);
            
            return view();
        }else{
            $obj = new OrderItem();
            if( empty($this->data['cid']) ) return $this->error('客户不能为空');
            if( empty($this->data['gid']) ) return $this->error('产品不能为空！');
            if( empty($this->data['did']) ) return $this->error('检测流程不能为空！');
            if($this->data['id']){
                // 编辑
                AdminLog($this->admin['id'],'修改检测单【'.$this->data['order_sn'].'】信息');
                $state = $obj->saveData($this->data,'edit');
            }else{
                // 新增
                AdminLog($this->admin['id'],'新增检测单【'.$this->data['order_sn'].'】');
                $state = $obj->saveData($this->data,'add',$this->admin);
            }
            if($state){
                return $this->success('操作成功',url('index'));
            }else{
                return $this->error($obj->getError());
            }
        }
    }

    /**
     * author: Jason
     * 获取单个二维码
     */
    public function erweimaImg(){
        
        $data = $this->request->param();
        $obj = new OrderItem;
        $url = config('codeUrl');
        $res = $obj->create_qrcode($url,$data);
        // echo '<img src="http://'.$res['url'].'"/>';
        if($res){
            $return = [
                'code'=>0,
                'msg'=>'',
                'data'=>$res
            ];
            return json($return);
        }else{
            return $this->error($obj->getError());
        }

    }


    public function update_status(){
        $data = $this->request->param();
        $update['status'] = $data['status'];
        $update['engding'] = $data['engding'];
        $update['updated_time'] = date('Y-m-d H:i:s');
        AdminLog($this->admin['id'],'修改检测单【'.$data['id'].'】状态');
        $res = Db::name('order')->where('id',$data['id'])->update($update);
        if(!$res){
            return json(['code'=>0,'msg'=>'请求失败']);
        }
        return json(['code'=>1,'msg'=>'请求成功']);
    }

    public function update_d_status(){
        $data = $this->request->param();
        $update['status'] = $data['status'];
        $update['updated_time'] = date('Y-m-d H:i:s');
        AdminLog($this->admin['id'],'修改检测单环节【'.$data['oid'].'-'.$data['dsid'].'】状态');
        $res = Db::name('order_d_s')->where(['oid'=>$data['oid'],'dsid'=>$data['dsid']])->update($update);
        if(!$res){
            return json(['code'=>0,'msg'=>'请求失败']);
        }
        return json(['code'=>1,'msg'=>'请求成功']);
    }

    /**
     * 获取表单列表信息
     * @param int $id
     * @return \think\response\View|void
     */
    public function getson(){
        if( request()->isAjax() ){
            $data = $this->request->param();
            $list = OrderS::where(['oid'=>$data['oid'],'dsid'=>$data['dsid'],'status'=>1])->order('sort desc')->field('id,dsid,sort,created_time')->select();
            $nlist = [];
            foreach( $list as $key=>$item ){
                $list[$key]['link'] = '<a href="'.url('spec',['oid'=>$data['oid'],'dsid'=>$data['dsid'],'sort'=>$item['sort']]).'" > 表单信息</a>';
            }
            $return = [
                'code'=>0,
                'msg'=>'',
                'data'=>$list
            ];
            return json($return);
        }
    }

    /**
     * author: Json
     * 复制检测单
     */
    public function copy(){
        if( empty($this->data['id']) ) return $this->error('检测单ID为空');;
        $obj = new OrderItem;
        
        AdminLog($this->admin['id'],'复制检测单【'.$this->data['id'].'】');
        $res = $obj->copyOrder($this->data,$this->admin,2);
        if( $res ){
            return json(['code'=>1,'msg'=>'复制检测单成功']);
        }else{
            return json(['code'=>0,'msg'=>'复制检测单失败']);
        }

    }


    /**
     * 流程环节表单填写
     * @param int $id
     * @return \think\response\View|void
     */
    public function spec($id = 0,$sid = 0,$sort=0){
        if(!request()->isPost()){
            $order = OrderItem::get($id);
            $son = DetectionSon::get($sid);
            $info = DetectionSpec::where('d_son_sn',$son['d_son_sn'])->find();
            
            $list = [];
            if( !empty($info) ){
                $list = json_decode($info['spec'],true);
                foreach( $list as $key=>$item ){
                    
                    $val  = OrderSpec::where(['oid'=>$id,'class_name'=>$item['name'],'sort'=>$sort])->column('content');
                    
                    if( !empty( $val ) ){
                        if( $item['type'] === 'checkbox' ){
                            $val[0] = json_decode($val[0],true);
                        }
                        if( $item['type'] === 'picker' ){
                            $val[0] = json_html_decode($val[0]);
                            
                        }
                        if( $item['type'] === 'file' ){
                            $list[$key]['photo'] = json_decode( html_entity_decode($val[0]), true );
                        }
                        $list[$key]['value'] = $val[0];
                    }else{
                        $list[$key]['value'] = '';
                    }
                    
                }
            }
        
            $this->assign('order',$order);
            $this->assign('son',$son);
            $this->assign('info',$info);
            $this->assign('sort',$sort);
            $this->assign('list',$list);
            return view();
        }else{
            
            $obj = new OrderSpec();
            

            AdminLog($this->admin['id'],'编辑检测表单【'.$this->data['d_son_sn'].'】信息');
            $state = $obj->saveData($this->data);
            
            if($state){
                return $this->success('操作成功',url('lcindex',['id'=>$this->data['oid'],'did'=>$this->data['did']]));
            }else{
                return $this->error($obj->getError());
            }
        }
    }


    





}