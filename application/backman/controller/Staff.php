<?php
namespace app\backman\controller;
use app\backman\model\Meal;
use \app\common\controller\AuthBack;
use app\common\model\Staff as StaffItem;
use app\common\model\StaffPower;
use app\common\model\StaCategory;
use lib\CashStatus;
use think\Db;
use think\facade\Env;
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use Intervention\Image;
use GuzzleHttp;
use think\facade\App;
require_once App::getRootPath().'/extend/PHPExcel/PHPExcel.php';


class Staff extends AuthBack{


    public function initialize(){
        parent::initialize();
    }

    public function index(){
        $model = new StaffItem;
        $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
        $name = isset($_GET['name']) ? $_GET['name'] : '';
	    $phone = isset($_GET['phone']) ? $_GET['phone'] : '';
	    $status = isset($_GET['status']) ? $_GET['status'] : '';
        $where = [];
        if($name){
            $where[] = ['name','like',"%{$name}%"];
        }
        if($phone){
            $where[] = ['phone','like',"%{$phone}%"];
        }



	    if( !empty($status) || $status==='0' ){
            $where[] = ['status','eq',$status];
        }
        
        $totalNum = StaffItem::where($where)->count();
        if(request()->isAjax()){
            $page = isset($_GET['page']) ? $_GET['page'] : '1';
            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
            $startNum = ($page - 1) * $limit;
            $totalPage = ceil($totalNum/$limit);
            $list = StaffItem::where($where)->limit($startNum.','.$limit)->order('id desc')->select();

            if(empty($list)){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage,'count'=>$totalNum]);
            }elseif($page > $totalPage){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage+1,'count'=>$totalNum]);
            }else{
                $nList = array();
                if(!empty($list)){
                    foreach($list as $k=>$vo){
                        $nList[$k] = $vo;
                        $nList[$k]['text'] = $vo['cid']['text'];
                        $nList[$k]['op'] = url('option',['id'=>$vo['id']]);
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
            return view();
        }
    }



    /*
    * author: Jason
    * 员工批量导入
    */
    public function savestudentImport(){  
        $config = load_config('application/upload');
        $size = $config['upload_size']*1024*1024;

        $objPHPExcel  = new \PHPExcel();
        $root_path = \think\facade\Env::get('root_path').'public';
        //获取表单上传文件  
        $file = request()->file('file');  
        $info = $file->validate(['size'=>$size,'ext'=>'xlsx,xls,csv'])->move( $root_path . '/excel');  
        if($info){  
            $exclePath = $info->getSaveName();  //获取文件名  
            
            $file_name = $root_path . '/excel/'. $exclePath;   //上传文件的地址  
            
            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');  
            
            if(!$objReader->canRead($file_name)){
                $objReader = \PHPExcel_IOFactory::createReader('Excel5');
            }
            
            $obj_PHPExcel =$objReader->load($file_name, $encode = 'utf-8');  //加载文件内容,编码utf-8  
            $excel_array=$obj_PHPExcel->getsheet(0)->toArray();   //转换为数组格式  
            array_shift($excel_array);  //删除第一个数组(标题);  
            $data = [];  
            $i=0;  
            $resn = [];
            $rephone = [];
            $phonearr = [];
            $ndata = [];
            $category = DB::name('sta_category')->where('type',1)->field('id,name')->order('id asc')->select();
            foreach($excel_array as $k=>$v) {  
                if( empty($v[2])){
                    continue;
                }
                
                // 查看是否设置员工号码 有就查询数据库， 重复记录返回
                if( empty( $v[0] ) ){
                    $data[$k]['staff_sn'] = 'JC'.get_order_sn_rand();
                }else{
                    $where[] = ['staff_sn','eq',$v[0]];
                    $count = DB::name('staff')->where($where)->count();
                    if(!$count){
                        $data[$k]['staff_sn'] = $v[0]; 
                    }else{
                        $resn[] = $v[0];
                    }
                    
                }

                if( empty($v[1]) ){
                    $data[$k]['cid'] = $category[0]['id'];
                }else{
                    foreach( $category as $item ){
                        if( $item['name'] == $v[1] ){
                            $data[$k]['cid'] = $item['id'];
                        }
                    }
                    if( empty($data[$k]['cid']) ){
                        $data[$k]['cid'] = $category[0]['id'];
                    }
                }
                $data[$k]['name'] = $v[2]; 
                $data[$k]['phone'] = $v[2]; 
                // 查看是否存在手机号 有就查询数据库， 重复记录返回
                if( in_array($v[3], $phonearr) ){
                    $rephone[] = $v[3];
                    $i++;
                    continue;
                }
                if( empty( $v[3] ) ){
                    $i++;
                    continue;
                }else{
                    $where1[] = ['phone','eq',$v[3]];
                    $where1[] = ['status','eq',1];
                    $count = DB::name('staff')->where($where1)->count();
                    if(!$count){
                        $data[$k]['phone'] = $v[3]; 
                    }else{
                        $rephone[] = $v[3];
                        $i++;
                        continue;
                    }
                    
                }
                $phonearr[] = $v[3];
                if(empty( $v[4] )){
                    $data[$k]['password'] = sp_password($v[4]); 
                }else{
                    $data[$k]['password'] = sp_password('123456'); 
                }
                $data[$k]['email'] = $v[5]; 
                
                $data[$k]['status'] = 1;
                $data[$k]['timestamp'] = date('Y-m-d H:i:s');
                $ndata[] = $data[$k];
                $i++;  
            }  
            
           $success=Db::name('staff')->insertAll($ndata); //批量插入数据  这里的数据表改为你需要的。
           // 统计录入失败数量
           $error=$i-$success;
            // 删除文件
           unset($info);
           unset($obj_PHPExcel);
           unlink($file_name);

            $return = [
                'code'=>0,
                'msg'=>"导入成功{$success}条记录，失败{$error}条记录,编号重复".json_encode($resn)."手机号重复".json_encode($rephone),
            ];

            return json($return);
           
        }else{  
            // 上传失败获取错误信息  
              
            return json($file->getError());
        }  
  
    }

    public function del(){

        $name = DB::name('staff')->where('id',$this->data['id'])->field('name')->find();

        $count = DB::name('order')->where('sid',$this->data['id'])->where('s_type',1)->count();
        
        if( $count ){
            return $this->error('该员工有负责的订单，无法删除');
        }else{
            AdminLog($this->admin['id'],'删除员工【'.$this->data['id'].'】信息');
            $res = DB::name('staff')->where('id',$this->data['id'])->update(['status'=>'-1']);
            if( $res ){
                return $this->success('操作成功',url('index'));
            }else{
                return $this->error('删除失败');
            }
        }

    }

    /**
     * 编辑核销账号信息
     * @param int $id
     * @return \think\response\View|void
     */
    public function option($id = 0){
        if(!request()->isPost()){
            $info = StaffItem::get($id);

            $this->assign('info',$info);
            return view();
        }else{
            $obj = new StaffItem();
            if($this->data['id']){
                // 编辑
                AdminLog($this->admin['id'],'修改员工【'.$this->data['name'].'】信息');
                if( $this->data['password'] == ''){
                    $info = StaffItem::get($this->data['id']);
	                $this->data['password'] = $info['password'];
	                $this->data['password_show'] = $info['password_show'];
                }else{
	                $this->data['password_show'] = $this->data['password'];
	                $this->data['password'] = sp_password($this->data['password']);
                }
                
                $state = $obj->saveData($this->data,'edit');
            }else{
                // 新增
	            $this->data['password_show'] = $this->data['password'];
	            $this->data['password'] = sp_password($this->data['password']);
                AdminLog($this->admin['id'],'新增员工【'.$this->data['name'].'】');
                $state = $obj->saveData($this->data);
            }
            if($state){
                return $this->success('操作成功',url('index'));
            }else{
                return $this->error($obj->getError());
            }
        }
    }

    public function category(){

        $list = StaCategory::where('type','1')->select();
        $cateObj = new \lib\Category(['id','parent_id','name','cname']);
        $relist = $cateObj->getTree($list);
        $this->assign('list',$relist);
        return view();
    }

    public function category_op($id = 0,$parent_id = 0){
        if(!request()->isPost()){
            $info = StaCategory::get($id);
            $group = StaCategory::where('parent_id','0')->where('type','1')->select();

            $this->assign('parent_id',$parent_id);
            $this->assign('group',$group);
            $this->assign('info',$info);
            return view();
        }else{
            $obj = new StaCategory();
            if($this->data['id']){
                // 编辑
                AdminLog($this->admin['id'],'修改员工分类【'.$this->data['name'].'】信息');
                $state = $obj->saveData($this->data,'edit');
            }else{
                // 新增
                AdminLog($this->admin['id'],'新增员工分类【'.$this->data['name'].'】');
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
     * 员工权限管理列表
     * @return [type] [description]
     */
    public function powerlist(){

        $list = StaffPower::where('is_show','1')->order('sort','asc')->select();
        $cateObj = new \lib\Category(['id','parent_id','name','cname']);
        $relist = $cateObj->getTree($list);
        $this->assign('list',$relist);
        return view();
    }

    /**
     * 权限修改
     * @return [type] [description]
     */
    public function power_op($id = 0){
        if(!request()->isPost()){
           
            $info = StaffPower::get($id);
            $group = StaffPower::where('parent_id','0')->where('is_show','1')->select();
            $this->assign('group',$group);
            $this->assign('info',$info);
            return view();
        }else{
            $obj = new StaffPower();
            if($this->data['id']){
                // 编辑
                AdminLog($this->admin['id'],'修改员工权限【'.$this->data['name'].'】信息');
                $state = $obj->saveData($this->data,'edit');
            }else{
                // 新增
                AdminLog($this->admin['id'],'新增员工权限【'.$this->data['name'].'】');
                $state = $obj->saveData($this->data);
            }
            if($state){
                return $this->success('操作成功',url('powerlist'));
            }else{
                return $this->error($obj->getError());
            }
        }
    }



}