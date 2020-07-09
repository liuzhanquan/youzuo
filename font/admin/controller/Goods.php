<?php
namespace app\admin\controller;
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
        if($title){
            $where[] = ['title','like',"%{$title}%"];
        }
        if( !empty(input('title')) ){
            $where[] = ['title', 'like', "%".input('title')."%"];
        }
        if( !empty(input('cid')) ){
            //获取一级分类的子分类
            $cid = categorySelSon('cus_category',input('cid'),true);
            $where[] = ['cid','in',$cid];
        }
        $totalNum = GoodsItem::where($where)->count();
        $list = Category::where('type','1')->where([['id','neq',51]])->select();
        $cateObj = new \lib\Category(['id','parent_id','name','cname']);
        $category = $cateObj->getTree($list);
        if(request()->isAjax()){
            $page = isset($_GET['page']) ? $_GET['page'] : '1';
            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
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
            $this->assign('category',$category);
            $this->assign('totalNum',$totalNum);
            return view();
        }
    }

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
                $objReader = PHPExcel_IOFactory::createReader('Excel5');
            }
            
            $obj_PHPExcel =$objReader->load($file_name, $encode = 'utf-8');  //加载文件内容,编码utf-8  
            echo "<pre>";  
            $excel_array=$obj_PHPExcel->getsheet(0)->toArray();   //转换为数组格式  
            array_shift($excel_array);  //删除第一个数组(标题);  
            $data = [];  
            $i=0;  
            foreach($excel_array as $k=>$v) {  
                $data[$k]['name'] = $v[0];  
                $data[$k]['type'] = $v[1];  
                $data[$k]['type'] = $v[1];  
                $data[$k]['type'] = $v[1];  
                $data[$k]['type'] = $v[1];  
                $i++;  
            }  

            dump($data);exit();
           $success=Db::name('fuzhuang')->insertAll($data); //批量插入数据  这里的数据表改为你需要的。
           //$i=  
           $error=$i-$success;  
           $this->success('导入成功{$success}条记录，失败{$error}条记录','lan/index');
            // echo "总{$i}条，成功{$success}条，失败{$error}条。";  
           // Db::name('t_station')->insertAll($city); //批量插入数据  
        }else{  
            // 上传失败获取错误信息  
              
            return json($file->getError());
        }  
  
    }

    public function meal_index(){
        $title = isset($_GET['title']) ? $_GET['title'] : '';
        $where[] = ['cid','eq',51];
        if($title){
            $where[] = ['title','like',"%{$title}%"];
        }
        $totalNum = GoodsItem::where($where)->count();
        if(request()->isAjax()){
            $page = isset($_GET['page']) ? $_GET['page'] : '1';
            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
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
                        $nList[$k]['text'] = $vo['cid']['text'];
                        $nList[$k]['op'] = url('meal',['id'=>$vo['id']]);
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
     * 套餐列表
     * @return \think\response\Json|\think\response\View
     */
    public function meal_list(){
        $where = [];
        $totalNum = Meal::where($where)->count();
        if(request()->isAjax()){
            $page = isset($_GET['page']) ? $_GET['page'] : '1';
            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
            $startNum = ($page - 1) * $limit;
            $totalPage = ceil($totalNum/$limit);
            $list = Meal::where($where)->limit($startNum.','.$limit)->order('id desc')->select();
            if(empty($list)){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage,'count'=>$totalNum]);
            }elseif($page > $totalPage){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage+1,'count'=>$totalNum]);
            }else{
                $nList = array();
                if(!empty($list)){
                    foreach($list as $k=>$vo){
                        $nList[$k] = $vo;
                        $data = unserialize($vo['data']);
                        $str = '';
                        if(!empty($data)){
                            foreach ($data as $kk=>$v){
                                $str .= Db::name('agent_level')->where('id',$kk)->value('name').":{$v}元，";
                            }
                        }
                        $nList[$k]['data'] = $str;
                        $nList[$k]['op'] = url('meal_list_op',['id'=>$vo['id']]);
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
     * 套餐
     */
    public function meal_list_op($id = 0){
        if(!request()->isPost()){
            $info = Meal::get($id);
            $this->assign('info',$info);
            $levels = unserialize($info['data']);
            $level = AgentLevel::select();
            $this->assign('level',$level);
            $this->assign('levels',$levels);

            $list = Category::where('type','1')->where([['id','neq',51]])->select();
            $cateObj = new \lib\Category(['id','parent_id','name','cname']);
            $relist = $cateObj->getTree($list);
            $express = Delivery::order('id desc')->select();
            $fen = unserialize($info['fen_data']);
            $level = AgentLevel::select();
            $this->assign('level',$level);
            $this->assign('fen',$fen);
            $this->assign('express',$express);
            $this->assign('list',$relist);
            return view();
        }else{
            $obj = new Meal();
            $this->data['fen_data'] = serialize($this->data['fen']);
            $this->data['level_data'] = serialize($this->data['level']);
            $this->data['data'] = serialize($this->data['level']);
            if($this->data['id']){
                // 编辑
                AdminLog($this->admin['id'],'修改套餐【'.$this->data['name'].'】信息');
                $state = $obj->allowField(true)->save($this->data,['id'=>$id]);
            }else{
                // 新增
                AdminLog($this->admin['id'],'新增套餐【'.$this->data['name'].'】');
                $state = $obj->allowField(true)->save($this->data);
            }
            if($state){
                return $this->success('操作成功',url('meal_list'));
            }else{
                return $this->error($obj->getError());
            }
        }
    }

    /**
     * 套餐
     */
    public function meal($id = 0){
        if(!request()->isPost()){
            $info = GoodsItem::get($id);
            $this->assign('info',$info);
            $list = Category::where('type','1')->where([['id','neq',51]])->select();
            $cateObj = new \lib\Category(['id','parent_id','name','cname']);
            $relist = $cateObj->getTree($list);
            $express = Delivery::order('id desc')->select();
            $info = GoodsItem::get($id);
            $photo = unserialize($info['photo']);
            $fen = unserialize($info['fen_data']);
            $levels = unserialize($info['level_data']);
            $level = AgentLevel::select();
            $this->assign('photo',$photo);
            $this->assign('level',$level);
            $this->assign('levels',$levels);
            $this->assign('info',$info);
            $this->assign('fen',$fen);
            $this->assign('express',$express);
            $this->assign('list',$relist);
            return view();
        }else{
            $obj = new GoodsItem();
            $this->data['cid'] = 51;
            $this->data['image'] = $this->data['photo'][0];
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
                return $this->success('操作成功',url('meal_index'));
            }else{
                return $this->error($obj->getError());
            }
        }
    }
    public function option($id = 0){
        if(!request()->isPost()){
            $list = Category::where('type','1')->where([['id','neq',51]])->select();
            $cateObj = new \lib\Category(['id','parent_id','name','cname']);
            $relist = $cateObj->getTree($list);
            $info = GoodsItem::get($id);
            $photo = unserialize($info['photo']);
            $this->assign('photo',$photo);
            $this->assign('info',$info);
            $this->assign('list',$relist);
            return view();
        }else{
            $obj = new GoodsItem();
            $this->data['image'] = $this->data['photo'][0];
            $this->data['photo'] = serialize($this->data['photo']);
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

    public function category(){
        $list = Category::where('type','1')->select();
        $cateObj = new \lib\Category(['id','parent_id','name','cname']);
        $relist = $cateObj->getTree($list);
        $this->assign('list',$relist);
        return view();
    }

    public function category_op($id = 0){
        if(!request()->isPost()){
            $info = Category::get($id);
            $group = Category::where('parent_id','0')->where('type','1')->select();
            $this->assign('group',$group);
            $this->assign('info',$info);
            return view();
        }else{
            $obj = new Category();
            if($this->data['id']){
                // 编辑
                AdminLog($this->admin['id'],'修改产品分类【'.$this->data['name'].'】信息');
                $state = $obj->saveData($this->data,'edit');
            }else{
                // 新增
                AdminLog($this->admin['id'],'新增产品分类【'.$this->data['name'].'】');
                $state = $obj->saveData($this->data);
            }
            if($state){
                return $this->success('操作成功',url('category'));
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