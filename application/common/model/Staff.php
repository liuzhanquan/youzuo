<?php

/**
 * @Author: Azaz；QQ：826355918
 * @Date:   2018-12-11 23:09:08
 * @Last Modified by:   Azaz
 * @Last Modified time: 2019-02-22 02:25:49
 */

namespace app\common\model;
use \think\Model;

class Staff extends Model{

	protected $pk = 'id';
    protected $name = 'staff';


    /**
     * 数据完成
    **/
    public function saveData($data = [],$type = 'add'){
        if( empty($data['staff_sn']) ){
            $data['staff_sn'] = 'YG'.get_order_sn();
        }else{
            $where[] = ['staff_sn','eq',$data['staff_sn']];
			$where[] = ['status','eq',1];
			
            if( $data['id'] ){
                $where[] = ['id','neq',$data['id']];
            }
            $count = $this->where($where)->count();
            if( $count ){
                $this->error = '员工编号已存在';
                return false;
            }
        }
        
        if($data['phone']){
            $nwhere[] = ['phone','eq',$data['phone']];
            $nwhere[] = ['status','eq',1];
            if( $data['id'] ){
                $nwhere[] = ['id','neq',$data['id']];
            }
            $count = $this->where($nwhere)->count();
            if( $count ){
                $this->error = '账号已存在';
                return false;
            }
        }else{
            $this->error = '手机号不能为空';
            return false;
        }

        if($type == 'add'){
            $data['timestamp'] = date('Y-m-d H:i:s');
            $id = $state = $this->allowField(true)->save($data);
        }
        if($type == 'edit'){
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


    /**
     * 员工个人信息修改
    **/
    public function modifyInfo($data,$userInfo){

        $state = $this->allowField(true)->save($data,['id'=>$userInfo['id']]);
        if($state){
            return true;
        }else{
            $this->error = $this->getError() ? $this->getError() : '没有可修改的数据！';
            return false;
        }
        return false;
    }



}