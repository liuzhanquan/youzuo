<?php

/**
 * @Date:   2018-12-11 23:09:08
 * @Last Modified time: 2019-02-22 02:25:49
 */

namespace app\common\model;
use \think\Model;

class StaffPower extends Model{

	protected $pk = 'id';

	public function pSelect(){
		$where['is_show'] = 1;
		$res = $this->where($where)->order('sort','asc')->select();
		$res = $this->powertree($res);
		return $res;
	}

	public function powertree($arr,$level = 0, $num = 2){
		$data = [];
		$res  = [];
		foreach( $arr as $key=>$item ){
			if( $item['parent_id'] == $level ){
				$data['id'] = $item['id'];
				$data['name'] = $item['name'];
				$data['parent_id'] = $item['parent_id'];
				if( $num > 1 ){
					$data['son'] = $this->powertree($arr,$item['id'],$num - 1);
				}
				$res[] = $data;
			}
		}
		return $res;
	}

	/**
	 * 员工权限修改保存
	 * @return [type] [description]
	 */
	public function saveData($data = [],$type = 'add'){
		$parent = $this->where('id',$data['parent_id'])->find();
		if($type == 'add'){
            $id = $state = $this->allowField(true)->save($data);
        }
        if($type == 'edit'){
            $slef = $this->where('id',$data['id'])->find();
            if($data['parent_id'] == $data['id']){
                $this->error = "上级分类设定错误";
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