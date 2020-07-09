<?php

/**
 * @Author: Azaz；QQ：826355918
 * @Date:   2018-12-11 23:09:08
 * @Last Modified by:   Azaz
 * @Last Modified time: 2019-02-22 02:25:49
 */

namespace app\common\model;
use \think\Model;
use \lib\Str;

class Admin extends Model{

	protected $pk = 'id';


    /**
     * 数据完成
    **/
    public function saveData($data = [],$type = 'add'){
        if($type == 'add'){
        	$data['reg_time'] = date('Y-m-d H:i:s');
        	$data['authkey'] = Str::randStr(10);
        	$data['password'] = sp_password($data['password'],$data['authkey']);
            $id = $state = $this->allowField(true)->save($data);
        }
        if($type == 'edit'){
        	$info = $this->find($data['id']);
        	$data['password'] = sp_password($data['password'],$info['authkey']);
            $state = $this->allowField(true)->save($data,['id'=>$data['id']]);
            $id = $data['id'];
        }
        if($state){
            return $id;
        }else{
            $this->error = $this->getError() ? $this->getError() : '没有可修改的数据！';
            return false;
        }
        return false;
    }
    
}