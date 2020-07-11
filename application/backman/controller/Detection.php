<?php
namespace app\backman\controller;
use app\backman\model\Meal;
use \app\common\controller\AuthBack;
use app\common\model\Detection as DetectionItem;
use app\common\model\DetectionSon;
use app\common\model\Goods;
use app\common\model\Customer;
use think\Db;
use think\facade\Env;
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use Intervention\Image;
use GuzzleHttp;



class Detection extends AuthBack{


    public function initialize(){
        parent::initialize();
    }

    public function index(){
        $model = new DetectionItem;
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
        $totalNum = DetectionItem::where($where)->count();
        if(request()->isAjax()){
            $page = isset($_GET['page']) ? $_GET['page'] : '1';
            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
            $startNum = ($page - 1) * $limit;
            $totalPage = ceil($totalNum/$limit);

            if( !empty(input('title')) ){
                $where[] = ['detection_sn|name|content','like','%'.input('title').'%'];
            }

            $list = DetectionItem::where($where)->limit($startNum.','.$limit)->order('id desc')->select();
            
            if(empty($list)){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage,'count'=>$totalNum]);
            }elseif($page > $totalPage){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage+1,'count'=>$totalNum]);
            }else{
                $nList = array();
                if(!empty($list)){
                    foreach($list as $k=>$vo){
                        $nList[$k] = $vo;
	                    $nList[$k]['goods_text'] = $vo['goods_id']['text'];
	                    $nList[$k]['customer_name'] = $vo['customer_id']['text'];
	                    $nList[$k]['number_line'] = $vo['start_num'].'-'.$vo['end_num'];
                        $nList[$k]['op'] = url('option',['id'=>$vo['id']]);
                        $nList[$k]['cp'] = url('copy', ['id'=>$vo['id'] ] );
                        $nList[$k]['lc'] = url('lcindex',['id'=>$vo['id']]);
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
     * 分配记录删除
     */
    public function del(){
        $count = DB::name('order')->where('detection_id',$this->data['id'])->count();
        if( $count ){
            return $this->error('该检测流程有绑定的订单，无法删除');
        }else{
            AdminLog($this->admin['id'],'删除检测流程【'.$this->data['id'].'】信息');
            $res = DB::name('detection')->where('id',$this->data['id'])->delete();

            if( $res ){
                return $this->success('操作成功',url('index'));
            }else{
                return $this->error('删除失败');
            }
        }

    }


    /**
     * 二维码分配管理 添加/修改
     * @param int $id
     * @return \think\response\View|void
     */
    public function option($id = 0){
        if(!request()->isPost()){

            $info = DetectionItem::get($id);
            // 商品列表
	        $goods = Goods::where('status',1)->field('id, title')->order('timestamp desc')->select();
	        $customer = Customer::where('status',1)->field('id, customer_name')->order('timestamp desc')->select();

	        $this->assign('info',$info);
	        $this->assign('goods',$goods);
	        $this->assign('customer',$customer);

            return view();
        }else{
            $obj = new DetectionItem();

            if($this->data['id']){
                // 编辑
                AdminLog($this->admin['id'],'修改二维码记录【'.$this->data['start_num'].'-'.$this->data['end_num'].'】信息');
                $state = $obj->saveData($this->data,'edit');
            }else{
                // 新增
                AdminLog($this->admin['id'],'新增二维码记录【'.$this->data['start_num'].'-'.$this->data['end_num'].'】');
                $state = $obj->saveData($this->data);
            }
            if($state){
            	return $this->success('操作成功',url('index'));
            }else{
                return $this->error($obj->getError());
            }
        }
    }






}