<?php
namespace app\api\validate;

use think\Validate;
/**
 * 生成token参数验证器
 */
class Token extends Validate
{

    protected $rule = [
        'code'       =>  'require',
        'iv'      =>  'require',
        'encryptedData'       =>  'require',
    ];

    protected $message  =   [
        'code.require'    => 'code不能为空',
        'iv.require'    => 'iv不能为空',
        'encryptedData.require'     => 'encryptedData不能为空',
    ];
}