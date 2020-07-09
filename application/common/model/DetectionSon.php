<?php

/**
 * @Author: Jason
 * @Date:   2018-12-11 23:09:08
 * @Last Modified by:   Jason
 * @Last Modified time: 2019-02-22 02:25:49
 */

namespace app\common\model;
use \think\Model;

class DetectionSon extends Model{

	protected $pk = 'id';
    protected $name = 'detection_son';

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
        if($type == 'add'){
            $data['created_time'] = date('Y-m-d H:i:s');
            $data['updated_time'] = date('Y-m-d H:i:s');
            if ( empty($data['d_son_sn']) ) { 
                $data['d_son_sn'] = get_order_sn();
            }
            $state = $this->allowField(true)->save($data);
            $id = $this->id;
        }
        if($type == 'edit'){
            $data['updated_time'] = date('Y-m-d H:i:s');
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