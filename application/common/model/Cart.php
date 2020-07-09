<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/8/5
 * Time: 16:38
 */

namespace app\common\model;
use think\Model;

class Cart extends Model
{
    protected $pk = 'id';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    //protected $autoWriteTimestamp = true;


    /**
     * 获取购物车商品信息
     */
    public function profile()
    {
        return $this->hasOne('goods');
    }
}