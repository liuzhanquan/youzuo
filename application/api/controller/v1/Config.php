<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/8/5
 * Time: 16:27
 */

namespace app\api\controller\v1;

use hg\Code;
use hg\ServerResponse;

class Config
{
    /**
     * 获取系统配置
     * @return \think\response\Json
     */
    public function getConfig(){
        //try{
            $app = config('config.site')['weixin'];
            $app = unserialize($app);
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>[
                'name'=>config('config.site')['site_name'],
                'email'=>config('config.site')['site_mail'],
                'tel'=>config('config.site')['site_tel'],
                'qq'=>config('config.site')['site_qq'],
                'app_name'=>$app['app_name'],
                'app_desc'=>$app['app_desc'],
                'app_logo'=>request()->domain().str_replace("\\", '/', $app['app_logo']),
            ]]);
//        }catch (\Exception $exception){
//            ServerResponse::message(Code::CODE_INTERNAL_ERROR);
//        }
    }
}