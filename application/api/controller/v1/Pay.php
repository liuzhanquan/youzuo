<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/4/21
 * Time: 15:34
 */

namespace app\api\controller\v1;
use app\api\controller\Api;
use hg\CodeMsg;
use hg\ServerResponse;
use hg\Code;
use think\Exception;
use think\facade\Env;
use think\Db;
use think\facade\Request;
class Pay extends Api
{
    /**
     * 微信支付
     */
    public function index(){
        try{
            if($this->data['order_sn']){
                $order = db('order')->where(['id'=>$this->data['order_sn']])->find();
                $openid = db('user')->where(['uid'=>$this->uid])->value('openid');
                $pay = unserialize(config('config.site')['weixin']);
                $orderMoney = $order['pay_money'];
                $order_sn = $order['order_sn'];
                if($order['status'] == '0' && $order['pay_status'] == 1){
                    $request = Request::instance();
                    $jsApi = [
                        'appid'=>$pay['appid'],
                        'appsecret'=>$pay['secret'],
                        'mchid'=>$pay['mch_id'],
                        'key'=>$pay['apikey'],
                        'apiclient_cert'=> Env::get("root_path").$pay['cert_file'],
                        'apiclient_key'=> Env::get("root_path").$pay['key_file'],
                        'openid'=>$openid,
                        'body'=>'订单支付',
                        'out_trade_no'=>$order_sn,
                        'total_fee'=>$orderMoney * 100,
                        'notify_url'=>request()->domain().'/index.php/v1/notify',
                        'spbill_create_ip'=>$request->ip(),
                    ];
                    $paySdk = new \wechat\Jspay($jsApi);
                    $return = $paySdk->getParameters();
                    if($return){
                        return ServerResponse::message(Code::CODE_SUCCESS, '', $return);
                    }else{
                        ServerResponse::message(Code::CODE_INTERNAL_ERROR,'订单状态错误');
                    }

                }else{
                    ServerResponse::message(Code::CODE_INTERNAL_ERROR,'订单状态错误');
                }

            }else{
                ServerResponse::message(Code::CODE_INTERNAL_ERROR,'订单状态错误');
            }
        }catch (Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
        }
    }

    /**
     * 微信支付
     */
    public function zOrderPay(){
        try{
            if($this->data['order_sn']){

                $order = db('z_order')->where(['id'=>$this->data['order_sn']])->find();
                if(!$order['logistics_money']){
                    //将状态返回
                    Db::name('z_order')->where('id',$this->data['order_sn'])->update(['pay_time'=>date('Y-m-d H:i:s'),'pay_status'=>2]);
                    return ServerResponse::message(Code::CODE_SUCCESS, '', ['money'=>$order['logistics_money']]);
                }
                $openid = db('user')->where(['uid'=>$this->uid])->value('openid');
                $pay = unserialize(config('config.site')['weixin']);
                $orderMoney = $order['logistics_money'];
                $order_sn = get_order_sn();
                Db::name('z_order')->where('id',$this->data['order_sn'])->update(['order_sn'=>$order_sn]);
                if($order['pay_status'] == 1 && $order['status'] == 1){
                    $request = Request::instance();
                    $jsApi = [
                        'appid'=>$pay['appid'],
                        'appsecret'=>$pay['secret'],
                        'mchid'=>$pay['mch_id'],
                        'key'=>$pay['apikey'],
                        'apiclient_cert'=> Env::get("root_path").$pay['cert_file'],
                        'apiclient_key'=> Env::get("root_path").$pay['key_file'],
                        'openid'=>$openid,
                        'body'=>'订单支付',
                        'out_trade_no'=>$order_sn,
                        'total_fee'=>$orderMoney * 100,
                        'notify_url'=>request()->domain().'/index.php/v1/zt',
                        'spbill_create_ip'=>$request->ip(),
                    ];
                    $paySdk = new \wechat\Jspay($jsApi);
                    $return = $paySdk->getParameters();
                    if($return){
                        return ServerResponse::message(Code::CODE_SUCCESS, '', $return);
                    }else{
                        ServerResponse::message(Code::CODE_INTERNAL_ERROR,'订单状态错误1');
                    }

                }else{
                    ServerResponse::message(Code::CODE_INTERNAL_ERROR,'订单状态错误2');
                }

            }else{
                ServerResponse::message(Code::CODE_INTERNAL_ERROR,'订单状态错误3');
            }
        }catch (Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
        }
    }

    /**
     * 余额支付
     */
    public function balance(){
        try{
            if($this->data['order_sn']){
                $order = db('order')->where(['order_sn'=>$this->data['order_sn']])->find();
                $money = $order['pay_money'];

                $user = db('user')->where(['uid'=>$this->uid])->find();
                if($user['money'] < $money){
                    ServerResponse::message(Code::CODE_INTERNAL_ERROR,'账户余额不足！');
                }
                //支付成功
                db('user')->where(['uid'=>$this->uid])->setDec('money',$money);
                //增加支付记录
                $res = db('pay_log')->insertGetId(['order_id'=>$order['order_sn'],'money'=>$order['pay_money'],'uid'=>$order['uid'],'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s'),'type'=>2]);
                if($res){
                    //跟新订单状态
                    $r = db('order')->where(['order_sn'=>$this->data['order_sn']])->update([
                        'pay_time'=>date('Y-m-d H:i:s'),
                        'status'=>4,
                        'pay_status'=>2,
                        'pay_type'=>0,
                    ]);
                    return ServerResponse::message(Code::CODE_SUCCESS);
                }else{
                    ServerResponse::message(Code::CODE_INTERNAL_ERROR);
                }

            }else{
                ServerResponse::message(Code::CODE_INTERNAL_ERROR);
            }
        }catch (Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
        }
    }

    /**
     * 更新库存
     */
    protected function updateStocks($order){
        if(!empty($order)){
                $orderGoods = Db::name('order_goods')->where(['order_id'=>$order['id']])->select();
                if(!empty($orderGoods)){
                    foreach ($orderGoods as $k=>$v){
                        if($v['number'] > 0){
                            $goods = Db::name('goods')->where(['id'=>$v['goods_id']])->find();
                            //根据商品的减库存规则减少对应的库存
                            if($goods['stock_type'] == 2){
                                //减少库存
                                Db::name('goods')->where(['id'=>$goods['id']])->setInc('stock',$v['number']);
                            }
                        }
                    }

                }

        }
    }

    /**
     * 随机创建订单号方法
     * @return string
     */
    protected function build_order_no(){
        return date('ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }


}