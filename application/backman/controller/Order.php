<?php 
namespace app\backman\controller; 
use app\backman\model\Meal; 
use \app\common\controller\AuthBack; 
use \app\common\model\Category; 
use \app\common\model\OrderTestType; 
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
use think\facade\App;
require_once App::getRootPath().'/extend/PHPExcel/PHPExcel.php';

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

            // 检测单号、客户名称、产品编号、名称、规格、建单人、备注

            $where[] = ['o.order_sn|c.customer_name|g.good_sn|g.title|o.spec|o.sid|o.remark','like',"%".input('like')."%"];

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

        
        if( !empty(input('customer')) || !empty(input('goods')) || !empty(input('detection')) || !empty(input('like')) ){
            $totalNum   = OrderItem::alias('o')

                        ->join('customer c','c.id = o.cid')

                        ->join('goods g','g.id = o.gid')

                        ->join('detection d','d.id = o.did')

                        ->where($where)

                        ->count();
        }else{
            $totalNum   = OrderItem::alias('o')
            
                        ->where($where)

                        ->count();
        }

        $customer   = Customer::where('status',1)->field('id,customer_name')->select();

        $goods   = Goods::where('status',1)->field('id,title')->select();

        $detection   = Detection::where('status',1)->field('id,name')->select();

        if(request()->isAjax()){

            $page = isset($_GET['page']) ? $_GET['page'] : '1';

            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';

            $startNum = ($page - 1) * $limit;

            $totalPage = ceil($totalNum/$limit);

            

            if( !empty(input('customer')) || !empty(input('goods')) || !empty(input('detection')) || !empty(input('like')) ){
                $list = OrderItem::alias('o')

                                ->join('customer c','c.id = o.cid')

                                ->join('goods g','g.id = o.gid')

                                ->join('detection d','d.id = o.did')

                                ->where($where)

                                ->field('o.id,o.order_sn,o.cid,o.gid,o.did,o.gsid,o.sid,o.s_type,o.spec,o.supplier,o.machine,o.composition,o.status,o.engding,o.remark,o.is_show,o.created_time,o.updated_time,o.test_type,o.contract_sn')

                                ->limit($startNum.','.$limit)

                                ->order('o.id desc')

                                ->select();

            }else{
                $list = OrderItem::alias('o')

                                ->where($where)

                                ->field('o.id,o.order_sn,o.cid,o.gid,o.did,o.gsid,o.sid,o.s_type,o.spec,o.supplier,o.machine,o.composition,o.status,o.engding,o.remark,o.is_show,o.created_time,o.updated_time,o.test_type,o.contract_sn')

                                ->limit($startNum.','.$limit)

                                ->order('o.id desc')

                                ->select();

            }

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

                        $nList[$k]['staff'] = ($vo['s_type'] == 1 ? '员工:':'管理员:') .get_order_cname($vo['sid'],$vo['s_type']);

                        $nList[$k]['test_type_text'] = $vo['test_type']['text'];

                        $nList[$k]['op'] = url('option',['id'=>$vo['id']]);

                        $nList[$k]['print'] = url('idnex',['id'=>$vo['id'],'sid'=>$vo['did']['value']]);

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

     * 获取检测流程下的环节

     */

    public function getdetectionSon(){

        if( request()->isAjax() ){

            $data = $this->request->param();

            $list = DetectionSon::where(['parent_id'=>$data['id'],'status'=>1])->order('sort asc')->field('id,d_son_sn,name')->select();

            

            

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

            $test_type  = OrderTestType::field('id,name')->select();

            $info = OrderItem::get($id);



            $this->assign('info',$info);

            $this->assign('customer',$customer);

            $this->assign('detection',$detection);

            $this->assign('goods',$goods);

            $this->assign('test_type',$test_type);

            // dump($info);exit();

            return view();

        }else{

            $obj = new OrderItem();

            // if( empty($this->data['cid']) ) return $this->error('客户不能为空');

            // if( empty($this->data['gid']) ) return $this->error('产品不能为空！');

            // if( empty($this->data['did']) ) return $this->error('检测流程不能为空！');

            // if( empty($this->data['gsid']) ) return $this->error('检测流程环节不能为空！');



            if( !empty($this->data['did']) ){

                if( empty($this->data['gsid']) ) return $this->error('检测流程环节不能为空！');

            }



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

    public function update_s_status(){

        $data = $this->request->param();

        $update['status'] = $data['status'];

        $update['updated_time'] = date('Y-m-d H:i:s',time());

        AdminLog($this->admin['id'],'修改检测单表单【'.$data['id'].'】状态');

        $res = Db::name('order_s')->where(['id'=>$data['id']])->update($update);

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

            $list = OrderS::where(['oid'=>$data['oid'],'dsid'=>$data['dsid']])->where('status','egt',0)->order('sort desc')->field('id,did,dsid,sort,created_time,status')->select();

            $nlist = [];

            foreach( $list as $key=>$item ){

                $list[$key]['link'] = '<a class="layui-btn layui-btn-primary" href="'.url('spec',['oid'=>$data['oid'],'dsid'=>$data['dsid'],'sort'=>$item['sort']]).'" > 表单信息</a>';

                $list[$key]['modify'] = '<a class="modify layui-btn layui-btn-primary" data-method="modify" num="'.$item['id'].'" status="'.$item['status'].'" > 审核</a>';

                $list[$key]['del'] = '<a class="layui-btn layui-btn-primary" href="'.url('specdel',['oid'=>$data['oid'],'dsid'=>$data['dsid'],'sort'=>$item['sort'],'did'=>$item['did']]).'" > 删除</a>';

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

     * 表单信息删除

     */

    public function specdel($id = 0,$dsid = 0,$sort = 0,$did=0){



        if( empty($id) ) return $this->error('参数错误1');

        if( empty($dsid) ) return $this->error('参数错误2');



        AdminLog($this->admin['id'],'删除录入表单信息【订单id'.$id.'- 子环节id'.$dsid.'】');

        $res = DB::name('order_s')->where(['oid'=>$id,'dsid'=>$dsid,'sort'=>$sort])->update(['status'=>-1]);

        DB::name('order_spec')->where( ['oid'=>$id,'dsid'=>$dsid,'sort'=>$sort] )->delete();

        if( $res ){

            // return $this->success('删除成功',url('lcindex',['id'=>$id,'did'=>$did]),'',0);

            $this->redirect('lcindex', ['id'=>$id,'did'=>$did]);

        }else{

            return $this->success('删除失败');

        }

    }

    





    /**

     * author: Json

     * 复制检测单

     */

    public function copy(){

        if( empty($this->data['id']) ) return $this->error('检测单ID为空');

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

            $created_time = '';

            if( !empty($info) ){

                $list = json_decode($info['spec'],true);

                // 查询添加时间

                $ctime = DB::name('order_s')->where(['oid'=> $id,'dsid'=> $sid,'status'=>1])->field('created_time')->find();

                if( $ctime ){

                    $created_time = $ctime['created_time'];

                }

                

                $list = select_spec_data($list,$id,$son['d_son_sn'],$sort);

            }

            $this->assign('order',$order);

            $this->assign('son',$son);

            $this->assign('info',$info);

            $this->assign('created_time',$created_time);

            $this->assign('sort',$sort);

            $this->assign('list',$list);

            return view();

        }else{

            if( empty($this->data['os_created_time']) ) $this->data['os_created_time'] = date('Y-m-d H:i:s');

            $obj = new OrderSpec();

        

            AdminLog($this->admin['id'],'编辑检测表单【'.$this->data['d_son_sn'].'】信息');

            $state = $obj->saveData($this->data,$this->admin);

            

            if($state){

                return $this->success('操作成功',url('lcindex',['id'=>$this->data['oid'],'did'=>$this->data['did']]));

            }else{

                return $this->error($obj->getError());

            }

        }

    }



    public function print(){





        return view();



    }



    public function idnex($id = 0,$did = 0){

        $order = OrderItem::get($id);

            $order['engding'] = config('engding_status')[$order['engding']];

            $son = DetectionSon::where('parent_id',$did)->order('sort asc')->field('id,d_son_sn,name')->select();

            

            $sonArr = [];

            foreach( $son as $item ){

                $sonArr[] = $item['d_son_sn'];

            }

            

            $infoSpec = DetectionSpec::where('d_son_sn','in',$sonArr)->select();

            $customer = Customer::where('id',$order['cid']['value'])->field('customer_name,province,city,county,address')->find();

            $resList = [];

            $list = [];

            $num = 0;

            $created_time="";

            foreach($son as $k=>$s){

                

                foreach( $infoSpec as $is ){

                    $created_time="";

                    $num = 0;

                    if( $s['d_son_sn'] == $is['d_son_sn'] ){

                        $list = [];

                        if( !empty($is) ){

                            $list = json_decode($is['spec'],true);

                            // 查询添加时间

                            $ctime = DB::name('order_s')->where(['oid'=> $id,'dsid'=> $s['id']])->field('created_time,sid,aid,s_type,sort')->select();

                            

                            

                            foreach($ctime as $ckey=>$ci){

                                if( $ci['s_type'] == 1 ){

                                    $per = DB::name('staff')->where('id',$ci['sid'])->field('name')->find();

                                    $person = $per['name'];

                                }else{

                                    $per = DB::name('admin')->where('id',$ci['aid'])->field('username')->find();

                                    $person = $per['username'];

                                }  

                                

                                $resList[$k]['son'][$ckey]['list'] = $this->sonSpec($id,$ci['sort'],$list);

                                $resList[$k]['son'][$ckey]['created_time'] = $ci['created_time'];

                                $resList[$k]['son'][$ckey]['name'] = $person;

                            }

                        }

                        // $list['created_time'] = $created_time;

                        // $list['person'] = $person;

                        // $resList[$k]['son']['name'] = $s['name'];

                    }



                }   

            }



            //获取二维码

            $url = config('codeUrl');

            $url = $url.'?order_sn='.$order['order_sn'];

            $res =  create_qrcode($url,$order['order_sn'],$order['created_time']);

            // dump($res);exit();

            $this->assign('qrcode',$res[0]['url']);

            $this->assign('order',$order);

            $this->assign('son',$son);

            $this->assign('resList',$resList);

            $this->assign('customer',$customer);

            

            // dump($resList);

            $this->assign('list',$list);

            return view();

    }

    



    public function sonSpec($id,$sort,$list){

        

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

                    $list[$key]['photo'] = \photo_arr_path( json_decode( html_entity_decode($val[0]), true ) );

                }

                if( $item['type'] === 'content' ){

                    $val[0] = \contentphotopathadmin($val[0]);

                }

                $list[$key]['value'] = $val[0];

            }else{

                $list[$key]['value'] = '';

            }

            

        }

        return $list;

    }



    /**

     * 检测类型设置

     *

     * @return void

     */

    public function category(){

        $list = OrderTestType::where('type','1')->select();

        $cateObj = new \lib\Category(['id','parent_id','name','cname']);

        $relist = $cateObj->getTree($list);

        $this->assign('list',$relist);

        return view();

    }

    public function category_op($id = 0){

        if(!request()->isPost()){

            $info = OrderTestType::get($id);

            $list = OrderTestType::where(['parent_id'=>0])->select();

            $cateObj = new \lib\Category(['id','parent_id','name','cname']);

            $relist = $cateObj->getTree($list);

            $this->assign('group',$relist);

            $this->assign('info',$info);

            return view();

        }else{

            $obj = new OrderTestType();

            $this->data['type'] = '1';

            if($this->data['id']){

                // 编辑

                AdminLog($this->admin['id'],'修改检测单检测类型【'.$this->data['name'].'】信息');

                $state = $obj->saveData($this->data,'edit');

            }else{

                // 新增

                AdminLog($this->admin['id'],'新增检测单检测类型【'.$this->data['name'].'】');

                $state = $obj->saveData($this->data);

            }

            if($state){

                return $this->success('操作成功',url('category'));

            }else{

                return $this->error($obj->getError());

            }

        }

    }



    /**

     * 批量创建订单

     */

    public function batchOrder(){

        // 新增

        $num = (int)( $this->data['num']);

        $order_sn= order_sn_rand();

        if( empty( $num) ) return_ajax(40001,'error','新建单数错误');

        

        if( empty( $num )  ) return_ajax(40001,'error','新建单数错误');

        for( $i = 0; $i < $num ; $i++  ){

            $order_sn = $order_sn + 1;

            $data[$i]['order_sn'] = 'UZ'.$order_sn.'PH';

            $data[$i]['sid'] = $this->admin['id'];

            $data[$i]['s_type'] = 2;

            $data[$i]['created_time'] = date('Y-m-d H:i:s');

            $data[$i]['updated_time'] = date('Y-m-d H:i:s');

            $data[$i]['is_show'] = implode(',',['composition','supplier','machine','contract_sn','remark','test_type']);

        }



        AdminLog($this->admin['id'],'新增检测单【'.$this->data['num'].'】');

        $state = DB::name('order')->insertAll($data);

        

        if( $state ){

            return json(['code'=>1,'msg'=>'批量创建检测单成功']);

        }else{

            return json(['code'=>0,'msg'=>'批量创建检测单失败']);

        }

    }



    /**

     *  批量下载图片

     */

    public function loadQuecode(  ){

        if( empty( $this->data['order_sn'] ) ) return_ajax(40001,'error','参数错误');



        $Arr = explode(',',$this->data['order_sn']);

        $res = [];



        $path = \think\facade\Env::get('root_path').'public';

        //在此之前你的项目目录中必须新建一个空的zip包

        $file_template=$path.'/static/quecode.zip';  //在此之前你的项目目录中必须新建一个空的zip包（必须存在）

        if(!file_exists($file_template)){

            return json(['code'=>0,'msg'=>'公用压缩文件丢失']);

        }

        //自定义文件名

        $card="quecode";

        //即将打包的zip文件名称

        $downname ='/'.$card.'.zip';  //你即将打包的zip文件名称

        //把你打包后zip所存放的目录

        $file_name = $path.'/uploads'.$downname.""; //把你打包后zip所存放的目录

        $result = copy( $file_template, $file_name );//把原来项目目录存在的zip复制一份新的到另外一个目录并重命名（可以在原来的目录）

        $zip = new \ZipArchive();//新建一个对象



        $res = [];

        $url = config('codeUrl');

        $obj = new OrderItem;

        foreach( $Arr as $key=>$item ){

            $res[] = $obj->create_qrcode_down($url,$item)[0];

        }



        if ($zip->open($file_name, \ZipArchive::CREATE) === TRUE) { //打开你复制过后空的zip包

            $zip->addEmptyDir($card);   //在zip压缩包中建一个空文件夹，成功时返回 TRUE， 或者在失败时返回 FALSE

            //下面是我的场景业务处理，可根据自己的场景需要去处理（我的是将所有的图片打包）

            $i = 1;

            foreach ($res as $key3 => $value3) {

                

                $file_ext = explode('/',$value3['path']);//获取到图片的后缀名

                

                $zip->addFromString($card.'/'.$file_ext[count($file_ext)-1] , file_get_contents($value3['path']));//（图片的重命名，获取到图片的二进制流）

                $i++;

            }

            

            $zipurl = config('PHOTOPATH').'/uploads'.$downname;

            

            $zip->close();

            $fp=fopen($file_name,"r"); 

            $file_size=filesize($file_name);//获取文件的字节

          

            header ( "Cache-Control: max-age=0" );

            header ( "Content-Description: File Transfer" );

            header ( 'Content-disposition: attachment; filename=' . basename ( $downname ) ); // 文件名

            header ( "Content-Type: application/zip" ); // zip格式的

            header ( "Content-Transfer-Encoding: binary" ); // 告诉浏览器，这是二进制文件

            header ( 'Content-Length: ' . filesize ( $file_name ) ); // 告诉浏览器，文件大小

            

            $buffer=1024; //设置一次读取的字节数，每读取一次，就输出数据（即返回给浏览器） 

            $file_count=0; //读取的总字节数 

            //向浏览器返回数据 如果下载完成就停止输出，如果未下载完成就一直在输出。根据文件的字节大小判断是否下载完成

            @readfile ( $file_name );//输出文件;

            @unlink($file_name);

        

            //下载完成后删除压缩包，临时文件夹 

            if($file_count >= $file_size) { 

        　　　　unlink($file_name); 

            }

        }





        



        dump(  $res );exit();



    }



    /**

     * 批量删除

     */

    public function delArr(){

        if( empty( $this->data['id'] ) ) return_ajax(40001,'error','参数错误');

        $idS = implode(',',$this->data['id']);

        AdminLog($this->admin['id'],'批量删除检测单信息【订单id'.$idS.'】');





        $state = DB::name('order')->where('id','in',$this->data['id'])->delete();

        

        if( $state ){

            return json(['code'=>1,'msg'=>'批量删除检测单成功']);

        }else{

            return json(['code'=>0,'msg'=>'批量删除检测单失败']);

        }





    }


    /**

     * 数据导出 Excel

     */

    public function load_excel(){
        
        
        $like       = isset($_GET['like']) ? $_GET['like'] : '';

        $customer   = isset($_GET['customer']) ? $_GET['customer'] : '';

        $goods      = isset($_GET['goods']) ? $_GET['goods'] : '';

        $detection  = isset($_GET['detection']) ? $_GET['detection'] : '';

        $status     = isset($_GET['status']) ? $_GET['status'] : '';

        $start_time = isset($_GET['start_time']) ? $_GET['start_time'] : '';

        $end_time   = isset($_GET['end_time']) ? $_GET['end_time'] : '';


        
        $where[] = ['o.id','neq',0];

        if(!empty(input('like'))){

            // 检测单号、客户名称、产品编号、名称、规格、建单人、备注

            $where[] = ['o.order_sn|c.customer_name|g.good_sn|g.title|o.spec|o.sid|o.remark','like',"%".input('like')."%"];

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

        



            if( !empty(input('customer')) || !empty(input('goods')) || !empty(input('detection')) || !empty(input('like')) ){
                $list = OrderItem::alias('o')

                                ->join('customer c','c.id = o.cid')

                                ->join('goods g','g.id = o.gid')

                                ->join('detection d','d.id = o.did')

                                ->where($where)

                                ->field('o.id,o.order_sn,o.cid,o.gid,o.did,o.gsid,o.sid,o.s_type,o.spec,o.supplier,o.machine,o.composition,o.status,o.engding,o.remark,o.is_show,o.created_time,o.updated_time,o.test_type,o.contract_sn')

                                // ->limit($startNum.','.$limit)

                                ->order('o.id desc')

                                ->select();

            }else{
                $list = OrderItem::alias('o')

                                ->where($where)

                                ->field('o.id,o.order_sn,o.cid,o.gid,o.did,o.gsid,o.sid,o.s_type,o.spec,o.supplier,o.machine,o.composition,o.status,o.engding,o.remark,o.is_show,o.created_time,o.updated_time,o.test_type,o.contract_sn')

                                // ->limit($startNum.','.$limit)

                                ->order('o.id desc')

                                ->select();

            }

                $test_type  = OrderTestType::field('id,name')->select();
                
                $nList = array();
                
                if(!empty($list)){

                    foreach($list as $k=>$vo){

                        $nList[$k] = $vo;

                        $nList[$k]['customer_name'] = $vo['cid']['text'];

                        $nList[$k]['goods_title'] = $vo['gid']['text'];

                        $nList[$k]['detection_name'] = $vo['did']['text'];

                        $nList[$k]['staff'] = ($vo['s_type'] == 1 ? '员工:':'管理员:') .get_order_cname($vo['sid'],$vo['s_type']);

                        $nList[$k]['test_type_text'] = $vo['test_type']['text'];

                        $nList[$k]['engding'] = config('engding_status')[$vo['engding']];
                        
                        $jcsj = '';
                        $jcsj = DB::name('order_s')->where('oid',$vo['id'])->where('did',$vo['did']['value'])->order('created_time asc')->field('dsid,status,sort')->find();
                        $jcsjStr = '';
                        if( empty($jcsj) ){
                            $nList[$k]['jcsj'] = '';
                        }else{
                            $jcsj_name  = DB::name('detection_son')->where('id',$jcsj['dsid'])->field('name,d_son_sn')->find();
                            $spec       = DB::name('detection_spec')->where('d_son_sn',$jcsj_name['d_son_sn'])->find();
                            $reArr      = DB::name('order_spec')->where('dsid',$jcsj['dsid'])->where('oid',$vo['id'])->where('sort',$jcsj['sort'])->select();
                            
                            if( !empty( $spec['spec'] ) ){
                                $sArr = json_decode($spec['spec'],true);
                                
                                foreach( $sArr as $item ){
                                    foreach( $reArr as $i ){
                                        if( $i['class_name'] == $item['name'] && $item['type'] != 'file' ){
                                            $jcsjStr = $jcsjStr.$item['title'] .''.$i['content'].chr(10) ;
                                        }
                                    }
                                }
                                
                            }
                            switch( $jcsj['status'] ){
                                case 0:
                                    $jcsj_text = '未审核';
                                break;
                                case 1:
                                    $jcsj_text = '合格';
                                break;
                                case 2:
                                    $jcsj_text = '不合格';
                                break;
                            }
                            $nList[$k]['jcsj'] = $jcsj_name['name'].chr(10).$jcsjStr;
                        }

                        $jcry = DB::name('order_s')->where('oid',$vo['id'])->where('did',$vo['did']['value'])->field('sid,aid,s_type')->select();
                        if( empty($jcry) ){
                            $nList[$k]['jcry'] = '';
                        }else{
                            $jcryArr = [];
                            $jcrySid  = [];
                            $jcryAid  = [];

                            foreach( $jcry as $item ){
                                if( $item['s_type'] == 1 ){
                                    $jcrySid[] = $item['sid'];
                                }
                                if( $item['s_type'] == 2 ){
                                    $jcryAid[] = $item['aid'];
                                }
                            } 
                            if( !empty($jcrySid) ){
                                $jcrySid = DB::name('staff')->where('id','in',$jcrySid)->field('name')->select();
                                foreach($jcrySid as $item){
                                    $jcryArr[] = $item['name'];
                                }
                            }
                            if( !empty($jcryAid) ){
                                $jcryAid = DB::name('admin')->where('id','in',$jcryAid)->field('username')->select();
                                foreach($jcryAid as $item){
                                    $jcryArr[] = $item['username'];
                                }
                            }
                            $nList[$k]['jcry'] = implode(',',$jcryArr);
                        }
                        
                        $nList[$k]['status'] = config('order_status')[$vo['status']];

                        $nList[$k]['op'] = url('option',['id'=>$vo['id']]);

                        $nList[$k]['print'] = url('idnex',['id'=>$vo['id'],'sid'=>$vo['did']['value']]);

                        $nList[$k]['lc'] = url('lcindex',['id'=>$vo['id'],'did'=>$vo['did']['value']]);

                        

                    }

                }
                $excelName = 'UZ'.date('YmdHis');
                $this->exportExcel($nList,$excelName);

                
                exit();


    }



    public function exportExcel($data, $name){
        $objPHPExcel = new \PHPExcel();  //实例化  相当于在桌面新建一个excel
            // 表头
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '序号')
                ->setCellValue('B1', '建单时间')
                ->setCellValue('C1', '检测单号')
                ->setCellValue('D1', '客户名称')
                ->setCellValue('E1', '建单人')
                ->setCellValue('F1', '供应商')
                ->setCellValue('G1', '产品名称')
                ->setCellValue('H1', '产品型号')
                ->setCellValue('I1', '检测流程')
                ->setCellValue('J1', '检测数据')
                ->setCellValue('K1', '检测结果')
                ->setCellValue('L1', '备注')
                ->setCellValue('M1', '检测人员')
                ->setCellValue('N1', '检测类型')
                ->setCellValue('O1', '合同编号（物流编号）')
                ->setCellValue('P1', '注塑机台')
                ->setCellValue('Q1', '材料成分')
                ->setCellValue('R1', '流程状态');
            //设置内容水平居中
            $objPHPExcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            // 设置个表格宽度
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(24);
            //设置单元格自动宽度
            $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setAutoSize(true);
            // 内容
            for ($i = 0, $len = count($data); $i < $len; $i++) {
                $objPHPExcel->getActiveSheet(0)->setCellValue('A' . ($i + 2), $data[$i]['id']);     //序号
                $objPHPExcel->getActiveSheet(0)->setCellValue('B' . ($i + 2), $data[$i]['created_time']);   // 建单时间
                $objPHPExcel->getActiveSheet(0)->setCellValue('C' . ($i + 2), $data[$i]['order_sn'], \PHPExcel_Cell_DataType::TYPE_STRING);       // 检测单号
                $objPHPExcel->getActiveSheet(0)->setCellValue('D' . ($i + 2), $data[$i]['customer_name']);  // 客户名称
                $objPHPExcel->getActiveSheet(0)->setCellValue('E' . ($i + 2), $data[$i]['staff']);          // 建单人
                $objPHPExcel->getActiveSheet(0)->setCellValue('F' . ($i + 2), $data[$i]['supplier']);       // 供应商
                $objPHPExcel->getActiveSheet(0)->setCellValue('G' . ($i + 2), $data[$i]['goods_title']);    // 产品名称 
                $objPHPExcel->getActiveSheet(0)->setCellValue('H' . ($i + 2), $data[$i]['spec']);           // 产品型号
                $objPHPExcel->getActiveSheet(0)->setCellValue('I' . ($i + 2), $data[$i]['detection_name']); // 检测流程
                $objPHPExcel->getActiveSheet(0)->setCellValue('J' . ($i + 2), $data[$i]['jcsj']);           // 检测数据 
                $objPHPExcel->getActiveSheet(0)->setCellValue('K' . ($i + 2), $data[$i]['engding']);        // 检测结果
                $objPHPExcel->getActiveSheet(0)->setCellValue('L' . ($i + 2), $data[$i]['remark']);         // 备注
                $objPHPExcel->getActiveSheet(0)->setCellValue('M' . ($i + 2), $data[$i]['jcry']);           // 检测人员   
                $objPHPExcel->getActiveSheet(0)->setCellValue('N' . ($i + 2), $data[$i]['test_type_text']); // 检测类型
                $objPHPExcel->getActiveSheet(0)->setCellValue('O' . ($i + 2), $data[$i]['contract_sn']);    // 合同编号（物流编号）
                $objPHPExcel->getActiveSheet(0)->setCellValue('P' . ($i + 2), $data[$i]['machine']);        // 注塑机台
                $objPHPExcel->getActiveSheet(0)->setCellValue('Q' . ($i + 2), $data[$i]['composition']);    // 材料成分
                $objPHPExcel->getActiveSheet(0)->setCellValue('R' . ($i + 2), $data[$i]['status']);         // 流程状态
                //设置内容水平居中
                $objPHPExcel->setActiveSheetIndex(0)->getStyle($i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                
                $objPHPExcel->getActiveSheet()->getStyle('J' . ($i + 2))->getAlignment()->setWrapText(true);
            }
            $objPHPExcel->getActiveSheet()->getColumnDimension('c')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('d')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('e')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('f')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('g')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('h')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('i')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('j')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('k')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('l')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('m')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('n')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('o')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('p')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('q')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('r')->setWidth(20);
            $objPHPExcel->getActiveSheet()->setTitle('User');
            $objPHPExcel->setActiveSheetIndex(0);
            ob_end_clean();//清楚缓存区，解决乱码问题
            header("Pragma:public");
            header("Content-Type:application/x-msexecl;name=\"{$name}.xlsx\"");
            header("Content-Disposition:inline;filename=\"{$name}.xlsx\"");
            $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->save('php://output');
            exit;
    }




}