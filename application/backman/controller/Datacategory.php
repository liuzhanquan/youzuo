<?php
namespace app\backman\controller;
use app\backman\model\Meal;
use \app\common\controller\AuthBack;
use app\common\model\DataCategory as DataCategoryItem;
use lib\CashStatus;
use think\Db;
use think\facade\Env;
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use Intervention\Image;
use GuzzleHttp;



class Datacategory extends AuthBack{


    public function initialize(){
        parent::initialize();
    }

    
    public function index(){
        $model = new DataCategoryItem;
        $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
        $title = isset($_GET['title']) ? $_GET['title'] : '';
        $where = [];
        $where[] = ['parent_id','eq',"0"];
        if($title){
            $lwhere[] = ['name','like',"%{$title}%"];
            $resarr = DataCategoryItem::where($lwhere)->field('id,parent_id')->select();
            $newidArr = [];
            foreach($resarr as $item){
                if( $item['parent_id'] == 0 ){
                    $newidArr[] = $item['id'];
                }else{
                    $newidArr[] = $item['parent_id'];
                }
            }
            $where[] = ['id','in',$newidArr];
        }
        
        $totalNum = DataCategoryItem::where($where)->count();
        if(request()->isAjax()){
            $power = DB::name('staff_power')->select();

            $page = isset($_GET['page']) ? $_GET['page'] : '1';
            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
            $startNum = ($page - 1) * $limit;
            $totalPage = ceil($totalNum/$limit);
            $list = DataCategoryItem::where($where)->limit($startNum.','.$limit)->order('id desc')->select();

            if(empty($list)){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage,'count'=>$totalNum]);
            }elseif($page > $totalPage){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage+1,'count'=>$totalNum]);
            }else{
                $nList = array();
                if(!empty($list)){
                    foreach($list as $k=>$vo){
                        $nList[$k] = $vo;
                        $nList[$k]['total'] = DB::name('spec_data')->where('did',$vo['id'])->count();
                        $nList[$k]['sontotal'] = DataCategoryItem::where('parent_id',$vo['id'])->count();
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

    public function dataadd(){

        $data  = $this->data;
        $ndata = [];
        $ndata['name'] = $data['dataname'];

        
        if($data['dataname']){
            $nwhere[] = ['name','eq',$data['dataname']];
            if( $data['dataid'] ){
                $nwhere[] = ['id','neq',$data['dataid']];
            }
            
            $count =DB::name('data_category')->where($nwhere)->count();
            if( $count ){
                return json('字典已存在');
            }
        }

        if( empty( $data['dataname'] )){
            return json('字典名称不能为空');
    }
        if( empty( $data['datasonname'] )){
            return json('字典数据不能为空');
        }


        if( $data['dataid'] ){
            $res = DB::name('data_category')->where('id',$data['dataid'])->update(['name'=>$data['dataname']]);
            
            $res = $data['dataid'];
        }else{
            $res = DB::name('data_category')->insertGetId(['name'=>$data['dataname'],'parent_id'=>0,'sort'=>100,'level'=>1]);
            if(!$res){
                return json('添加字典失败');
            }
        }

        if(!$res){
            return json('修改字典失败');
        }

        foreach( $data['datasonname'] as $item ){
            if( $item['id'] ){
                $result = DB::name('data_category')->where('id',$item['id'])->update(['name'=>$item['name']]);
                
            }else{
                $result = DB::name('data_category')->insertGetId(['name'=>$item['name'],'parent_id'=>$res,'sort'=>100,'level'=>2]);
                if(!$result){
                    return json('添加子字典失败');
                }
            }
            
        }

        if( !empty($data['dlearr']) ){
            $this->delArrSon($data['dlearr'],$res);
        }
        

        if( $res ){
            $return = [
                'code'=>1,
                'msg'=>'成功'
            ];
            return json($return);
        }
        return json('修改失败');
    }

    public function getdatason(){

        $data = $this->data;
        $res = [];
        if( $data['id'] ){
            $list = DB::name('data_category')->where('parent_id',$data['id'])->field('id,name')->select();
            foreach( $list as $key=>$item ){
                $res[$key]['id'] = $item['id'];
                $res[$key]['name'] = $item['name'];
            }
        }

        return json($res);

    }



    public function category_op($id = 0){
        if(!request()->isPost()){
            $info = DataCategoryItem::get($id);
            $group = DataCategoryItem::where('parent_id','0')->where('type','1')->select();
            $this->assign('group',$group);
            $this->assign('info',$info);
            return view();
        }else{
            $obj = new DataCategoryItem();
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
                return $this->success('操作成功',url('index'));
            }else{
                return $this->error($obj->getError());
            }
        }
    }

    /**
     * 检测流程删除
     */
    public function del(){
        
        $pid = DB::name('data_category')->where('id',$this->data['id'])->field('parent_id')->find();
        if( $pid['parent_id'] != 0 ){
            $Dcount = DB::name('data_category')->where('parent_id',$pid['parent_id'])->count();
            if( $Dcount <= 1 ){
                return $this->error('数据字典不能全部删除，必须留一个');
            }
        }
        
        $count = DB::name('spec_data')->where('did',$this->data['id'])->count();

        if( $count ){
            return $this->error('该数据字典有绑定的表格信息，无法删除');
        }else{
            AdminLog($this->admin['id'],'删除检测流程【'.$this->data['id'].'】信息');
            $res = DB::name('data_category')->where('id',$this->data['id'])->delete();
            if( $pid['parent_id'] == 0 ){
                $res = DB::name('data_category')->where('parent_id',$this->data['id'])->delete();
            }
            if( $res ){
                return $this->success('操作成功',url('index'));
            }else{
                return $this->error('删除失败');
            }
        }

    }



    /**
     * 检测流程删除
     */
    public function del2(){
        
        $pid = DB::name('data_category')->where('id',$this->data['id'])->field('parent_id')->find();
        if( $pid['parent_id'] != 0 ){
            $Dcount = DB::name('data_category')->where('parent_id',$pid['parent_id'])->count();
            if( $Dcount <= 1 ){
                return $this->error('数据字典不能全部删除，必须留一个');
            }
        }
        
        $count = DB::name('spec_data')->where('did',$this->data['id'])->count();

        if( $count ){
            return $this->error('该数据字典有绑定的表格信息，无法删除');
        }else{
            AdminLog($this->admin['id'],'删除检测流程【'.$this->data['id'].'】信息');
            $res = DB::name('data_category')->where('id',$this->data['id'])->delete();
            if( $pid['parent_id'] == 0 ){
                $res = DB::name('data_category')->where('parent_id',$this->data['id'])->delete();
            }
            if( $res ){
                $return = [
                    'code'=>1,
                    'msg'=>'成功'
                ];
                return json($return);
            }else{
                return $this->error('删除失败');
            }
        }

    }

    /**
     * 检测流程删除
     */
    public function delArrSon($arr,$pid){
        
        $count = DB::name('data_category')->where('parent_id',$pid)->count();
        $dCount = DB::name('data_category')->where('id','in',$arr)->count();
        
        if( $count - $dCount > 0 ){
            AdminLog($this->admin['id'],'删除检测流程【'.$pid.'】字典数据信息');
            $res = DB::name('data_category')->where('id','in',$arr)->delete();
            
        }else{
            return $this->error('数据字典不能全部删除，必须留一个');
        }

    }


}