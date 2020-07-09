<?php
namespace app\admin\controller;
use app\backman\model\Meal;
use \app\common\controller\AuthBack;
use app\common\model\Detection as DetectionItem;
use app\common\model\DetectionSon;
use app\common\model\DetectionSpec;
use app\common\model\DataCategory;
use app\common\model\Staff;
use app\common\model\CusCategory;
use lib\CashStatus;
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
                        $nList[$k]['text'] = $vo['cid']['text'];
                        $nList[$k]['op'] = url('option',['id'=>$vo['id']]);
                        $nList[$k]['lc'] = url('lcindex',['id'=>$vo['id']]);
                        $nList[$k]['content'] = html_entity_decode($vo['content']);
                        $nList[$k]['num'] = DetectionSon::where('parent_id',$vo['id'])->count();
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


    public function lcindex($id = 0){
        $model = new DetectionItem;
        $model = new DetectionSon;
       
        $where['parent_id'] = $id;
        $info = DetectionItem::get($id);
        $totalNum = DetectionSon::where($where)->count();
        if(request()->isAjax()){
            $page = isset($_GET['page']) ? $_GET['page'] : '1';
            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
            $startNum = ($page - 1) * $limit;
            $totalPage = ceil($totalNum/$limit);
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
                        $nList[$k] = $vo;
                        $nList[$k]['input_staff'] = implode(',',$arr);
                        $nList[$k]['op'] = url('lcoption',['pid'=>$id,'id'=>$vo['id']]);
                        $nList[$k]['spec'] = url('spec',['pid'=>$id,'id'=>$vo['id']]);
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
     * 获取流程信息
     * @param int $id
     * @return \think\response\View|void
     */
    public function getson(){
        if( request()->isAjax() ){
            $data = $this->request->param();
            $list = DetectionSon::where(['parent_id'=>$data['id'],'status'=>1])->order('sort asc')->field('id,d_son_sn,name,type')->select();
            $nlist = [];
            
            foreach( $list as $key=>$item ){
                
                $list[$key]['type'] = $item['type']?'多次': '单次';
                //$list[$key]['link'] = $this->request->host().'/'.$this->request->controller().'/spec/'.$item['d_son_sn'];
                $list[$key]['link'] = '<a style="color:yellow" href="'.url('spec',['pid'=>$data['id'],'id'=>$item['id']]).'" target="_blank">信息表单预览</a>';
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
     * 删除流程
     * @param int $id
     * @return \think\response\View|void
     */
    public function sondel(){
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
     * 编辑检测流程信息
     * @param int $id
     * @return \think\response\View|void
     */
    public function option($id = 0){
        if(!request()->isPost()){
            $info = DetectionItem::get($id);
            $this->assign('info',$info);
            return view();
        }else{
            $obj = new DetectionItem();
            if($this->data['id']){
                // 编辑
                AdminLog($this->admin['id'],'修改流程【'.$this->data['name'].'】信息');
                $state = $obj->saveData($this->data,'edit');
            }else{
                // 新增
                AdminLog($this->admin['id'],'新增流程【'.$this->data['name'].'】');
                $state = $obj->saveData($this->data);
            }
            if($state){
                if($this->data['id']){
                    return $this->success('操作成功',url('index'));
                }else{
                    return $this->success('操作成功',url('lcoption',['pid'=>$state]));
                }
                
            }else{
                return $this->error($obj->getError());
            }
        }
    }

     /**
     * 编辑检测流程环节
     * @param int $id
     * @return \think\response\View|void
     */
    public function lcoption($pid = 0,$id = 0){
        if(!request()->isPost()){
            $parent = DetectionItem::get($pid);
            $info = DetectionSon::get($id);
            $staff = Staff::where('status',1)->select();
            $this->assign('parent',$parent);
            $this->assign('info',$info);
            $this->assign('staff',$staff);
            return view();
        }else{
            
            $obj = new DetectionSon();
           
            if($this->data['id']){
                // 编辑
                AdminLog($this->admin['id'],'修改流程环节【'.$this->data['name'].'】信息');
                $state = $obj->saveData($this->data,'edit');
            }else{
                // 新增
                AdminLog($this->admin['id'],'新增流程环节【'.$this->data['name'].'】');
                $state = $obj->saveData($this->data);
            }
            if($state){
                if($this->data['id']){
                    return $this->success('操作成功',url('lcindex',['pid'=>$this->data['parent_id']]));
                }else{
                    return $this->success('操作成功',url('spec',['pid'=>$this->data['parent_id'],'id'=>$state])); 
                }
                
            }else{
                return $this->error($obj->getError());
            }
        }
    }

    /**
     * 流程环节规则设置
     * @param int $id
     * @return \think\response\View|void
     */
    public function spec($pid = 0,$id = 0){
        if(!request()->isPost()){
            $parent = DetectionItem::get($pid);
            $son = DetectionSon::get($id);
            $info = DetectionSpec::where('d_son_sn',$son['d_son_sn'])->find();
            $DB_DC = DataCategory::where('type',1)->order('sort asc')->select();
            
            $datacate = $this->categoryJson($DB_DC);
            
            $C_cate = $this->categorySel();
            
            $list = [];
            if( !empty($info) ){
                $list = json_decode($info['spec'],true);
                $list = spec_data_select($list,$son['d_son_sn']);
                
            }
            
            $this->assign('parent',$parent);
            $this->assign('son',$son);
            $this->assign('info',$info);
            $this->assign('list',$list);
            $this->assign('datacate',$datacate);
            $this->assign('C_cate',$C_cate);
            // dump($C_cate);exit();
            return view();
        }else{
            
            $obj = new DetectionSpec();
            $status = DetectionSpec::where('d_son_sn',$this->data['d_son_sn'])->count();
            
            if($status){
                // 编辑
                AdminLog($this->admin['id'],'修改流程环节规则【'.$this->data['d_son_sn'].'】信息');
                $state = $obj->saveData($this->data,'edit');
            }else{
                // 新增
                AdminLog($this->admin['id'],'新增流程环节规则【'.$this->data['d_son_sn'].'】');
                $state = $obj->saveData($this->data);
            }
            if($state){
                return $this->success('操作成功',url('lcindex',['pid'=>$this->data['parent_id']]));
            }else{
                return $this->error($obj->getError());
            }
        }
    }

    private function categorySel(){
        $res['table']   = config('cateTableArr');
        $res['name']    = config('cateNameArr');
        
        $arr = [];
        foreach( $res['table'] as $key=>$item ){
            $data = DB::name($item)->where('type',1)->order('sort asc')->select();
            $res['list'][$key] = $this->categoryJson($data,true,$item);
        }
        $res['table'] = json_encode($res['table']);
        $res['name'] = json_encode($res['name']);
        return $res;

    }


    private function categoryArr($data, $status = true){
        $res = [];
        $arr = [];
        foreach( $data as $item ) {
            if( $item['parent_id'] == 0 ){
                
                $arr['id'][] = $item['id'];
                $arr['name'][] = $item['name'];
                
            }
        }
        $res['id'] = $arr['id'];
        $res['name'] = $arr['name'];

        foreach( $data as $item ) {
            foreach( $res['id'] as $key=>$i ){
                if( $item['parent_id'] == $i ){
                    $res['sonid'][$key][] = $item['id'];
                    $res['sonname'][$key][] = $item['name'];
                }
            }
            
        }
        if( $status ){
            foreach( $res as $key=>$item ){
                $res[$key] = json_encode($item);
            }    
        }
        
        return $res;

    }

    private function categoryJson($data,  $status = true, $table = ''){
        $res = [];
        $arr = [];
        $info = 0;
        foreach( $data as $item ) {
            if( $item['parent_id'] == 0 ){
                if( $table ){
                    $info = DB::name($table)->where('parent_id',$item['id'])->count();
                }
                if( $info || $table == '' ){
                    $arr['id'][] = $item['id'];
                    $arr['name'][] = $item['name'];
                }
            }
        }
        $res['id'] = $arr['id'];
        $res['name'] = $arr['name'];

        foreach( $data as $item ) {
            foreach( $res['id'] as $key=>$i ){
                if( $item['parent_id'] == $i ){
                    $res['sonid'][$key][] = $item['id'];
                    $res['sonname'][$key][] = $item['name'];
                }
            }
            
        }
        if( $status ){
            foreach( $res as $key=>$item ){
                $res[$key] = json_encode($item);
            }    
        }
        
        return $res;

    }

    public function category(){

        $list = CusCategory::where('type','1')->select();
        $cateObj = new \lib\Category(['id','parent_id','name','cname']);
        $relist = $cateObj->getTree($list);
        $this->assign('list',$relist);
        return view();
    }

    public function category_op($id = 0){
        if(!request()->isPost()){
            $info = CusCategory::get($id);
            $group = CusCategory::where('parent_id','0')->where('type','1')->select();
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

    



}