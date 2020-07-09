<?php
namespace app\admin\controller;
use \app\common\controller\AuthBack;
use app\common\model\Admin;
use \app\common\model\Category;
use \app\common\model\Video;
use \app\common\model\Friend;
use \app\common\model\Photo;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use think\Db;
use think\facade\Env;

class Material extends AuthBack{


    public function initialize(){
        parent::initialize();
    }

    public function index(){
        $cate = isset($_GET['cate']) ? $_GET['cate'] :0;
        $where = [];
        if($cate){
            $where[] = ['cid','eq',$cate];
        }

        $obj = new Photo();
        $list = $obj->getList($where);
        $this->assign('list',$list);
        $this->assign('page',$list->render());
        $this->assign('total',$list->total());
        $list = Category::where('type','3')->select();
        $cateObj = new \lib\Category(['id','parent_id','name','cname']);
        $relist = $cateObj->getTree($list);
        $this->assign('group',$relist);
        $this->assign('cate',$cate);
        return view();
    }
    public function index_op($id = 0){
        if(!request()->isPost()){
            $info = Photo::get($id);
            $list = Category::where('type','3')->select();
            $cateObj = new \lib\Category(['id','parent_id','name','cname']);
            $relist = $cateObj->getTree($list);
            $this->assign('group',$relist);
            $this->assign('info',$info);
            return view();
        }else{
            $obj = new Photo();
            AdminLog($this->admin['id'],'新增资料图库');
            $state = $obj->saveData($this->data);
            if($state){
                return $this->success('操作成功',url('index'));
            }else{
                return $this->error($obj->getError());
            }
        }
    }
    public function index_cate(){
        $list = Category::where('type','3')->select();
        $cateObj = new \lib\Category(['id','parent_id','name','cname']);
        $relist = $cateObj->getTree($list);
        $this->assign('list',$relist);
        return view();
    }
    public function index_cate_op($id = 0){
        if(!request()->isPost()){
            $info = Category::get($id);
            $list = Category::where(['type'=>'3','parent_id'=>0])->select();
            $cateObj = new \lib\Category(['id','parent_id','name','cname']);
            $relist = $cateObj->getTree($list);
            $this->assign('group',$relist);
            $this->assign('info',$info);
            return view();
        }else{
            $obj = new Category();
            $this->data['type'] = '3';
            if($this->data['id']){
                // 编辑
                AdminLog($this->admin['id'],'修改资料图库分类【'.$this->data['name'].'】信息');
                $state = $obj->saveData($this->data,'edit');
            }else{
                // 新增
                AdminLog($this->admin['id'],'新增资料图库分类【'.$this->data['name'].'】');
                $state = $obj->saveData($this->data);
            }
            if($state){
                return $this->success('操作成功',url('index_cate'));
            }else{
                return $this->error($obj->getError());
            }
        }
    }

    public function video(){
        $obj = new Video();
        $totalNum = Video::count();
        if(request()->isAjax()){
            $page = isset($_GET['page']) ? $_GET['page'] : '1';
            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
            $startNum = ($page - 1) * $limit;
            $totalPage = ceil($totalNum/$limit);
            $list = Video::limit($startNum.','.$limit)->order('sort asc')->select();
            if(empty($list)){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage,'count'=>$totalNum]);
            }elseif($page > $totalPage){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage+1,'count'=>$totalNum]);
            }else{
                $nList = array();
                if(!empty($list)){
                    foreach($list as $k=>$vo){
                        $nList[$k] = $vo;
                        $nList[$k]['cname'] = $vo['cid']['text'];
                        $nList[$k]['op'] = url('video_op',['id'=>$vo['id']]);
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

    public function video_op($id = 0){
        if(!request()->isPost()){
            $info = Video::get($id);
            $list = Category::where('type','2')->select();
            $cateObj = new \lib\Category(['id','parent_id','name','cname']);
            $relist = $cateObj->getTree($list);
            $this->assign('group',$relist);
            $videoCnfig = unserialize($this->config['upload']);
            $info['image'] = $videoCnfig['domain'].$info['video'].$info['image'];
            $this->assign('info',$info);
            return view();
        }else{
            //require_once Env::get('base_path') . '/vendor/qiniu/autoload.php';
            $obj = new Video();
            // 处理视频
            $videoCnfig = unserialize($this->config['upload']);
            if($videoCnfig['type'] == '2'){ // 开启七牛云后

//                $filePath = Env::get('root_path').'public'.request()->post('video');
//                $ext = explode('.',$filePath);
//                $ext = $ext[1];  //后缀
//                //获取当前控制器名称
//                //$controllerName=$this->getContro();
//                // 上传到七牛后保存的文件名
//                $key =substr(md5($filePath) , 0, 5). date('YmdHis') . rand(0, 9999) . '.' . $ext;
//                // 构建鉴权对象
//                $auth = new Auth($videoCnfig['ak'], $videoCnfig['sk']);
//                // 要上传的空间
//                $bucket = $videoCnfig['bucket'];
//                $domain = $videoCnfig['domain'];
//                $token = $auth->uploadToken($bucket);
//                // 初始化 UploadManager 对象并进行文件的上传
//                $uploadMgr = new UploadManager();
//                // 调用 UploadManager 的 putFile 方法进行文件的上传
//                list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
//
//
//                if ($err !== null) {
//                    return $this->error('上传七牛云失败，请关闭后重试');
//                    $data = ["err"=>1,"msg"=>$err,"data"=>""];
//                } else {
//                    //返回图片的完整URL
//                    // 获取视频的时长
//                    // 第一步先获取到到的是关于视频所有信息的json字符串
//                    $shichang = file_get_contents($domain .'/'. $ret['key'].'?avinfo');
//                    // 第二部转化为对象
//                    $shi =json_decode($shichang);
//                    // 第三部从中取出视频的时长
//                    //$chang = $shi->format->duration;
//                    // 获取封面
//                    //http://p3fczj25n.bkt.clouddn.com/8.mp4?vframe/jpg/offset/1
//                    //$vpic = $domain .'/'. $ret['key'].'?vframe/jpg/offset/1';
//
//                    $path =$domain .'/'. $ret['key'];
//                    $this->data['image'] = "?vframe/jpg/offset/1";
//                    $this->data['video_time'] = $shi->format->duration;
//                    $this->data['qiniu_path'] = '/'. $ret['key'];
//                    //$data = ["err"=>0,"msg"=>"上传完成","data"=>($domain .'/'. $ret['key'])];
//                }

//                $videoUrl = $videoCnfig['domain'].$this->data['video'];
//                $videoInfo = http_get($videoUrl."?avinfo");
//                $videoInfo = json_decode($videoInfo,true);
//                if(isset($videoInfo['format'])){
//                    $this->data['video_time'] = number_format($videoInfo['format']['duration']);
//                }
                //$this->data['image'] = "?vframe/jpg/offset/0";

                $videoUrl = $videoCnfig['domain'].$this->data['video'];
                $videoInfo = http_get($videoUrl."?avinfo");
                $videoInfo = json_decode($videoInfo,true);
                if(isset($videoInfo['format'])){
                    $this->data['video_time'] = number_format($videoInfo['format']['duration']);
                }
                $this->data['image'] = "?vframe/jpg/offset/0";
            }
            if($this->data['id']){
                // 编辑
                AdminLog($this->admin['id'],'修改视频素材【'.$this->data['title'].'】信息');
                $state = $obj->saveData($this->data,'edit');
            }else{
                // 新增
                AdminLog($this->admin['id'],'新增视频素材【'.$this->data['title'].'】');
                $state = $obj->saveData($this->data);
            }
            if($state){
                return $this->success('操作成功',url('video'));
            }else{
                return $this->error($obj->getError());
            }
        }
    }
    public function category_video(){
        $list = Category::where('type','2')->select();
        $cateObj = new \lib\Category(['id','parent_id','name','cname']);
        $relist = $cateObj->getTree($list);
        $this->assign('list',$relist);
        return view();
    }
    public function category_op($id = 0){
        if(!request()->isPost()){
            $info = Category::get($id);
            $list = Category::where('type','2')->select();
            $cateObj = new \lib\Category(['id','parent_id','name','cname']);
            $relist = $cateObj->getTree($list);
            $this->assign('group',$relist);
            $this->assign('info',$info);
            return view();
        }else{
            $obj = new Category();
            $this->data['type'] = '2';
            if($this->data['id']){
                // 编辑
                AdminLog($this->admin['id'],'修改视频分类【'.$this->data['name'].'】信息');
                $state = $obj->saveData($this->data,'edit');
            }else{
                // 新增
                AdminLog($this->admin['id'],'新增视频分类【'.$this->data['name'].'】');
                $state = $obj->saveData($this->data);
            }
            if($state){
                return $this->success('操作成功',url('category_video'));
            }else{
                return $this->error($obj->getError());
            }
        }
    }

    public function friend(){
        $totalNum = Friend::count();
        if(request()->isAjax()){
            $page = isset($_GET['page']) ? $_GET['page'] : '1';
            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
            $startNum = ($page - 1) * $limit;
            $totalPage = ceil($totalNum/$limit);
            $list = Friend::limit($startNum.','.$limit)->order('sort asc')->select();
            if(empty($list)){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage,'count'=>$totalNum]);
            }elseif($page > $totalPage){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage+1,'count'=>$totalNum]);
            }else{
                $nList = array();
                if(!empty($list)){
                    foreach($list as $k=>$vo){
                        $nList[$k] = $vo;
                        $nList[$k]['content'] = trim($vo['content']);
                        $nList[$k]['type'] = ($vo['type']== '1') ? '照片' : '视频';
                        $nList[$k]['op'] = url('friend_op',['id'=>$vo['id']]);
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

    public function friend_del(){
        if(!request()->isAjax()){
            return json(['code'=>'0']);
        }else{
            $id = (int) $_POST['id'];
            switch ($_POST['table']) {
                case 'friend': // 图库分类
                    AdminLog($this->admin['id'],'朋友圈素材【'.$id.'】');
                    $state = Db::name('friend')->where('id',$id)->delete();
                    break;
                case 'v_category': // 视频分类
                    $info = Category::get($id);
                    $item = Admin::where('role_id',$id)->count();
                    if($item > 0){
                        return $this->error('请先删除名下管理员');die;
                    }
                    AdminLog($this->admin['id'],'删除视频分类【'.$info['name'].'】');
                    $state = Db::name('category')->where('id',$id)->delete();
                    break;
                case 'photo': // 视频分类
                    AdminLog($this->admin['id'],'图片资料【'.$id.'】');
                    $state = Db::name($_POST['table'])->where('id',$id)->delete();
                    break;
                case 'video': // 视频分类
                    AdminLog($this->admin['id'],'图片视频【'.$id.'】');
                    $state = Db::name($_POST['table'])->where('id',$id)->delete();
                    break;
            }
            if($state){
                return $this->success('删除成功');
            }else{
                return $this->error('删除失败');
            }
        }
    }

    public function friend_op($id = 0){
        if(!request()->isPost()){
            $info = Friend::get($id);
            $photo = unserialize($info['image']);
            $this->assign('photo',$photo);
            $this->assign('info',$info);
            return view();
        }else{
            $obj = new Friend();
            $videoCnfig = unserialize($this->config['upload']);
            if($this->data['type'] == '2'){
                $videoUrl = $videoCnfig['domain'].$this->data['video'];
                $videoInfo = http_get($videoUrl."?avinfo");
                $videoInfo = json_decode($videoInfo,true);
                if(isset($videoInfo['format'])){
                    $this->data['video_time'] = number_format($videoInfo['format']['duration']);
                }
            }
            if(isset($this->data['image'])){
                $this->data['image'] = serialize($this->data['image']);
            }
            if($this->data['id']){
                // 编辑
                AdminLog($this->admin['id'],'修改朋友圈素材');
                $state = $obj->saveData($this->data,'edit');
            }else{
                // 新增
                AdminLog($this->admin['id'],'新增朋友圈素材');
                $state = $obj->saveData($this->data);
            }
            if($state){
                return $this->success('操作成功',url('friend'));
            }else{
                return $this->error($obj->getError());
            }
        }
    }

    /**
     * 删除操作
     * @DateTime 2019-03-27
     * @param    [type]
     * @return   [type]     [description]
     */
    public function del(){
        if(!request()->isAjax()){
            return json(['code'=>'0']);
        }else{
            $id = (int) $_POST['id'];
            switch ($_POST['table']) {
                case 'i_category': // 图库分类
                    $info = Category::get($id);
                    AdminLog($this->admin['id'],'删除图库分类【'.$info['name'].'】');
                    $state = Db::name('category')->where('id',$id)->delete();
                break;
                case 'v_category': // 视频分类
                    $info = Category::get($id);
                    $item = Admin::where('role_id',$id)->count();
                    if($item > 0){
                        return $this->error('请先删除名下管理员');die;
                    }
                    AdminLog($this->admin['id'],'删除视频分类【'.$info['name'].'】');
                    $state = Db::name('category')->where('id',$id)->delete();
                break;
                case 'photo': // 视频分类
                    AdminLog($this->admin['id'],'图片资料【'.$id.'】');
                    $state = Db::name($_POST['table'])->where('id',$id)->delete();
                    break;
                case 'video': // 视频分类
                    AdminLog($this->admin['id'],'图片视频【'.$id.'】');
                    $state = Db::name($_POST['table'])->where('id',$id)->delete();
                    break;
            }
            if($state){
                return $this->success('删除成功');
            }else{
                return $this->error('删除失败');
            }
        }
    }

    public function modifyPhoto(){
        $data = $this->request->param();
        
        $update[ $data['type']] = $data['value'];
        if( $data['type'] == 'name' ){
            AdminLog($this->admin['id'],'图片素材名称【'.$data['id'].'】');
        }else{
            AdminLog($this->admin['id'],'图片素材分组【'.$data['id'].'】');
        }
        
        $res = Db::name('photo')->where('id',$data['id'])->update($update);
        if(!$res){
            return json(['code'=>0,'msg'=>'请求失败']);
        }
        return json(['code'=>1,'msg'=>'请求成功']);
    }

    public function modifyPhotoAll(){
        $data = $this->request->param();
        $id = implode(',',$data['id']);
        $update['cid'] = $data['value'];
            AdminLog($this->admin['id'],'图片素材分组【'.$id.'】');
        
        $res = Db::name('photo')->where('id', 'in',$id)->update($update);
        if(!$res){
            return json(['code'=>0,'msg'=>'请求失败']);
        }
        return json(['code'=>1,'msg'=>'请求成功']);
    }



}