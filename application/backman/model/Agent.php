<?php

/**
 * @Author: Azaz；QQ：826355918
 * @Date:   2018-12-11 23:09:08
 * @Last Modified by:   Azaz
 * @Last Modified time: 2019-02-22 02:25:49
 */

namespace app\backman\model;
use \think\Model;

class Agent extends Model{

	protected $pk = 'id';

    public function level(){
        return $this->hasOne('AgentLevel','id','level_id');
    }
    
    public function detail($where){
        $filter = [];
        if (is_array($where)) {
            $filter = array_merge($filter, $where);
        } else {
            $filter['id'] = (int)$where;
        }
        return self::get($filter, ['level']);
    }
    public function getlist($where = array(),$limit = 15,$order = 'id desc',$field = "*"){
        return $this->with(['level'])
            ->where($where)
            ->field($field)
            ->order($order)
            ->paginate($limit, false, [
                'query' => request()->param()
            ]);
    }

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