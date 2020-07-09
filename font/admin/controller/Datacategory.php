<?php
namespace app\admin\controller;
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

        $list = DataCategoryItem::where('type','1')->select();
        $category = [];
        foreach( $list as $item ){
            if( $item['parent_id'] == 0 ){
                $category[] = $item;
            }
        }
        $cateObj = new \lib\Category(['id','parent_id','name','cname']);
        $relist = $cateObj->getTree($list);
        $this->assign('category',$category);
        $this->assign('list',$relist);
        return view();
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


}