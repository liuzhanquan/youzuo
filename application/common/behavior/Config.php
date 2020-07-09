<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/8/15
 * Time: 17:34
 */

namespace app\common\behavior;
use hg\ServerResponse;
use think\facade\Config as d;
use think\Db;
use hg\Code;

class Config
{
    public function run(){
//        try{
//            $list = Db::name('config')->select();
//            if(!empty($list)){
//                foreach ($list as $key=>$value){
//                    $config_data[$value['name']] = $value['value'];
//                }
//                d::set(['config'=>['site'=>$config_data]]);
//            }
//        }catch (\Exception $exception){
//            return ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
//        }
    }
}