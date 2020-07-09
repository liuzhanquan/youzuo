<?php

/**
 * @Author: Azaz；QQ：826355918
 * @Date:   2018-12-11 23:09:08
 * @Last Modified by:   Azaz
 * @Last Modified time: 2019-02-22 02:25:49
 */

namespace app\backman\model;
use \think\Model;

class Delivery extends Model{

	protected $pk = 'id';
    protected $name = 'delivery';

    public function rule(){
        return $this->hasMany('DeliveryRule');
    }
    /**
     * 计费方式
     * @param $value
     * @return mixed
     */
    public function getMethodAttr($value)
    {
        $method = [1 => '按件数', 2 => '按重量'];
        return ['text' => $method[$value], 'value' => $value];
    }
    /**
     * 数据完成
    **/
    public function saveData($data = [],$type = 'add'){
        if($type == 'add'){
            $id = $state = $this->allowField(true)->save($data);
        }
        if($type == 'edit'){
            $state = $this->allowField(true)->save($data,['id'=>$data['id']]);
            $id = $data['id'];
        }
        if($state){
            $this->createDeliveryRule($data['rule'],$id);
            return $id;
        }else{
            $this->error = $this->getError() ? $this->getError() : '没有可修改的数据！';
            return false;
        }
        return false;
    }
    /**
     * 添加模板区域及运费
     * @param $data
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    private function createDeliveryRule($data,$delivery_id)
    {
        $save = [];
        $connt = count($data['region']);
        for ($i = 0; $i < $connt; $i++) {
            $save[] = [
                'region' => $data['region'][$i],
                'first' => $data['first'][$i],
                'first_fee' => $data['first_fee'][$i],
                'additional' => $data['additional'][$i],
                'additional_fee' => $data['additional_fee'][$i]
            ];
        }
        $this->rule()->where('delivery_id',$delivery_id)->delete();
        return $this->rule()->saveAll($save);
    }
    /**
     * 运费模板详情
     * @param $delivery_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($delivery_id)
    {
        return self::get($delivery_id, ['rule']);
    }
}