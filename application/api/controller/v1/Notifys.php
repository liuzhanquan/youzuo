<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/4/21
 * Time: 15:48
 */

namespace app\api\controller\v1;
use think\Db;
class Notifys
{
    /**
     * 微信支付异步返回
     */
    public function index(){
        $xmlData = file_get_contents('php://input'); // 接收微信异步返回信息
        $jsonxml = json_encode(simplexml_load_string($xmlData, 'SimpleXMLElement', LIBXML_NOCDATA));
        //转成数组
        $result = json_decode($jsonxml, true);
        if($result){
            //如果成功返回了
            if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS'){
                //进行改变订单状态等操作。。。。
                //$result['out_trade_no'] = 19050554485448;
                $order = Db::name('recharge_order')->where(['order_sn'=>$result['out_trade_no']])->find();
                if($order['pay_status'] == 1){
                    Db::name('recharge_order')->where(['order_sn'=>$result['out_trade_no']])->update(['pay_status'=>2,'pay_time'=>date('Y-m-d H:i:s')]);
                    Db::name('agent')->where('id',$order['agent_id'])->setInc('money',$order['money']);
                    //增加微信支付记录
                    Db::name('pay_log')->insertGetId(['order_id'=>$order['order_sn'],'money'=>$result['total_fee'] ?? 0/100,'uid'=>$order['user_id'],'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
                    $data = [
                        'agent_id'=>$order['agent_id'],
                        'order_id'=>$order['id'],
                        'type'=>1,
                        'created_at'=>date('Y-m-d H:i:s'),
                        'updated_at'=>date('Y-m-d H:i:s'),
                        'money'=>$order['money'],
                    ];
                    Db::name('agent_money_log')->insertGetId($data);
                    return true;
                }

            }
        }
    }
}