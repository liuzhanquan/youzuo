<?php
namespace app\api\validate;

use think\Validate;
/**
 * 生成token参数验证器
 */
class User extends Validate
{

    protected $rule = [
        'nickname'       =>  'require',
        'sex'      =>  'require',
    ];

    protected $field = [
        'nickname'=>'昵称',
        'sex'=>'性别',
    ];
}