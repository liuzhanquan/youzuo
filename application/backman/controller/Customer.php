<?php
namespace app\backman\controller;
use app\backman\model\Meal;
use \app\common\controller\AuthBack;
use app\common\model\Customer as CustomerItem;
use app\common\model\CusCategory;
use lib\CashStatus;
use think\Db;
use think\facade\Env;
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use Intervention\Image;
use GuzzleHttp;



class Customer extends AuthBack{


    public function initialize(){
        parent::initialize();
    }

    public function index(){
        $name = isset($_GET['name']) ? $_GET['name'] : '';
        $phone = isset($_GET['phone']) ? $_GET['phone'] : '';
        $where = [];
        if( !empty(input('title')) ){
            $where[] = ['customer_sn|customer_name|phone|content', 'like', "%".input('title')."%"];
        }


        $totalNum = CustomerItem::where($where)->count();
        
        if(request()->isAjax()){
            $page = isset($_GET['page']) ? $_GET['page'] : '1';
            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
            $startNum = ($page - 1) * $limit;
            $totalPage = ceil($totalNum/$limit);
            $list = CustomerItem::where($where)->limit($startNum.','.$limit)->order('id desc')->select();
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


    /**
     * 编辑业务员信息
     * @param int $id
     * @return \think\response\View|void
     */
    public function option($id = 0){
        if(!request()->isPost()){
            $info = CustomerItem::get($id);
            
            $this->assign('info',$info);
            return view();
        }else{
            $obj = new CustomerItem();

            if( !empty($this->data['phone']) ){
                if( !phoneNum($this->data['phone']) ){
                    return $this->error('请输入正确的手机号');
                }
            }

            if($this->data['id']){
                // 编辑
                AdminLog($this->admin['id'],'修改业务员【'.$this->data['customer_name'].'】信息');
                $state = $obj->saveData($this->data,'edit');
            }else{
                // 新增
                AdminLog($this->admin['id'],'添加业务员【'.$this->data['customer_name'].'】');
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

        $list = CusCategory::where('type','1')->select();
        $cateObj = new \lib\Category(['id','parent_id','name','cname']);
        $relist = $cateObj->getTree($list);
        $this->assign('list',$relist);
        return view();
    }

    public function category_op($id = 0,$parent_id = 0){
        if(!request()->isPost()){
            $info = CusCategory::get($id);
            $group = CusCategory::where('parent_id','0')->where('type','1')->select();
            $this->assign('parent_id',$parent_id);
            $this->assign('group',$group);
            $this->assign('info',$info);
            return view();
        }else{
            $obj = new CusCategory();
            if($this->data['id']){
                // 编辑
                AdminLog($this->admin['id'],'修改客户分类【'.$this->data['name'].'】信息');
                $state = $obj->saveData($this->data,'edit');
            }else{
                // 新增
                AdminLog($this->admin['id'],'新增客户分类【'.$this->data['name'].'】');
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
     * 客户删除
     */
    public function del(){
        $count = DB::name('order')->where('cid',$this->data['id'])->count();
        if( $count ){
            return $this->error('该客户有绑定的订单，无法删除');
        }else{
            AdminLog($this->admin['id'],'删除客户【'.$this->data['id'].'】信息');
            $res = DB::name('customer')->where('id',$this->data['id'])->delete();
            if( $res ){
                return $this->success('操作成功',url('index'));
            }else{
                return $this->error('删除失败');
            }
        }

    }






}