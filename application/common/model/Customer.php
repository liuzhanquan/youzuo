<?php

/**
 * @Author: Azaz；QQ：826355918
 * @Date:   2018-12-11 23:09:08
 * @Last Modified by:   Azaz
 * @Last Modified time: 2019-02-22 02:25:49
 */

namespace app\common\model;
use \think\Model;

class Customer extends Model{

	protected $pk = 'id';
    protected $name = 'customer';

    /**
     * 分类
     * @param $value
     * @return mixed
     */
    public function getCidAttr($value)
    {
        $info = CusCategory::where('id',$value)->value('name');
        return ['text' => $info, 'value' => $value];
    }
    /**
     * 数据完成
    **/
    public function saveData($data = [],$type = 'add'){
        
        if( empty($data['customer_sn']) ){
            $data['customer_sn'] = 'YW'.get_order_sn();
        }else{
            $where[] = ['customer_sn','eq',$data['customer_sn']];
            if( $data['id'] ){
                $where[] = ['id','neq',$data['id']];
            }
            $count = $this->where($where)->count();
            if( $count ){
                $this->error = '客户编号已存在';
                return false;
            }
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
}