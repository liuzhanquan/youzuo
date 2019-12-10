<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/4/21
 * Time: 15:48
 */

namespace app\api\controller\v1;
use think\Db;
class Notify
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
                $order = Db::name('order')->where(['order_sn'=>$result['out_trade_no']])->find();
                $status = 4;
                if($order['pay_status'] == 1 && $order['status'] == 0){
                    if($order['type'] == 3){
                        $status = 5;
                    }
                    db('order')->where(['order_sn'=>$result['out_trade_no']])->update(['pay_status'=>2,'pay_time'=>date('Y-m-d H:i:s'),'status'=>$status,'pay_type'=>1]);
                    //增加微信支付记录
                    db('pay_log')->insertGetId(['order_id'=>$order['order_sn'],'money'=>$result['total_fee'] ?? 0/100,'uid'=>$order['uid'],'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
                    //更新商品库存
                    $this->subCommission($order,2);
                    //$this->updateStock($order,$order['agent_id'],2);
                    return true;
                }

            }
        }
    }

    /**
     * 微信支付异步返回
     */
    public function index2(){
        Db::name('log')->insert(['created_at'=>date('Y-m-d H:i:s'),'msg'=>'支付']);
        $xmlData = file_get_contents('php://input'); // 接收微信异步返回信息
        $jsonxml = json_encode(simplexml_load_string($xmlData, 'SimpleXMLElement', LIBXML_NOCDATA));
        //转成数组
        $result = json_decode($jsonxml, true);
        if($result){
            //如果成功返回了
            if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS'){
                //进行改变订单状态等操作。。。。
                //$result['out_trade_no'] = 19050554485448;
                $order = Db::name('z_order')->where(['order_sn'=>$result['out_trade_no']])->find();
                if($order['pay_status'] == 1){
                    db('order')->where(['order_sn'=>$result['out_trade_no']])->update(['pay_status'=>2,'pay_time'=>date('Y-m-d H:i:s')]);
                    //增加微信支付记录
                    db('pay_log')->insertGetId(['order_id'=>$order['order_sn'],'money'=>$result['total_fee'] ?? 0/100,'uid'=>$order['user_id'],'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
                    //更新商品库存
                    return true;
                }

            }
        }
    }

    /**
     * 会员分佣/代理分佣
     * @param $order
     */
    protected function subCommission($order,$type){
        Db::startTrans();
        if($order['order_type'] == 1){
            //普通商品分销
            $orderGoods = Db::name('order_goods')->where(['order_id'=>$order['id']])->field('price,goods_id,number,id,id_distribution')->select();
            $user = Db::name('user')->where(['uid'=>$order['uid']])->field('parent_id,uid')->find();
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

                                $one_user = Db::name('user')->where(['uid'=>$user['parent_id']])->field('parent_id,uid,openid,is_distribution')->find();
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
                                    if($one_user['is_distribution'] == 2){
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



                                if($one_user && $one_user['parent_id'] > 0 && $two_money > 0){
                                    //判断上级是否是代理，是代理的话改变分佣类型为代理
                                    $two_user = Db::name('user')->where(['uid'=>$one_user['parent_id']])->field('parent_id,uid,openid,is_distribution')->find();
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
                                        Db::name('agent')->where('id',$two_agent['id'])->setInc('money',$two_money);
                                    }else{
                                        //二级分销
                                        if($two_user['is_distribution'] == 2){
                                            $data[] = [
                                                'user_id'=>$two_user['parent_id'],
                                                'tuser_id'=>$user['uid'],
                                                'type'=>1,
                                                'created_at'=>date('Y-m-d H:i:s'),
                                                'updated_at'=>date('Y-m-d H:i:s'),
                                                'money'=>$two_money,
                                                'order_id'=>$order['id'],
                                            ];
                                            Db::name('user')->where(['uid'=>$two_user['parent_id']])->setInc('money',$two_money);
                                        }
                                    }
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
        }else{
            //套餐商品分销
            $user = Db::name('user')->where(['uid'=>$order['uid']])->field('parent_id,uid')->find();
                $where[] = ['id','eq',1];
                $goods = Db::name('meal')->where($where)->field('fen_time,fen_type,data,is_fen')->lock(true)->find();
                if($goods['id_distribution'] == 1){
                    //未分销的参与分销
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

                            $one_user = Db::name('user')->where(['uid'=>$user['parent_id']])->field('parent_id,uid,openid,is_distribution')->find();
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
                                if($one_user['is_distribution'] == 2){
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



                            if($one_user && $one_user['parent_id'] > 0 && $two_money > 0){
                                //判断上级是否是代理，是代理的话改变分佣类型为代理
                                $two_user = Db::name('user')->where(['uid'=>$one_user['parent_id']])->field('parent_id,uid,openid,is_distribution')->find();
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
                                    Db::name('agent')->where('id',$two_agent['id'])->setInc('money',$two_money);
                                }else{
                                    //二级分销
                                    if($two_user['is_distribution'] == 2){
                                        $data[] = [
                                            'user_id'=>$two_user['parent_id'],
                                            'tuser_id'=>$user['uid'],
                                            'type'=>1,
                                            'created_at'=>date('Y-m-d H:i:s'),
                                            'updated_at'=>date('Y-m-d H:i:s'),
                                            'money'=>$two_money,
                                            'order_id'=>$order['id'],
                                        ];
                                        Db::name('user')->where(['uid'=>$two_user['parent_id']])->setInc('money',$two_money);
                                    }
                                }
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
                    $rr = Db::name('order')->where(['id'=>$order['id']])->update(['id_distribution'=>2]);
                    if(!$rr){
                        Db::rollback();
                    }
                }
        }

        Db::commit();
    }

    /**
     * 更新商品库存
     */
    protected function updateStock($order,$agent_id = 0,$type){
        $orderGoods = Db::name('order_goods')->where(['order_id'=>$order['id']])->field('price,goods_id,number,id,id_distribution')->select();
        $user = Db::name('user')->where(['uid'=>$order['uid']])->field('parent_id,uid')->find();
        Db::startTrans();
        if($order['order_type'] == 1){
            if(!empty($orderGoods)){
                foreach ($orderGoods as $key=>$value){
                    if($value['stock_type'] == $type){
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
        }else{
            //套餐更新库存
            if(!$agent_id){
                Db::name('agent_stock')->where([['type','eq',2],['user_id','eq',$order['uid']],['agent_id','eq',$agent_id],['goods_id','eq',1]])->setDec('num',1);
                Db::name('agent_stock')->where([['type','eq',2],['user_id','eq',$order['uid']],['agent_id','eq',$agent_id],['goods_id','eq',1]])->setInc('sales',1);
            }
        }

    }
}