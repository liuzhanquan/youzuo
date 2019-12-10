<?php

/**
 * @Author: Azaz；QQ：826355918
 * @Date:   2018-12-11 23:09:08
 * @Last Modified by:   Azaz
 * @Last Modified time: 2019-02-22 02:25:49
 */

namespace app\backman\model;
use \think\Model;

class DeliveryRule extends Model{

	protected $pk = 'id';
    protected $name = 'delivery_rule';
    protected $append = ['region_content'];
    
    static $regionAll;
    static $regionTree;

    /**
     * 可配送区域
     * @param $value
     * @param $data
     * @return string
     */
    public function getRegionContentAttr($value, $data)
    {
        // 当前区域记录转换为数组
        $regionIds = explode(',', $data['region']);

        if (count($regionIds) === 373) return '全国';

        // 所有地区
        if (empty(self::$regionAll)) {
            self::$regionAll = Region::getCacheAll();
            self::$regionTree = Region::getCacheTree();
        }
        // 将当前可配送区域格式化为树状结构
        $alreadyTree = [];
        foreach ($regionIds as $regionId)
            $alreadyTree[self::$regionAll[$regionId]['pid']][] = $regionId;
        $str = '';
        foreach ($alreadyTree as $provinceId => $citys) {
            $str .= self::$regionTree[$provinceId]['name'];
            if (count($citys) !== count(self::$regionTree[$provinceId]['city'])) {
                $cityStr = '';
                foreach ($citys as $cityId)
                    $cityStr .= self::$regionTree[$provinceId]['city'][$cityId]['name'];
                $str .= ' (<span class="am-link-muted">' . mb_substr($cityStr, 0, -1, 'utf-8') . '</span>)';
            }
            $str .= '、';
        }
        return mb_substr($str, 0, -1, 'utf-8');
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
            return $id;
        }else{
            $this->error = $this->getError() ? $this->getError() : '没有可修改的数据！';
            return false;
        }
        return false;
    }
    
}