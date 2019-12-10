<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/4/18
 * Time: 21:26
 */

namespace app\api\validate;
use think\Validate;

class Address extends Validate
{
    protected $rule = [
        'name'=>'require',
        'phone'=>'require',
        'province'=>'require',
        'province_name'=>'require',
        'city'=>'require',
        'city_name'=>'require',
        'area'=>'require',
        'area_name'=>'require',
        'address'=>'require',
    ];

    protected $field = [
        'name'=>'收货人',
        'phone'=>'收货人手机号码',
        'address'=>'收货地址',
        'province'=>'省份',
        'province_name'=>'省份名称',
        'city'=>'市',
        'city_name'=>'市名称',
        'area'=>'县区',
        'area_name'=>'县区名称',
    ];
}