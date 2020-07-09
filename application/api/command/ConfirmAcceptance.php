<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/10/29
 * Time: 10:48
 */

namespace app\api\command;
use app\backman\model\Agent;
use app\common\model\Goods;
use think\Db;
use think\facade\Log;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class ConfirmAcceptance extends Command
{
    protected function configure()
    {
        $this->setName('confirmAcceptance')->setDescription("7天自动确认收货");
    }

    protected function execute(Input $input, Output $output)
    {
        //7天自动确认收货
        $start_date = date('Y-m-d 00:00:00', strtotime('-7 days'));
        $end_date = date('Y-m-d 00:00:00', time());
        $data = Db::name('order')->where('pay_status', 2)->where('status', 3)->whereBetweenTime('created_at', $start_date, $end_date)->select();
        //执行计算
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (!empty($value) && $value['status'] == 3 && strtotime($value['express_time']) < time() - (98600*7)) {
                    $res = Db::name('order')->where(['id' => $value['id']])->update(['status' => 2, 'confirmd_at' => date('Y-m-d H:i:s')]);
                    if (!$res) {
                        return false;
                    }
                    //增加分销提成
                    //if($order['type'] == 0){
                    //判断用户是否具有分销资格
                    $subCommission = $this->subCommission($value, 1);
                    //}
                    //增加销量
                    $this->updateStock($value, $value['agent_id'], 1);
                    //结算给代理用户
                    $this->agentMoney($value, $value['agent_id'], $subCommission);
                    //判断用户消费类型是套盒，并且满398获得分销资格
                    $userInfo = Db::name('user')->where('uid', $value['uid'])->field('is_distribution')->find();
                    if ($value['money'] >= 398 && $value['order_type'] == 2 && $userInfo['is_distribution'] == 1) {
                        //更新分销资格
                        Db::name('user')->where('uid', $value['uid'])->update(['is_distribution' => 2]);
                    }
                    Db::name('order')->where(['id' => $value['id']])->update(['is_distribution' => '2']);
                }
            }
            //\think\Queue::push('app\api\job\Agent@fire',$data);
            Db::name('log')->insert(['created_at' => date('Y-m-d H:i:s'), 'msg' => '自动确认收货']);
            echo 'ok';
        }
    }

    /**
     * 结算代理用户资金
     */
    protected function agentMoney($order,$agent_id,$subCommission){
        //代理存在并且已经分销完成才进行结算
        if($agent_id){
            //结算代理资金
            $orderGoods = Db::name('order_goods')->where('order_id',$order['id'])->select();
            $money = 0;
            $agent = Agent::where('id',$agent_id)->find();
            if($order['order_type'] == 1){
                if(!empty($orderGoods) && !empty($agent)){
                    foreach ($orderGoods as $key=>$value){
                        $goods = Goods::where('id',$value['goods_id'])->field('level_data')->find();
                        $agent_price = unserialize($goods['level_data']);
                        //减掉运费
                        $money  += ($value['price'] * $value['number']);// - $agent_price[$agent['level_id']];
                    }
                }
                $money = $money - $subCommission['one_money'] - $subCommission['two_money'] - $order['freight_money'];
            }else{
                $money = $order['pay_money'];
                $money = $money - $subCommission['one_money'] - $subCommission['two_money'] - $order['freight_money'];
            }


            if($money > 0){
                //结算给代理用户
                Db::name('agent')->where('id',$agent_id)->setInc('money',$money);
                //增加结算记录
                $data = [
                    'agent_id'=>$agent_id,
                    'order_id'=>$order['id'],
                    'type'=>1,
                    'created_at'=>date('Y-m-d H:i:s'),
                    'updated_at'=>date('Y-m-d H:i:s'),
                    'money'=>$money,
                ];
                Db::name('agent_money_log')->insertGetId($data);
                $openid = Agent::where('id',$agent_id)->field('openid')->find();
                $u = Db::name('user')->where('openid',$openid['openid'])->find();
                $data = [
                    'agent_id'=>$agent_id,
                    'order_id'=>$order['id'],
                    'type'=>2,
                    'created_at'=>date('Y-m-d H:i:s'),
                    'updated_at'=>date('Y-m-d H:i:s'),
                    'money'=>$money,
                    'user_id'=>$u['uid'] ?? 0,
                    'tuser_id'=>$order['uid'],
                ];
                Db::name('user_commission')->insertGetId($data);
            }
        }
    }

    /**
     * 会员分佣/代理分佣
     * @param $order
     */
    protected function subCommission($order,$type){
        if($order['order_type'] == 1){

            $one_money = 0;
            $two_money = 0;
            $orderGoods = Db::name('order_goods')->where(['order_id'=>$order['id']])->field('price,goods_id,number,id,id_distribution')->select();
            $user = Db::name('user')->where(['uid'=>$order['uid']])->field('parent_id,uid')->find();
            Db::startTrans();
            $oneMoney = 0;
            $twoMoney = 0;
            if(!empty($orderGoods)){

                foreach ($orderGoods as $key=>$value){
                    if($value['id_distribution'] == 1){
                        //未分销的参与分销
                        $goods = Db::name('goods')->where(['id'=>$value['goods_id']])->field('fen_time,fen_type,fen_data,is_fen')->lock(true)->find();
                        if($goods['is_fen'] == 1 && $goods['fen_time'] == $type){
                            //分销开启和分销结算时间
                            $info = unserialize($goods['fen_data']);
                            if($goods['fen_type'] == 1){
                                //金额分销方式，计算金额
                                $one_money = $info[1] * $value['number'];
                                $two_money = $info[2] * $value['number'];
                            }else{
                                //百分比方式，计算金额
                                $one_money = $info[1]/100 * ($value['price'] * $value['number']);
                                $two_money = $info[2]/100 * ($value['price'] * $value['number']);
                            }
                            //分销第一级
                            $data = [];

                            if($user['parent_id'] > 0 && $one_money > 0){
                                //判断上级是否是代理，是代理的话改变分佣类型为代理
                                $one_user = Db::name('user')->where(['uid'=>$user['parent_id']])->field('parent_id,uid,openid,is_distribution')->find();
                                $one_agent = Db::name('agent')->where('openid',$one_user['openid'])->find();
                                if($one_user['is_distribution'] == 2){
                                    if($one_agent){
                                        $data[] = [
                                            'agent_id'=>$one_agent['id'],
                                            'order_id'=>$order['id'],
                                            'type'=>2,
                                            'created_at'=>date('Y-m-d H:i:s'),
                                            'updated_at'=>date('Y-m-d H:i:s'),
                                            'money'=>$one_money,
                                            'user_id'=>0,
                                            'tuser_id'=>$user['uid'],
                                        ];
                                        //Db::name('user_commission')->insertGetId($data);
                                        Db::name('agent')->where('id',$one_agent['id'])->setInc('money',$one_money);
                                    }else{
                                        $data[] = [
                                            'user_id'=>$user['parent_id'],
                                            'tuser_id'=>$user['uid'],
                                            'type'=>1,
                                            'created_at'=>date('Y-m-d H:i:s'),
                                            'updated_at'=>date('Y-m-d H:i:s'),
                                            'money'=>$one_money,
                                            'order_id'=>$order['id'],
                                        ];
                                        //给用户增加余额
                                        Db::name('user')->where(['uid'=>$user['parent_id']])->setInc('money',$one_money);
                                    }
                                }


                                $oneMoney = $one_money;
                                if($one_user && $one_user['parent_id'] > 0 && $two_money > 0){
                                    //判断上级是否是代理，是代理的话改变分佣类型为代理
                                    $two_user = Db::name('user')->where(['uid'=>$one_user['parent_id']])->field('parent_id,uid,openid,is_distribution')->find();
                                    $two_agent = Db::name('agent')->where('openid',$two_user['openid'])->find();
                                    if($two_user['is_distribution'] == 2){
                                        if($two_agent){
                                            $data[] = [
                                                'agent_id'=>$two_agent['id'],
                                                'order_id'=>$order['id'],
                                                'type'=>2,
                                                'created_at'=>date('Y-m-d H:i:s'),
                                                'updated_at'=>date('Y-m-d H:i:s'),
                                                'money'=>$two_money,
                                                'user_id'=>0,
                                                'tuser_id'=>$user['uid'],
                                            ];
                                            //Db::name('user_commission')->insertGetId($data);
                                            $r =Db::name('agent')->where('id',$two_agent['id'])->setInc('money',$two_money);
                                        }else{
                                            //二级分销
                                            $data[] = [
                                                'user_id'=>$two_user['parent_id'],
                                                'tuser_id'=>$user['uid'],
                                                'type'=>1,
                                                'created_at'=>date('Y-m-d H:i:s'),
                                                'updated_at'=>date('Y-m-d H:i:s'),
                                                'money'=>$two_money,
                                                'order_id'=>$order['id'],
                                            ];
                                            $r = Db::name('user')->where(['uid'=>$two_user['parent_id']])->setInc('money',$two_money);
                                        }
                                        if(!$r){
                                            Db::rollback();
                                        }
                                    }
                                    $twoMoney = $two_money;
                                }

                            }
                            if(!empty($data)){
                                $res = Db::name('user_commission')->insertAll($data);
                                if(!$res){
                                    Db::rollback();
                                }
                            }

                        }
                        //更新为已分销
                        $rr = Db::name('order_goods')->where(['id'=>$value['id']])->update(['id_distribution'=>2]);
                        if(!$rr){
                            Db::rollback();
                        }
                    }
                }
            }
            Db::commit();
            return ['one_money'=>$oneMoney,'two_money'=>$twoMoney];
        }else{
            //套餐商品分销
            $one_money = 0;
            $two_money = 0;
            $user = Db::name('user')->where(['uid'=>$order['uid']])->field('parent_id,uid')->find();
            Db::startTrans();
            $oneMoney = 0;
            $twoMoney = 0;
            if($order['is_distribution'] == 1){
                //未分销的参与分销
                $goods = Db::name('meal')->where('id',1)->field('fen_time,fen_type,data,fen_data,is_fen')->lock(true)->find();
                if($goods['is_fen'] == 1 && $goods['fen_time'] == $type){
                    //分销开启和分销结算时间
                    $info = unserialize($goods['fen_data']);
                    if($goods['fen_type'] == 1){
                        //金额分销方式，计算金额
                        $one_money = $info[1] * 1;
                        $two_money = $info[2] * 1;
                    }else{
                        //百分比方式，计算金额
                        $one_money = $info[1]/100 * ($goods['price'] * 1);
                        $two_money = $info[2]/100 * ($goods['price'] * 1);
                    }
                    //分销第一级
                    $data = [];


                    if($user['parent_id'] > 0 && $one_money > 0){
                        //判断上级是否是代理，是代理的话改变分佣类型为代理
                        $one_user = Db::name('user')->where(['uid'=>$user['parent_id']])->field('parent_id,uid,openid')->find();
                        $one_agent = Db::name('agent')->where('openid',$one_user['openid'])->find();
                        if($one_agent){
                            $data[] = [
                                'agent_id'=>$one_agent['id'],
                                'order_id'=>$order['id'],
                                'type'=>2,
                                'created_at'=>date('Y-m-d H:i:s'),
                                'updated_at'=>date('Y-m-d H:i:s'),
                                'money'=>$one_money,
                                'user_id'=>0,
                                'tuser_id'=>$user['uid'],
                            ];
                            //Db::name('user_commission')->insertGetId($data);
                            Db::name('agent')->where('id',$one_agent['id'])->setInc('money',$one_money);
                        }else{
                            $data[] = [
                                'user_id'=>$user['parent_id'],
                                'tuser_id'=>$user['uid'],
                                'type'=>1,
                                'created_at'=>date('Y-m-d H:i:s'),
                                'updated_at'=>date('Y-m-d H:i:s'),
                                'money'=>$one_money,
                                'order_id'=>$order['id'],
                            ];
                            //给用户增加余额
                            Db::name('user')->where(['uid'=>$user['parent_id']])->setInc('money',$one_money);
                        }

                        $oneMoney = $one_money;
                        if($one_user && $one_user['parent_id'] > 0 && $two_money > 0){
                            //判断上级是否是代理，是代理的话改变分佣类型为代理
                            $two_user = Db::name('user')->where(['uid'=>$one_user['parent_id']])->field('parent_id,uid,openid')->find();
                            $two_agent = Db::name('agent')->where('openid',$two_user['openid'])->find();
                            if($two_agent){
                                $data[] = [
                                    'agent_id'=>$two_agent['id'],
                                    'order_id'=>$order['id'],
                                    'type'=>2,
                                    'created_at'=>date('Y-m-d H:i:s'),
                                    'updated_at'=>date('Y-m-d H:i:s'),
                                    'money'=>$two_money,
                                    'user_id'=>0,
                                    'tuser_id'=>$user['uid'],
                                ];
                                //Db::name('user_commission')->insertGetId($data);
                                $r =Db::name('agent')->where('id',$two_agent['id'])->setInc('money',$two_money);
                            }else{
                                //二级分销
                                $data[] = [
                                    'user_id'=>$two_user['parent_id'],
                                    'tuser_id'=>$user['uid'],
                                    'type'=>1,
                                    'created_at'=>date('Y-m-d H:i:s'),
                                    'updated_at'=>date('Y-m-d H:i:s'),
                                    'money'=>$two_money,
                                    'order_id'=>$order['id'],
                                ];
                                $r = Db::name('user')->where(['uid'=>$two_user['parent_id']])->setInc('money',$two_money);
                            }

                            if(!$r){
                                Db::rollback();
                            }
                            $twoMoney = $two_money;
                        }

                    }

                    if(!empty($data)){
                        $res = Db::name('user_commission')->insertAll($data);
                        if(!$res){
                            Db::rollback();
                        }
                    }

                }
//                        //更新为已分销
//                        $rr = Db::name('order')->where(['id'=>$order['id']])->update(['id_distribution'=>2]);
//                        if(!$rr){
//                            Db::rollback();
//                        }
            }
            Db::commit();
            return ['one_money'=>$oneMoney,'two_money'=>$twoMoney];
        }
    }

    /**
     * 更新商品库存
     */
    protected function updateStock($order,$agent_id = 0,$type){
        $orderGoods = Db::name('order_goods')->where(['order_id'=>$order['id']])->field('price,goods_id,number,id,id_distribution')->select();
        $user = Db::name('user')->where(['uid'=>$order['uid']])->field('parent_id,uid')->find();
        Db::startTrans();
        if(!empty($orderGoods)){
            foreach ($orderGoods as $key=>$value){
                $goodsInfo = Goods::where('id',$value['goods_id'])->field('stock_type')->find();
                if($goodsInfo['stock_type'] == $type){
                    if(!$agent_id){
                        //代理库存减
//                        Db::name('agent_stock')->where(['goods_id'=>$value['goods_id'],'agent_id'=>$agent_id])->setInc('num',$value['number']);
//                        Db::name('agent_stock')->where(['goods_id'=>$value['goods_id'],'agent_id'=>$agent_id])->setDec('sales',$value['number']);
//                    }else{
                        //减系统库存
                        Db::name('goods')->where(['id'=>$value['goods_id']])->setDec('stock',$value['number']);
                        Db::name('goods')->where(['id'=>$value['goods_id']])->setInc('sales',$value['number']);
                    }
                }

                //增加销量
                Db::commit();
            }
        }
    }
}