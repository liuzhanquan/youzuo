<?php

/**
 * @Author: Azaz；QQ：826355918
 * @Date:   2018-12-11 23:09:08
 * @Last Modified by:   Azaz
 * @Last Modified time: 2019-02-22 02:25:49
 */

namespace app\common\model;
use \think\Model;

class Detection extends Model{

	protected $pk = 'id';
    protected $name = 'detection';

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