<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/8/12
 * Time: 17:36
 */

namespace app\api\controller\v1;
use app\api\controller\Api;
use hg\Code;
use hg\ServerResponse;
use think\Db;
use Naixiaoxin\ThinkWechat\Facade;
use think\facade\Env;

class Ad
{
    /**
     * 获取广告列表
     */
    public function index(){
        //try{
            //$officialAccount = Facade::officialAccount('');  // 公众号
            //$work = Facade::work(); // 企业微信
        $config = [
            // 必要配置
            'app_id'             => 'wx4841817a8d012d9a',
            'mch_id'             => '1513216231',
            'key'                => 'ca529d42fa72bc58edb3a00a84265ae1',   // API 密钥

            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            'cert_path'          => 'cert/apiclient_cert.pem', // XXX: 绝对路径！！！！
            'key_path'           => 'cert/apiclient_key.pem',      // XXX: 绝对路径！！！！

            'notify_url'         => '默认的订单回调地址',     // 你也可以在下单时单独设置来想覆盖它
        ];
            $payment = Facade::payment(); // 微信支付
            //$openPlatform = Facade::openPlatform(); // 开放平台
            //$miniProgram = Facade::miniProgram(); // 小程序
        $res = $payment->transfer->toBalance([
            'partner_trade_no' => '1233455', // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
            'openid' => 'oxTWIuGaIt6gTKsQRLau2M0yL16E',
            'check_name' => 'FORCE_CHECK', // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
            're_user_name' => '王小帅', // 如果 check_name 设置为FORCE_CHECK，则必填用户真实姓名
            'amount' => 10000, // 企业付款金额，单位为分
            'desc' => '理赔', // 企业付款操作说明信息。必填
        ]);
        if($res['result_code'] == 'FAIL'){
            return json(['code'=>0,'msg'=>$res['err_code_des']]);
        }
            dump($res);die;
            $parent_id = request()->post('type') ?? 1;
            $Address = Db::name('adver')->where(['parent_id'=>$parent_id])->select();
            if(!empty($Address)){
                foreach ($Address as $k=>$value){
                    $Address[$k]['image'] = request()->domain().str_replace("\\", '/', $value['image']);
                }
            }
            return json(['StatusCode'=>20000,'message'=>'请求成功！','data'=>$Address]);
//        }catch (\Exception $exception){
//            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
//        }
    }
}