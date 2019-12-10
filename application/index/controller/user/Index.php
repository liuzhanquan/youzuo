<?php
namespace app\index\controller\user;
use app\index\model\User;
use \app\index\controller\Api;

class Index extends Api{


	public function login(){
		if(!request()->isPost()){
			return $this->renderError('非法操作');die;
		}else{
			$model = new User;
	        return $this->renderSuccess([
	            'user_id' => $model->login(request()->param()),
	            'token' => $model->getToken()
	        ]);
		}
	}

}