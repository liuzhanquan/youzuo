<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/10/23
 * Time: 18:37
 */

namespace app\common\model;
use think\Model;

class Logistics extends Model
{
    protected $table = 'bear_logistics';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
}