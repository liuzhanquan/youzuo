<?php

/**
 * @Date:   2018-12-11 23:09:08
 * @Last Modified time: 2019-02-22 02:25:49
 */

namespace app\common\model;
use \think\Model;

class Config extends Model{

	protected $pk = 'id';


    public function saveInfo($data = array()) {
    	if(empty($data)){
    		$data = request()->param();
    	}
    	foreach($data as $k=>$vo){
			$upDa['value'] = $vo;
			$state = $this->where('name',$k)->update($upDa);
    	}
        return true;
    }
    
}