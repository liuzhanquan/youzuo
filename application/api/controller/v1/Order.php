<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/8/5
 * Time: 18:16
 */

namespace app\api\controller\v1;
use app\api\controller\Api;
use app\backman\model\Agent;
use app\backman\model\Meal;
use app\common\model\AgentStock;
use app\common\model\Cart;
use app\common\model\User;
use hg\Code;
use hg\ServerResponse;
use think\Db;
use think\Request;
use app\common\model\Order as OrderModel;
use app\common\model\OrderGoods as OrderGoods;
use app\common\model\Goods;
use dh2y\query\express\QueryExpress;
use Xu42\KuaiDi100\KuaiDi100;
use GuzzleHttp;

class Order extends Api
{
    /**
     * 获取用户订单列表
     * @param OrderModel $orderModel
     * @param Goods $goods
     * @param OrderGoods $orderGoods
     * @return \think\response\Json
     */
    public function index(OrderModel $orderModel,Goods $goods, OrderGoods $orderGoods){
        try{
            $where['uid'] = $this->uid;
            $where['is_delete'] = 0;
            $status = $this->data['status'];
            if($status != 99){
                $where['status'] = $this->data['status'];
            }
            $list = $orderModel->where($where)->field('status,order_sn,id,pay_money,num,order_type')->order('id','desc')->paginate(10,false,['query'=>request()->param()])->toArray();
            $data = $list['data'];
            if(!empty($data)){
                foreach ($data as $key=>$value){
                    //$data[$key]['goods'] = $goods->where(['id'=>$value['goods_id']])->find()->toArray();
                    $order_goods = $orderGoods->where(['order_id'=>$value['id']])->field('number,image,title,price,total_money')->select();
                    if(!empty($order_goods)){
                        foreach ($order_goods as $k=>$v){
                            $order_goods[$k]['image'] = request()->domain().str_replace("\\", '/', $v['image']);
                            $order_goods[$k]['order_type'] = $value['order_type'];
                        }
                    }
                    $data[$key]['goods'] = $order_goods;
                }
            }
            //if(empty($data)) ServerResponse::message(Code::CODE_NO_CONTENT);
            $list['data'] = $data;
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$list]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
        }
    }

    /**
     * 获取订单详情
     * @param OrderModel $orderModel
     * @return \think\response\Json
     */
    public function details(OrderModel $orderModel,Goods $goods, OrderGoods $orderGoods){
        try{
            $order_id = $this->data['order_id'];
            $list = $orderModel->where(['id'=>$order_id])->field('status,order_sn,id,pay_money,num,address,created_at,phone,city,province,area,freight_money,name,confirmd_at,pay_time,express,express_name,order_type')->find();
            $data = $list;
            if(!empty($data)){
                    $order_goods = $orderGoods->where(['order_id'=>$data['id']])->field('number,image,title,price,total_money,id,goods_id,is_comment')->select();
                    if(!empty($order_goods)){
                        foreach ($order_goods as $k=>$v){
                            $order_goods[$k]['image'] = request()->domain().str_replace("\\", '/', $v['image']);
                            if($data['status'] != 2){
                                $order_goods[$k]['is_comment'] = 3;
                            }

                        }
                    }
                $data['goods'] = $order_goods;
            }
            //if(empty($data)) ServerResponse::message(Code::CODE_NO_CONTENT);
            $list = $data;
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$list]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
        }
    }

    /**
     * 查询物流信息
     */
    public function logistics(){
        try{
            // 获取快递公司类型
            $order_id = $this->data['order_id'];
            $order = \app\common\model\Order::where('id',$order_id)->field('express,express_name,express_id')->find();

//            $Query = QueryExpress::getInstance();
//
//            $express = $Query->getType($order['express']);
//            dump($express);die;

            $ex_company = Db::name('ex_company')->where('id',$order['express_id'])->find();
           $logistics = KuaiDi100::getCode($order['express']);
            //$client = new GuzzleHttp\Client();
//            $url = "https://www.kuaidi100.com/query?type={$logistics}&postid={$order['express']}";
//            $res = $client->request('GET', $url, []);
//            dump($order['express']);die;
            // 查询结果
            //dump($res->getBody()->getContents());die;
            $data      = KuaiDi100::track($logistics,$order['express']);
            if($data['message'] == 'ok'){
                return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>['order'=>$order,'logistics'=>$data]]);
            }else{
                return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>['order'=>$order,'logistics'=>$data ?? []]]);
            }

        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
        }
    }

    /**
     * 查询物流信息
     */
    public function logistics2(){
        try{
//            $Query = QueryExpress::getInstance();
//
//            $express = $Query->getType($order['express']);
//            dump($express);die;
            $sn = $this->data['express'];
            $order_id = $this->data['id'];
            $order = Db::name('z_order')->where('id',$order_id)->find();
            $ex_company = Db::name('ex_company')->where('id',$order['logistics_id'])->find();

            $logistics = KuaiDi100::getCode($sn);
            //$client = new GuzzleHttp\Client();
//            $url = "https://www.kuaidi100.com/query?type={$logistics}&postid={$order['express']}";
//            $res = $client->request('GET', $url, []);
//            dump($order['express']);die;
            // 查询结果
            //dump($res->getBody()->getContents());die;
            $data      = KuaiDi100::track($logistics,$sn);
            if($data['message'] == 'ok'){
                return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>['order'=>$order,'logistics'=>$data]]);
            }else{
                return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>['order'=>$order,'logistics'=>$data ?? []]]);
            }

        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
        }
    }

    /**
     * 校验商品库存
     */
    public function check(Goods $goods){
        try{
            $type = $this->data['type'] ?? 1;
            $agent_id = $this->data['agent_ids'] ?? 0;
            $user = User::where('uid',$this->uid)->field('agent_id')->find();
            $agent_id = $user['agent_id'] ?? $agent_id;
            if($type == 1){
                //立即购买校验商品库存
                $goods_id = $this->data['goods_id'];
                $goodsInfo = $goods->where(['id'=>$goods_id])->find();
                $num = $this->data['num'];
                if($goodsInfo['status'] == 2){
                    //ServerResponse::message(Code::CODE_INTERNAL_ERROR,'商品已下架');
                    return json(['StatusCode'=>50000,'message'=>'商品已下架']);
                }
//                if($agent_id){
//                    //获取代理库存
//                    $AgentStock = AgentStock::where('agent_id',$agent_id)->where('goods_id',$goods_id)->find();
//                    $goodsInfo['stock'] = $AgentStock['num'] ?? 0;
//                }
//                if($num > $goodsInfo['stock']){
//                    return json(['StatusCode'=>50000,'message'=>'库存不足']);
//                    //ServerResponse::message(Code::CODE_INTERNAL_ERROR,'库存不足');
//                }
                return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>['is_status'=>1,'is_stock'=>1,'is_statusArr'=>[],'is_stockArr'=>[]]]);
            }else{
                //购物车购买校验商品库存
                $cart_id = $this->data['cart_id'];
                $arr = explode(',',$cart_id);

                $is_status = 1;
                $is_statusArr = [];
                $is_stock = 1;
                $is_stockArr = [];
                if(!empty($arr)){
                    foreach ($arr as $key=>$item){
                        $goodsCart = \app\common\model\Cart::where(['id'=>$item])->find();
                        $goodsInfo = \app\common\model\Goods::where(['id'=>$goodsCart['goods_id']])->find();
                        if($goodsInfo['status'] == 2){
                            $is_status = 2;
                            $is_statusArr[] = $item;
                        }
//                        if($agent_id){
//                            //获取代理库存
//                            $AgentStock = AgentStock::where('agent_id',$agent_id)->where('goods_id',$goodsCart['goods_id'])->find();
//                            $goodsInfo['stock'] = $AgentStock['num'] ?? 0;
//                        }
                        if($goodsInfo['stock'] < $goodsCart['num']){
                            $is_stock = 2;
                            $is_stockArr[] = ['id'=>$item,'num'=>$goodsInfo['stock']];
                        }
                    }
                }
                return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>['is_status'=>$is_status,'is_stock'=>$is_stock,'is_statusArr'=>$is_statusArr,'is_stockArr'=>$is_stockArr]]);
            }

        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
        }
    }

    /**
     * 订单取消
     */
    public function cancel(){
        try{
            $order_sn = $this->data['order_sn'];
            $order = db('order')->where(['order_sn'=>$order_sn,'uid'=>$this->uid])->find();
            if(!empty($order) && $order['status'] == 0 && $order['pay_status'] == 1){
                $res = db('order')->where(['order_sn'=>$order_sn])->update(['status'=>1]);
                if(!$res){
                    ServerResponse::message(Code::CODE_INTERNAL_ERROR);
                }
            }else{
                ServerResponse::message(Code::CODE_INTERNAL_ERROR);
            }
            return json(['StatusCode'=>20000,'message'=>'请求成功']);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }

    /**
     * 申请退款
     */
    public function refund(){
        try{
            $order_sn = $this->data['order_sn'];
            $order = db('order')->where(['order_sn'=>$order_sn,'uid'=>$this->uid])->find();
            if(!empty($order) && $order['status'] == 0 && $order['pay_status']  == 0){
                $res = db('order')->where(['order_sn'=>$order_sn])->update(['refund'=>3]);
                if(!$res){
                    ServerResponse::message(Code::CODE_INTERNAL_ERROR);
                }
                return json(['StatusCode'=>20000,'message'=>'请求成功']);
            }else{
                ServerResponse::message(Code::CODE_INTERNAL_ERROR);
            }
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }

    /**
     * 订单删除
     */
    public function delete(){
        try{
            $order_sn = $this->data['order_sn'];
            $order = db('order')->where(['order_sn'=>$order_sn,'uid'=>$this->uid])->find();
            if(!empty($order) && $order['status'] == 1){
                $res = db('order')->where(['order_sn'=>$order_sn])->update(['is_delete'=>1]);
                if(!$res){
                    return json(['StatusCode'=>50000,'message'=>'删除失败']);
                }
                return json(['StatusCode'=>20000,'message'=>'请求成功']);
            }else{
                return json(['StatusCode'=>50000,'message'=>'该订单不允许删除']);
                ServerResponse::message(Code::CODE_INTERNAL_ERROR,'该订单不允许删除！');
            }
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }

    /**
     * 确认收货
     */
    public function confirmGoods(){
        //try{
            $order_sn = $this->data['order_sn'];
            $order = Db::name('order')->where(['order_sn'=>$order_sn,'uid'=>$this->uid])->find();
            if(!empty($order) && $order['status'] == 3){
                $res = Db::name('order')->where(['order_sn'=>$order_sn])->update(['status'=>2,'confirmd_at'=>date('Y-m-d H:i:s')]);
                if(!$res){
                    ServerResponse::message(Code::CODE_INTERNAL_ERROR);
                }
                //增加分销提成
                //if($order['type'] == 0){
                //判断用户是否具有分销资格
                $subCommission = $this->subCommission($order,1);
                //}
                //增加销量
                $this->updateStock($order,$order['agent_id'],1);
                //结算给代理用户
                $this->agentMoney($order,$order['agent_id'],$subCommission);
                //判断用户消费类型是套盒，并且满398获得分销资格
                $userInfo = Db::name('user')->where('uid',$order['uid'])->field('is_distribution')->find();
                if($order['money'] >= 398 && $order['order_type'] == 2 && $userInfo['is_distribution'] == 1){
                    //更新分销资格
                    Db::name('user')->where('uid',$order['uid'])->update(['is_distribution'=>2]);
                }
                Db::name('order')->where(['id'=>$order['id']])->update(['is_distribution'=>'2']);
                return json(['StatusCode'=>20000,'message'=>'请求成功']);
            }else{
                ServerResponse::message(Code::CODE_INTERNAL_ERROR);
            }
//        }catch (\Exception $exception){
//            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
//        }
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
                    'tuser_id'=>$this->uid,
                ];
                Db::name('user_commission')->insertGetId($data);
            }
        }
    }
    /**
     * 创建订单(立即购买)
     */
    public function create(OrderModel $orderModel, OrderGoods $orderGoods,Goods $goods){
        $agent_id = $this->data['agent_ids'] ?? 0;
        //try{
            Db::startTrans();
            if(!$this->data['address_id']){
                return json(['StatusCode'=>50000,'message'=>'请选择收货地址']);
                ServerResponse::message(Code::CODE_OTHER_FAIL,'请选择收货地址');
            }
            if($this->data['type'] == 2){
                //立即购买
                $goods_id = $this->data['goods_id'];
                $num = $this->data['num'];
                //判断商品库存是否足够
                $goodsInfo = \app\common\model\Goods::where(['id'=>$goods_id])->find();
                if($goodsInfo['status'] == 2){
                    ServerResponse::message(Code::CODE_OTHER_FAIL,'商品已下架');
                }
                if($goodsInfo['stock'] < $num){
                    ServerResponse::message(Code::CODE_OTHER_FAIL,'库存不足');
                }
                $freight_money = 0;
                $orderInfo = [
                    'uid'=>$this->uid,
                    'order_sn'=>get_order_sn(),
                    'money'=>$goodsInfo['price'] * $num,
                ];
                //计算运费
                $address_id = $this->data['address_id'] ?? 0;
                if($address_id){
                    //计算运费
                    $address = Db::table('bear_user_address')->where(['id'=>$address_id])->find();
                    $freight_money += $this->freightCalculations($address,$goodsInfo,$num,$goodsInfo['weight']);
                    $pay_money = $goodsInfo['price'] * $num;// + $freight_money;
                    $orderInfo['pay_money'] = $pay_money;
                    //获取地址信息
                    $orderInfo['address'] = $address['address'] ?? '';
                    $orderInfo['phone'] = $address['phone'] ?? '';
                    $orderInfo['city'] = $address['city'] ?? '';
                    $orderInfo['province'] = $address['province'] ?? '';
                    $orderInfo['area'] = $address['area'] ?? '';
                    $orderInfo['uid'] = $this->uid;
                    $orderInfo['address_id'] = $address['id'] ?? [];
                    $orderInfo['name'] = $address['name'] ?? [];
                    $orderInfo['freight_money'] = $freight_money;//$this->freightCalculations($this->data['address_id'],$goods);
                }
                $orderInfo['num'] = $num;
                $orderInfo['created_at'] = date('Y-m-d H:i:s');
                $user = User::where('uid',$this->uid)->field('agent_id')->find();
                if($user['agent_id']){
                    //给用户绑定代理关系
                    $orderInfo['agent_id'] = $user['agent_id'];
                }else{
                    if(!$agent_id){
                        //当前没有代理信息，找分享人上级的代理id,无线往上查找
                        $orderInfo['agent_id'] = 0;
                        if(isset($this->data['pid']) && $this->data['pid']){
                            $orderInfo['agent_id'] = $this->getUser($this->data['pid']);
                        }
                    }else{
                        $orderInfo['agent_id'] = $agent_id;
                    }
                }
                //$orderInfo['agent_id'] = $user['agent_id'] ?? $agent_id;
                $order_id = Db::table('bear_order')->insertGetId($orderInfo);
                if(!$order_id){
                    Db::rollback();
                    ServerResponse::message(Code::CODE_OTHER_FAIL,'订单创建失败');
                }
                //商品入库
                $orderGoods = [
                    'order_id'=>$order_id,
                    'goods_id'=>$goods_id,
                    'number'=>$num,
                    'image'=>$goodsInfo['image'],
                    'title'=>$goodsInfo['title'],
                    'price'=>$goodsInfo['price'],
                    'total_money'=>$goodsInfo['price'],
                    'created_at'=>date('Y-m-d H:i:s'),
                ];
                $orderGoodsDataRes = Db::name('order_goods')->insertGetId($orderGoods);
                if(!$orderGoodsDataRes){
                    Db::rollback();
                    ServerResponse::message(Code::CODE_OTHER_FAIL,'订单创建失败');
                }
            }else{
                //购物车购买
                $cart_id = $this->data['cart_id'];
                //判断商品库存是否足够
                $cart_list = explode(',',$cart_id);
                $money = 0;
                $num = 0;
                $address_id = $this->data['address_id'] ?? 0;
                if($address_id){
                    $address = Db::name('user_address')->where(['id'=>$address_id])->find();
                }
                $freight_money = 0;//运费
                $orderGoodsArr = [];
                if(!empty($cart_list)){
                    foreach ($cart_list as $item){
                        $goodsCart = \app\common\model\Cart::where(['id'=>$item])->find();
                        $goodsInfo = \app\common\model\Goods::where(['id'=>$goodsCart['goods_id']])->find();
                        if($goodsInfo['status'] == 2){
                            ServerResponse::message(Code::CODE_OTHER_FAIL,'商品已下架');
                        }
                        if($goodsInfo['stock'] < $goodsCart['num']){
                            return ServerResponse::message(Code::CODE_OTHER_FAIL,$goodsInfo['title'].'库存不足');
                        }
                        //金额相加
                        $money += ($goodsInfo['price'] * $goodsCart['num']);
                        //数量相加
                        $num += $goodsCart['num'];
                        //商品数组
                        $orderGoods = [
                            'order_id'=>0,
                            'goods_id'=>$goodsCart['goods_id'],
                            'number'=>$goodsCart['num'],
                            'image'=>$goodsInfo['image'],
                            'title'=>$goodsInfo['title'],
                            'price'=>$goodsInfo['price'],
                            'total_money'=>$goodsInfo['price'] * $goodsCart['num'],
                            'created_at'=>date('Y-m-d H:i:s'),
                        ];
                        if($address_id){
                            $freight_money += $this->freightCalculations($address,$goodsInfo,$goodsCart['num'],$goodsInfo['weight']);
                        }
                        $orderGoodsArr[] = $orderGoods;
                    }
                }

                $orderInfo = [
                    'uid'=>$this->uid,
                    'order_sn'=>get_order_sn(),
                    'money'=>$money,
                ];
                //计算运费

                if($address_id){
                    //计算运费
                    $pay_money = $money;// + $freight_money;
                    $orderInfo['pay_money'] = $pay_money;
                    //获取地址信息
                    $orderInfo['address'] = $address['address'] ?? '';
                    $orderInfo['phone'] = $address['phone'] ?? '';
                    $orderInfo['city'] = $address['city'] ?? '';
                    $orderInfo['province'] = $address['province'] ?? '';
                    $orderInfo['area'] = $address['area'] ?? '';
                    $orderInfo['uid'] = $this->uid;
                    $orderInfo['address_id'] = $address['id'] ?? [];
                    $orderInfo['name'] = $address['name'] ?? [];
                    $orderInfo['freight_money'] = $freight_money;//$this->freightCalculations($this->data['address_id'],$goods);
                }
                $orderInfo['created_at'] = date('Y-m-d H:i:s');
                $orderInfo['num'] = $num;


                $user = User::where('uid',$this->uid)->field('agent_id,parent_id')->find();

                if($user['agent_id']){
                    //给用户绑定代理关系
                    $orderInfo['agent_id'] = $user['agent_id'];
                }else{
                    if(!$agent_id){
                        //当前没有代理信息，找分享人上级的代理id,无线往上查找
                        $orderInfo['agent_id'] = 0;
                        if(isset($this->data['pid']) && $this->data['pid']){
                            $orderInfo['agent_id'] = $this->getUser($this->data['pid']);
                        }else{
                            if($user['parent_id']) $orderInfo['agent_id'] = $this->getUser($user['parent_id']);
                        }
                    }else{
                        $orderInfo['agent_id'] = $agent_id;
                    }
                }

                //$orderInfo['agent_id'] = $agent_id;

                $order_id = Db::table('bear_order')->insertGetId($orderInfo);
                if(!$order_id){
                    Db::rollback();
                    ServerResponse::message(Code::CODE_OTHER_FAIL,'订单创建失败');
                }
                //商品入库
                if(!empty($orderGoodsArr)){
                    foreach ($orderGoodsArr as $key=>$value){
                        $orderGoodsArr[$key]['order_id'] = $order_id;
                    }
                }
                $orderGoodsDataRes = Db::table('bear_order_goods')->insertAll($orderGoodsArr);
                if(!$orderGoodsDataRes){
                    Db::rollback();
                    ServerResponse::message(Code::CODE_OTHER_FAIL,'订单创建失败');
                }
                //删除购物车数据
                $res = Cart::destroy($this->data['cart_id']);
                if(!$res){
                    Db::rollback();
                    ServerResponse::message(Code::CODE_OTHER_FAIL,'订单创建失败');
                }
            }
            if(!$user['agent_id'] && $agent_id){
                //给用户绑定代理关系
                User::where('uid',$this->uid)->update(['agent_id'=>$agent_id]);
            }
            Db::commit();
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>['order_id'=>$order_id]]);
//        }catch (\Exception $exception){
//            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
//        }
    }

    /**
     * 根据收货地址计算运费
     */
    public function freight(){
       try{
            $freight_money = 0;
            $address_id = $this->data['address_id'] ?? 0;
            if($address_id){
                $address = Db::name('user_address')->where(['id'=>$address_id])->find();
            }

           if($this->data['type'] == 3){
                //套盒计算运费
               $num = $this->data['num'] ?? 0;
               $goodsInfo = Meal::where(['id'=>1])->find();
               if($address_id){
                   $address = Db::name('user_address')->where(['id'=>$address_id])->find();
                   $freight_money = $this->freightCalculations($address,$goodsInfo,1,$goodsInfo['weight']);
               }
               return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>['money'=>$freight_money]]);

           }
            if($this->data['type'] == 2){
                //立即购买计算
                $num = $this->data['num'] ?? 0;
                $goodsInfo = \app\common\model\Goods::where(['id'=>$this->data['goods_id']])->find();
                if($address_id){
                    $address = Db::name('user_address')->where(['id'=>$address_id])->find();
                    $freight_money = $this->freightCalculations($address,$goodsInfo,$num,$goodsInfo['weight']);
                }
            }else{
                //购物车结算的
                $cart_id = $this->data['cart_id'];
                $cart_list = explode(',',$cart_id);
                //判断商品库存是否足够
                if(!empty($cart_list)){
                    foreach ($cart_list as $item){
                        $goodsCart = \app\common\model\Cart::where(['id'=>$item])->find();
                        $goodsInfo = \app\common\model\Goods::where(['id'=>$goodsCart['goods_id']])->find();

                        $freight_money += $this->freightCalculations($address,$goodsInfo,$goodsCart['num'],$goodsInfo['weight']);
                    }
                }
            }
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>['money'=>$freight_money]]);
            ServerResponse::message(Code::CODE_SUCCESS,'',['money'=>$freight_money]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
        }
    }


    /**
     * 递归获取终端代理下面的终端代理
     * @param int $user_id 当前会员
     * @param int $tuser_id 上级会员
     * @return array
     */
    protected function getUser($user_id = 0){
        $agent_id = 0;
        $user = Db::name('user')->where('uid',$user_id)->field('parent_id,agent_id')->find();
        if($user['agent_id']){
            $agent_id = $user['agent_id'];
            //return $user['agent_id'];

        }else{
            //判断当前会员是否有代理，有代理的话直接返回，没有代理的话往上无线查
            if($user['parent_id']){
                $agent_id = $this->getUser($user['parent_id']);
            }
        }

        return $agent_id;

    }

    /**
     * 计算运费
     */
    protected function freightCalculations($address,$goods,$num,$weight){
        //先获取这个商品是否设置
        $weight = $weight * $num;
        if($goods['express_id']){
            $delivery = Db::name('delivery')->where(['id'=>$goods['express_id']])->find();
            $delivery_rule = Db::name('delivery_rule')->where(['delivery_id'=>$goods['express_id']])->select();
            //计算单个商品的价格
            $money = 0;
            if(!empty($delivery_rule)){
                foreach ($delivery_rule as $kk=>$vv){
                    if(strpos($vv['region'],$address['city_id'].',') !==false){
                        //包含在内，计算运费
                        if($delivery['method'] == 1){
                            //按件数
                            $money = $vv['first_fee'] ?? 0;
                            if($vv['additional'] < $num){
                                //满足条件，根据运费规则加收运费加收运费
                                if($vv['additional']){
                                    $count = floor($num / $vv['additional']);

                                    for ($i = 0; $i< $count-1; $i++){
                                        $money += $vv['additional_fee'];
                                    }
                                }

                            }
                        }else{
                            //按重量
                            $money = $vv['first_fee'] ?? 0;

                            if($vv['additional'] < $weight){
                                //满足条件，根据运费规则加收运费加收运费
                                if($vv['additional']){
                                    $count = floor($weight / $vv['additional']);

                                    for ($i = 0; $i< $count-1; $i++){
                                        $money += $vv['additional_fee'];
                                    }
                                }

                            }
                        }
                        break;
                    }
                }
            }

        }else{
            $money = 0;
        }
        return $money;
    }
}