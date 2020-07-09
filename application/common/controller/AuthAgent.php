<?php
namespace app\common\controller;
use \app\common\model\Config;


class AuthAgent extends Controller{


	public $agent = array();
	public $data = array();
	public $config = array();


	public function initialize(){
		parent::initialize();
		$this->checkLogin();
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

		$userId = cookie('agentId');
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
        
		$user = $this->agent = Admin::where(['id'=>$userId])->find();
		$this->assign('agent',$user);
	}

}