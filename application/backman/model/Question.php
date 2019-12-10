<?php

/**
 * @Author: Azaz；QQ：826355918
 * @Date:   2018-12-11 23:09:08
 * @Last Modified by:   Azaz
 * @Last Modified time: 2019-02-22 02:25:49
 */

namespace app\backman\model;
use \think\Model;

class Question extends Model{

	protected $pk = 'id';

    public function getAnswerAttr($value){
        $return = unserialize($value);
        return ['data' => $return, 'value' => $return];
    }

    /**
     * 数据完成
    **/
    public function saveData($data = [],$type = 'add'){
        $data['answer'] = serialize($data['answer']);
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