<?php
namespace app\backman\controller;
use app\backman\model\Meal;
use \app\common\controller\AuthBack;
use \app\common\model\Category;
use app\common\model\Comment;
use \app\common\model\Goods as GoodsItem;
use \app\backman\model\Delivery;
use \app\backman\model\AgentLevel;
use app\common\model\User;
use think\Db;
use think\facade\App;
require_once App::getRootPath().'/extend/PHPExcel/PHPExcel.php';

class Goods extends AuthBack{


    public function initialize(){
        parent::initialize();
    }

    public function index(){
        $title = isset($_GET['title']) ? $_GET['title'] : '';
        $where[] = ['id','neq',0];
        if( !empty(input('title')) ){
            $where[] = ['good_sn|title', 'like', "%".input('title')."%"];
        }
	    if( !empty(input('start_time')) && !empty(input('end_time')) ){

		    $where[] = ['timestamp','between',input('start_time').','.input('end_time')];

	    }else{

		    if( !empty(input('start_time')) ){
			    $where[] = ['timestamp','>=',input('start_time')];
		    }

		    if( !empty(input('end_time')) ){

			    $where[] = ['timestamp','<=',input('end_time')];

		    }

	    }

        $totalNum = GoodsItem::where($where)->count();
        if(request()->isAjax()){
            $page = isset($_GET['page']) ? $_GET['page'] : '1';
	        $limit = isset($_GET['limit']) ? $_GET['limit'] : '1';
            $startNum = ($page - 1) * $limit;
            $totalPage = ceil($totalNum/$limit);
            
            
            $list = GoodsItem::where($where)->limit($startNum.','.$limit)->order('id desc')->select();
            
            if(empty($list)){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage,'count'=>$totalNum]);
            }elseif($page > $totalPage){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage+1,'count'=>$totalNum]);
            }else{
                $nList = array();
                
                if(!empty($list)){
                    foreach($list as $k=>$vo){
                        $nList[$k] = $vo;
                        $nList[$k]['image'] = photo_addpath($vo['image']);
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
    * 产品批量导入
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
            $category = DB::name('category')->where('type',1)->field('id,name')->order('id asc')->select();
            foreach($excel_array as $k=>$v) {  
                if( empty($v[2])){
                    continue;
                }
                
                // 查看是否设置员工号码 有就查询数据库， 重复记录返回
                if( empty( $v[0] ) ){
                    $data[$k]['good_sn'] = 'CP'.get_order_sn_rand();
                }else{
                    $where[] = ['good_sn','eq',$v[0]];
                    $count = DB::name('goods')->where($where)->count();
                    if(!$count){
                        $data[$k]['good_sn'] = $v[0]; 
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
                $data[$k]['title'] = $v[2]; 
                
                $data[$k]['timestamp'] = date('Y-m-d H:i:s');
                $data[$k]['sort'] = 100;
                $data[$k]['status'] = 0;
                $i++;  
            }  
            
           $success=Db::name('goods')->insertAll($data); //批量插入数据  这里的数据表改为你需要的。
           // 统计录入失败数量
           $error=$i-$success;
            // 删除文件
           unset($info);
           unset($obj_PHPExcel);
           unlink($file_name);

            $return = [
                'code'=>0,
                'msg'=>"导入成功{$success}条记录，失败{$error}条记录,单号重复".json_encode($resn),
            ];

            return json($return);
           
        }else{  
            // 上传失败获取错误信息  
              
            return json($file->getError());
        }  
  
    }

   
    public function option($id = 0){
        if(!request()->isPost()){
            $list = Category::where('type','1')->where([['id','neq',51]])->select();
            $cateObj = new \lib\Category(['id','parent_id','name','cname']);
            $relist = $cateObj->getTree($list);
            $info = GoodsItem::get($id);
            if( !empty($info) ){
                $photo = photo_arr_path(unserialize($info['photo']));
                $info['content'] = contentphotopathadmin($info['content']);
                $this->assign('photo',$photo);
            }
            
            
            $this->assign('info',$info);
            $this->assign('list',$relist);
            return view();
        }else{
            $obj = new GoodsItem();

            if($this->data['id']){
                // 编辑
                AdminLog($this->admin['id'],'修改商品【'.$this->data['title'].'】信息');
                $state = $obj->saveData($this->data,'edit');
            }else{
                // 新增
                AdminLog($this->admin['id'],'新增商品【'.$this->data['title'].'】');
                $state = $obj->saveData($this->data);
            }
            if($state){
                return $this->success('操作成功',url('index'));
            }else{
                return $this->error($obj->getError());
            }
        }
    }



    public function update_status(){
        $data = $this->request->param();
        $table = $data['table'];
        $filed = $data['filed'];
        $filed_value = $data['filed_value'];
        $update[$filed] = $filed_value;
        $arr = json_decode($data['data'],true);
        $id = [];
        if(!empty($arr)){
            foreach ($arr as $item){
                $id[] = $item['id'];
            }
        }
        $res = Db::name($table)->whereIn('id',$id)->update($update);
        if(!$res){
            return json(['code'=>0,'msg'=>'请求失败']);
        }
        return json(['code'=>1,'msg'=>'请求成功']);
    }

    

    public function assess_del(){
        $data = $this->request->param();
        $id = $data['id'];
        $res = Db::name('comment')->whereIn('id',$id)->delete();
        if(!$res){
            return json(['code'=>0,'msg'=>'请求失败']);
        }
        return json(['code'=>1,'msg'=>'请求成功']);
    }

    /**
     * 产品删除
     */
    public function del(){
        $count = DB::name('order')->where('gid',$this->data['id'])->count();
        if( $count ){
            return $this->error('该产品有绑定的订单，无法删除');
        }else{
            AdminLog($this->admin['id'],'删除产品【'.$this->data['id'].'】信息');
            $res = DB::name('goods')->where('id',$this->data['id'])->delete();
            if( $res ){
                return $this->success('操作成功',url('index'));
            }else{
                return $this->error('删除失败');
            }
        }

    }

    public function assess(){
        $content = isset($_GET['content']) ? $_GET['content'] : '';
        $title = isset($_GET['title']) ? $_GET['title'] : '';
        $where = [];
        if($content){
            $where[] = ['content','like',"%{$content}%"];
        }
        if($title){
            //查询商品
            $goods_id = \app\common\model\Goods::where([['title','like',"%{$title}%"]])->field('id')->select();
            $arr = [];

            if(!empty($goods_id)){
                foreach ($goods_id as $key=>$value){
                    $arr[] = $value['id'];
                }
            }
            $goods = '';
            if(!empty($arr)){
                $goods = implode(',',$arr);
            }
            if($goods){
                $where[] = ['goods_id','in',[$goods]];
            }
        }
        $totalNum = Comment::where($where)->count();
        if(request()->isAjax()){
            $page = isset($_GET['page']) ? $_GET['page'] : '1';
            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
            $startNum = ($page - 1) * $limit;
            $totalPage = ceil($totalNum/$limit);
            $list = Comment::where($where)->limit($startNum.','.$limit)->order('id asc')->select();
            if(empty($list)){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage,'count'=>$totalNum]);
            }elseif($page > $totalPage){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage+1,'count'=>$totalNum]);
            }else{
                $nList = array();
                if(!empty($list)){
                    foreach($list as $k=>$vo){
                        $nList[$k] = $vo;
                        $goods= \app\common\model\Goods::where(['id'=>$vo['goods_id']])->field('title')->find();
                        $user= User::where(['uid'=>$vo['user_id']])->field('nickname')->find();

                        $nList[$k]['goods_title'] = $goods['title'];
                        $nList[$k]['nickname'] = $user['nickname'];
                        $nList[$k]['status_name'] = $vo['status'] == 1 ? '显示' :'隐藏';
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

}