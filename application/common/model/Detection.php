<?php

/**
 * @Author: Azaz；QQ：826355918
 * @Date:   2018-12-11 23:09:08
 * @Last Modified by:   Azaz
 * @Last Modified time: 2019-02-22 02:25:49
 */

namespace app\common\model;
use \think\Model;
use think\Db;

class Detection extends Model{

	protected $pk = 'id';
    protected $name = 'detection';

    /**
 * 产品名称
 * @param $value
 * @return mixed
 */
	public function getGoodsIdAttr($value)
	{
		$info = Goods::where('id',$value)->value('title');
		return ['text' => $info, 'value' => $value];
	}

	/**
	 * 业务员名称
	 * @param $value
	 * @return mixed
	 */
	public function getCustomerIdAttr($value)
	{
		$info = Customer::where('id',$value)->value('customer_name');
		return ['text' => $info, 'value' => $value];
	}
    /**
     * 数据完成
    **/
    public function saveData($data = [],$type = 'add'){

        if( empty($data['start_num']) || empty($data['end_num']) ){
	        $this->error = '二维码起始编号或结束编号为空';
	        return false;
        }else{
            $check = $this->checkNumStatus( $data, $type );
            if( $check['status'] == 400 ){
                $this->error = $check['msg'];
                return false;
            }
        }

        if($type == 'add'){
            $data['timestamp'] = date('Y-m-d H:i:s');
	        $data['update_time'] = date('Y-m-d H:i:s');
            $state = $this->allowField(true)->save($data);
            $id = $this->id;
        }
        if($type == 'edit'){
	        $data['update_time'] = date('Y-m-d H:i:s');
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

    // 查询二维码编号是否重复
	public function checkNumStatus( $data , $type = 'add' ){
		$where   = [];
		$whereOr = [];

		if( $type != 'add' ) {
			$where[]  = ['id','neq', $data['id']];
			$whereOr[]= ['id','neq', $data['id']];
		}
		$where[] = ['start_num','between', [(int)$data['start_num']  ,(int)$data['end_num'] ]];
		$whereOr[] = ['end_num','between',  [(int)$data['start_num']  ,(int)$data['end_num'] ]];

		$find = $this->whereOr([$where,$whereOr])->find();
		if( $find ){
			return ['status' => 400,'msg'=>'二维码编号范围已录入'.$find['start_num'].'-'.$find['end_num']];
		} else {
			return ['status' => 200];
		}

    }

}