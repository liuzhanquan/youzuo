<?php

/**
 * @Author: jason
 * @Date:   2018-12-11 23:09:08
 * @Last Modified by:   jason
 * @Last Modified time: 2019-02-22 02:25:49
 */

namespace app\common\model;
use \think\Model;

class StaCategory extends Model{

	protected $pk = 'id';

    /**
     * 数据完成
    **/
    public function saveData($data = [],$type = 'add'){
        $parent = $this->where('id',$data['parent_id'])->find();
        $data['level'] = $parent['level']+1;
        if($type == 'add'){
            $id = $state = $this->allowField(true)->save($data);
        }
        if($type == 'edit'){
            $slef = $this->where('id',$data['id'])->find();
            if($data['parent_id'] == $data['id']){
                $this->error = "上级分类设定错误";
                return false;
            }
            if($parent['level'] > $slef['level']){
                $this->error = "上级分类请勿跨级设定";
                return false;
            }
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