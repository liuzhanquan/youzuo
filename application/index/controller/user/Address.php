<?php
namespace app\index\controller\user;
use \app\backman\model\Region;
use \app\index\controller\Api;


class Address extends Api{


	/**
	 * 获取收货地址列表
	 * @DateTime 2019-07-28
	 * @param    [type]
	 * @return   [type]     [description]
	 */
	public function getlist(){
		
		return $this->renderError();	
	}

	/**
	 * 获取全国省市区
	 */
	public function getregion(){
		$region = json_encode(Region::getCacheTree());
		return $this->renderSuccess($region);
	}

}