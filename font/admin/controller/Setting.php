<?php
namespace app\admin\controller;
use \app\common\controller\AuthBack;
use \app\common\model\Admin;
use \app\common\model\AdminLog;
use \app\common\model\Group;
use \app\common\model\Config;
use \app\backman\model\SmsTpl;
use \app\backman\model\SmsLog;

class Setting extends AuthBack{


    public function initialize(){
        parent::initialize();
    }

    public function index(){
        if(!request()->isPost()){
            $list = Config::where('is_sys','1')->select();
            $this->assign('list',$list);
            return view();
        }else{
            $obj = new Config();
            $state = $obj->saveInfo($this->data);
            if(!$state){
                return $this->error('保存失败');
            }else{
                AdminLog($this->admin['id'],'修改系统配置');
                return $this->success('保存成功');
            }
        }
    }

    public function uploads(){
        if(!request()->isPost()){
            $tpl_data = $this->config['upload'];
            $show_data = unserialize($tpl_data);
            $this->assign('info',$show_data);
            return view();
        }else{
            $obj = new Config();
            $updata['upload'] = serialize($this->data);
            $state = $obj->saveInfo($updata);
            if(!$state){
                return $this->error('保存失败');
            }else{
                AdminLog($this->admin['id'],'修改上传配置');
                return $this->success('保存成功');
            }
        }
    }

    public function sms(){
        if(!request()->isPost()){
            $tpl_data = $this->config['sms'];
            $show_data = unserialize($tpl_data);
            $this->assign('info',$show_data);
            return view();
        }else{
            $updata['sms'] = serialize($this->data);
            $obj = new Config();
            $state = $obj->saveInfo($updata);
            if(!$state){
                return $this->error('保存失败');
            }else{
                AdminLog($this->admin['id'],'修改短信配置');
                return $this->success('保存成功');
            }
        }
    }

    public function sms_tpl(){
        $totalNum = SmsTpl::count();
        if(request()->isAjax()){
            $page = isset($_GET['page']) ? $_GET['page'] : '1';
            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
            $startNum = ($page - 1) * $limit;
            $totalPage = ceil($totalNum/$limit);
            $list = SmsTpl::limit($startNum.','.$limit)->order('id desc')->select();
            if(empty($list)){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage,'count'=>$totalNum]);
            }elseif($page > $totalPage){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage+1,'count'=>$totalNum]);
            }else{
                $nList = array();
                if(!empty($list)){
                    foreach($list as $k=>$vo){
                        $nList[$k] = $vo;
                        $nList[$k]['op'] = url('sms_tpl_op',['id'=>$vo['id']]);
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

    public function sms_tpl_op($id = 0){
        if(!request()->isPost()){
            $info = SmsTpl::get($id);
            $this->assign('info',$info);
            return view();
        }else{
            $obj = new SmsTpl();
            if($this->data['id']){
                // 编辑
                AdminLog($this->admin['id'],'编辑短信模板【'.$this->data['title'].'】');
                $state = $obj->saveData($this->data,'edit');
            }else{
                // 新增
                AdminLog($this->admin['id'],'新增短信模板【'.$this->data['title'].'】');
                $state = $obj->saveData($this->data);
            }
            if($state){
                return $this->success('操作成功',url('sms_tpl'));
            }else{
                return $this->error($obj->getError());
            }

        }
    }

    public function sms_log(){
        $totalNum = SmsLog::count();
        if(request()->isAjax()){
            $page = isset($_GET['page']) ? $_GET['page'] : '1';
            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
            $startNum = ($page - 1) * $limit;
            $totalPage = ceil($totalNum/$limit);
            $list = SmsLog::limit($startNum.','.$limit)->order('id desc')->select();
            if(empty($list)){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage,'count'=>$totalNum]);
            }elseif($page > $totalPage){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage+1,'count'=>$totalNum]);
            }else{
                $return = [
                    'code'=>0,
                    'msg'=>'',
                    'count'=>$totalNum,
                    'data'=>$list
                ];
                return json($return);
            }
        }else{
            $this->assign('totalNum',$totalNum);
            return view();
        }
    }

    public function user(){
        $totalNum = Admin::alias('a')->join('group b','a.role_id = b.id')->count();
        if(request()->isAjax()){
            $page = isset($_GET['page']) ? $_GET['page'] : '1';
            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
            $startNum = ($page - 1) * $limit;
            $totalPage = ceil($totalNum/$limit);
            $list = Admin::alias('a')->join('group b','a.role_id = b.id')->field('a.*,b.name,b.is_sys')->limit($startNum.','.$limit)->order('a.reg_time desc')->select();
            if(empty($list)){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage,'count'=>$totalNum]);
            }elseif($page > $totalPage){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage+1,'count'=>$totalNum]);
            }else{
                $nList = array();
                if(!empty($list)){
                    foreach($list as $k=>$vo){
                        $nList[$k]['id'] = $vo['id'];
                        $nList[$k]['name'] = $vo['username'];
                        $nList[$k]['group'] = $vo['name'];
                        $nList[$k]['status'] = $vo['status'];
                        $nList[$k]['reg'] = $vo['reg_time'];
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
    public function option($id = 0){
        if(!request()->isPost()){
            $info = Admin::get($id);
            $group = Group::select();
            $this->assign('group',$group);
            $this->assign('info',$info);
            return view();
        }else{
            $obj = new Admin();
            if($this->data['id']){
                // 编辑
                $info = Admin::get($this->data['id']);
                AdminLog($this->admin['id'],'修改管理员【'.$info['username'].'】的信息');
                $state = $obj->saveData($this->data,'edit');
            }else{
                // 新增
                AdminLog($this->admin['id'],'新增管理员【'.$this->data['username'].'】');
                $state = $obj->saveData($this->data);
            }
            if($state){
                return $this->success('操作成功',url('user'));
            }else{
                return $this->error($obj->getError());
            }

        }
    }

    public function group(){
        $totalNum = Group::count();
        if(request()->isAjax()){
            $page = isset($_GET['page']) ? $_GET['page'] : '1';
            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
            $startNum = ($page - 1) * $limit;
            $totalPage = ceil($totalNum/$limit);
            $list = Group::limit($startNum.','.$limit)->order('id desc')->select();
            if(empty($list)){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage,'count'=>$totalNum]);
            }elseif($page > $totalPage){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage+1,'count'=>$totalNum]);
            }else{
                $nList = array();
                if(!empty($list)){
                    foreach($list as $k=>$vo){
                        $nList[$k] = $vo;
                        $nList[$k]['op'] = url('group_op',['id'=>$vo['id']]);
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

    public function group_op($id = 0){
        if(!request()->isPost()){
            $info = Group::where('id',$id)->find();
            $menu = array();
            if(!empty($info)){
                $menu = explode(',', $info['menu_power']);
            }
            $this->assign('menu', $menu);
            $this->assign('info', $info);
            return $this->fetch();
        }else{
            $data = $this->data;
            $obj = new Group();
            if(isset($data['menu_power'])){
                $data['menu_power'] = implode(',', $data['menu_power']);
            }else{
                $data['menu_power'] = '';
            }
            if(isset($data['power'])){
                $data['power'] = implode(',', $data['power']);
            }else{
                $data['power'] = '';
            }
            if($data['id']){
                // 编辑
                AdminLog($this->admin['id'],'修改权限组【'.$this->data['name'].'】');
                $state = $obj->saveData($data,'edit');
            }else{
                // 新增
                AdminLog($this->admin['id'],'新增权限组【'.$this->data['name'].'】');
                $state = $obj->saveData($data);
            }
            if($state){
                return $this->success('操作成功',url('group'));
            }else{
                return $this->error($obj->getError());
            }
        }
    }

    public function log(){
        if($this->adminGroup['is_sys'] == 1){
            $totalNum = AdminLog::alias('a')->join('admin b','a.user_id = b.id')->field('a.*,b.username')->count();
        }else{
            $totalNum = AdminLog::alias('a')->join('admin b','a.user_id = b.id')->field('a.*,b.username')->where('a.user_id',$this->admin['id'])->count();
        }
        if(request()->isAjax()){
            $page = isset($_GET['page']) ? $_GET['page'] : '1';
            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
            $startNum = ($page - 1) * $limit;
            $totalPage = ceil($totalNum/$limit);
            if($this->adminGroup['is_sys'] == 1){
                $list = AdminLog::alias('a')->join('admin b','a.user_id = b.id')->field('a.*,b.username')->limit($startNum.','.$limit)->order('a.timestamp desc')->select();
            }else{
                $list = AdminLog::alias('a')->join('admin b','a.user_id = b.id')->field('a.*,b.username')->where('a.user_id',$this->admin['id'])->limit($startNum.','.$limit)->order('a.timestamp desc')->select();
            }
            
            if(empty($list)){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage,'count'=>$totalNum]);
            }elseif($page > $totalPage){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage+1,'count'=>$totalNum]);
            }else{
                $return = [
                    'code'=>0,
                    'msg'=>'',
                    'count'=>$totalNum,
                    'data'=>$list
                ];
                return json($return);
            }
        }else{
            $this->assign('totalNum',$totalNum);
            return view();
        }

        $totalNum = AdminLog::where('user_id',$this->admin['id'])->count();
        if(request()->isAjax()){
            $page = isset($_GET['page']) ? $_GET['page'] : '1';
            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
            $startNum = ($page - 1) * $limit;
            $totalPage = ceil($totalNum/$limit);
            $list = AdminLog::where('user_id',$this->admin['id'])->limit($startNum.','.$limit)->order('id desc')->select();
            if(empty($list)){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage,'count'=>$totalNum]);
            }elseif($page > $totalPage){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage+1,'count'=>$totalNum]);
            }else{
                $return = [
                    'code'=>0,
                    'msg'=>'',
                    'count'=>$totalNum,
                    'data'=>$list
                ];
                return json($return);
            }
        }else{
            $this->assign('totalNum',$totalNum);
            return view();
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
                case 'user':
                    $info = Admin::get($id);
                    AdminLog($this->admin['id'],'删除管理员【'.$info['username'].'】');
                    $state = db('admin')->where('id',$id)->delete();
                break;
                case 'group':
                    $info = Group::get($id);
                    $item = Admin::where('role_id',$id)->count();
                    if($item > 0){
                        return $this->error('请先删除名下管理员');die;
                    }
                    AdminLog($this->admin['id'],'删除权限组【'.$info['name'].'】');
                    $state = db('group')->where('id',$id)->delete();
                break;
                case 'smslog':
                    $info = SmsLog::get($id);
                    AdminLog($this->admin['id'],'删除短信记录【'.$info['phone'].'】');
                    $state = db('sms_log')->where('id',$id)->delete();
                break;
            }
            if($state){
                return $this->success('删除成功');
            }else{
                return $this->error('删除失败');
            }
        }
    }

}