<?php
namespace app\admin\controller;
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



class Staff extends AuthBack{


    public function initialize(){
        parent::initialize();
    }

    public function index(){
        $model = new StaffItem;
        $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
        $name = isset($_GET['name']) ? $_GET['name'] : '';
        $phone = isset($_GET['phone']) ? $_GET['phone'] : '';
        $where = [];
        if($name){
            $where[] = ['name','like',"%{$name}%"];
        }
        if($phone){
            $where[] = ['phone','like',"%{$phone}%"];
        }
        if( !empty(input('title')) ){
            $where[] = ['customer_sn|customer_name|phone|name', 'like', "%".input('title')."%"];
        }
        if( !empty(input('cid')) ){
            //获取一级分类的子分类
            $cid = categorySelSon('sta_category',input('cid'),true);
            $where[] = ['cid','in',$cid];
        }
        if( !empty(input('status')) || input('status')==='0' ){
            $where[] = ['status','eq',input('status')];
        }
        
        $totalNum = StaffItem::where($where)->count();
        $list = StaCategory::where('type','1')->where([['id','neq',51]])->select();
        $cateObj = new \lib\Category(['id','parent_id','name','cname']);
        $category = $cateObj->getTree($list);
        if(request()->isAjax()){
            $power = DB::name('staff_power')->select();

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
                        $nList[$k]['power'] = $this->staffpower($vo['power'],$power);
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
            $this->assign('category',$category);
            return view();
        }
    }

    /**
     * 员工权利字符串生成
     * 
     */
    public function staffpower($powerNum,$powerArr){
        $staff = explode(',',$powerNum);
        $strArr = [];
        foreach( $staff as $item ){
            foreach( $powerArr as $i ){
                if( $item == $i['id'] ){
                    $strArr[] = $i['name'];
                }
            }
        }
        return implode(',',$strArr);

    }





    /**
     * 编辑代理信息
     * @param int $id
     * @return \think\response\View|void
     */
    public function option($id = 0){
        if(!request()->isPost()){
            $list = StaCategory::where('type','1')->where([['id','neq',51]])->select();
            $cateObj = new \lib\Category(['id','parent_id','name','cname']);
            $relist = $cateObj->getTree($list);
            $info = StaffItem::get($id);
            $power = model('StaffPower')->pSelect();
            if( $id != 0 ){
                $info['power'] = explode(',',$info['power']);
            }
            
            $this->assign('info',$info);
            $this->assign('list',$relist);
            $this->assign('power',$power);

            return view();
        }else{
            
            $this->data['power'] = implode(',',$this->data['power']);
            $obj = new StaffItem();
            if($this->data['id']){
                // 编辑
                AdminLog($this->admin['id'],'修改员工【'.$this->data['name'].'】信息');
                if( $this->data['password'] == ''){
                    $info = StaffItem::get($this->data['id']);
                    $this->data['password'] = $info['password'];
                }else{
                    $this->data['password'] = sp_password($this->data['password']);
                }
                
                $state = $obj->saveData($this->data,'edit');
            }else{
                // 新增
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

    public function category_op($id = 0){
        if(!request()->isPost()){
            $info = StaCategory::get($id);
            $group = StaCategory::where('parent_id','0')->where('type','1')->select();
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