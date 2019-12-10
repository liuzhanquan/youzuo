<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/4/24
 * Time: 19:05
 */

namespace app\api\validate;
use think\Validate;

class Distributor extends Validate
{
    protected $rule = [
        'name'=>'require|chsAlphaNum',
        'phone'=>'require|mobile',
        //'business_z'=>'require',
        //'business_f'=>'require',
        'address'=>'require',
    ];

    protected $field = [
        'name'=>'姓名',
        'phone'=>'手机号码',
        //'business_z'=>'店铺信息照片',
        //'business_f'=>'店铺信息照片',
        'address'=>'所在地址',
    ];
}