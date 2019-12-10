<?php
namespace app\common\controller;

use \think\Controller;
use \app\common\model\Admin;
use \app\common\model\Group;
use \app\common\model\Menu;
use \app\common\model\Config;
use think\Db;

class AuthBack extends Controller{

	public $admin = array();
	public $adminGroup = array();
	public $data = array();
	public $config = array();


	public function initialize(){
		parent::initialize();
		$this->checkLogin();
		$this->checkPurview();
		$this->getPath();
		$this->data = request()->param('',null,'htmlspecialchars');

	}
	/**
	 * 登录验证
	 * @author Azaz
	 * @time   2018-12-11T19:15:43+0800
	 * @return [type]
	 */
	protected function checkLogin(){

		$rootName = request()->root();
		$rootPath = explode('/', $rootName);

		if(isset($rootPath[1])){
			$authUrl = url('/auth/login');
		}else{
			$authUrl = url('/auth/login');
		}

		$userId = cookie('userId');
		if(!$userId){
			if(request()->isAjax()){
				return $this->error('请先登录',$authUrl);die;
			}else{
				return $this->redirect($authUrl);die;
			}
		}
        $config = Config::select();
        $configArr = [];
        if(!empty($config)){
            foreach($config as $k=>$vo){
                $configArr[$vo['name']] = $vo['value'];
            }
        }
        $this->config = $configArr;
        $this->assign('config',$configArr);
        
		$user = $this->admin = Admin::where(['id'=>$userId])->find();
		$userGroup = $this->adminGroup = Group::where(['id'=>$user->role_id])->find();
		$this->assign('admin',$user);
		$this->assign('adminGroup',$userGroup);
	}

    /**
     * 权限检测
     * @return bool
     */
    private function checkPurview() {
        $topKey = $this->request->controller(); // 控制器
        $action = $this->request->action(); // 操作方法
        $userRoleId = $this->admin['role_id'];
        $roleGroup = Group::where('id',$userRoleId)->find();
        $menuInfo = Menu::where(['model'=>$topKey,'action'=>$action])->find();
        if($roleGroup['is_sys'] <> 1){
            // 栏目权限
            $menu = Menu::where('id','in',$roleGroup['menu_power'])->order('sort asc')->select();
            $powerArr = explode(',', $roleGroup['power']);
            $powerArr[] = 'Index/index';
            $delArr = array();
            foreach($powerArr as $vo){
                $powarr = explode('/', $vo);
                if(count($powarr) >= 3){
                    $delArr[] = $powarr[2];
                }
            }
            $menuPower = $topKey.'/'.$action;
            if($action == 'del') {
                // 判断删除权限
                $table = $this->data['table'];
                if (!in_array($table, $delArr)) {
                    return $this->error(lang('role error'));
                }else{
                    if(!in_array($menuPower,$powerArr)){
                    return $this->error(lang('role error'));
                }
                }
            }
//            }else{
//                if(!in_array($menuPower,$powerArr)){
//                    return $this->error(lang('role error'));
//                }
//            }

        }else{
            // 超级管理员
            $menu = Menu::where('is_show','1')->order('sort asc')->select();
        }
        foreach($menu as $k=>$vo){
            $menu[$k] = $vo;
            $menu[$k]['action'] = $vo['action'];
            if(empty($vo['extend'])){
                $extStr = $vo['action'];
            }else{
                $extStr = $vo['action'].','.$vo['extend'];
            }
            $menu[$k]['extend'] = explode(',', $extStr);
            $menu[$k]['url'] = url($vo['model'].'/'.$vo['action']);
        }
        $li = new \lib\PHPTree(['id', 'parent']);
        $list = $li->makeTree($menu,array('parent_key'=>'parent_id','expanded'=>true));
        $this->assign('topNav', $list);
    }
    private function getPath(){
        $topKey = $this->request->controller(); // 控制器
        $action = $this->request->action(); // 操作方法
        $data = Menu::select();
        $where = array(
            'model'=>$topKey,
            'action'=>$action,
        );
        $info = Menu::where($where)->find();
        if(empty($info)){
            $info = Menu::where([
                ['model','=',$topKey],
                ['extend','like','%'.$action.'%']
            ])->find();
        }
        $path = array();
        if(!empty($info)){
            $categroy = new \lib\Category(['id','parent_id','name','cname']);
            $path = $categroy->getPath($data,$info['id']);
        }
       
        $this->assign('pathCurrent',$info);
        $this->assign('path',$path);
    }


    public function del(){
        if(request()->isAjax()){
            $data = $this->request->param();
            $table = $this->table($data['table']);
            AdminLog($this->admin['id'],'删除了'.$table.'【 id ： '.$this->data['id'].'】');
            //$res = DB::name($data['table'])->where('id',$data['id'])->delete();

            // if(!$res){
            //     return $this->error('删除失败');
            // }
            return $this->success('操作成功');
            
        }

    }

    private function table($table){
        $name = '';
        switch( $table ){
            case 'goods':
                $name = '产品【'.$table.'】';
                break;

            case 'category':
                $name = '产品分类【'.$table.'】';
                break;

            case 'customer':
                $name = '客户【'.$table.'】';
                break;

            case 'cus_category':
                $name = '客户分类【'.$table.'】';
                break;

            case 'staff':
                $name = '员工【'.$table.'】';
                break;

            case 'sta_category':
                $name = '员工分类【'.$table.'】';
                break;

            default: 
                $name = $table;
                break;

        }
        return $name;
    }

}
