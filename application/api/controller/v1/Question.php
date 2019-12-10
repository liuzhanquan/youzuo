<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/8/7
 * Time: 9:34
 */

namespace app\api\controller\v1;
use app\api\controller\Api;
use app\backman\model\Meal;
use app\backman\model\QuestionGoods;
use app\common\model\AgentStock;
use app\common\model\Cart;
use app\common\model\Goods;
use app\common\model\Question as QuestionModel;
use app\common\model\User;
use app\common\model\UserQuestion;
use hg\Code;
use hg\ServerResponse;
use think\Db;

class Question extends Api
{
    /**
     * 获取问答列表
     * @param QuestionModel $question
     * @return \think\response\Json
     */
    public function index(QuestionModel $question){
        try{
            $list = $question->order('sort asc')->field('id,answer,title')->select();
            if(!empty($list)){
                foreach ($list as $key=>$value){
                    $list[$key]['answer'] = unserialize($value['answer']);
                }
            }
            //if(empty($data)) ServerResponse::message(Code::CODE_NO_CONTENT);
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$list]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
        }
    }

    /**
     * 用户提交问卷调查
     */
    public function create(UserQuestion $userQuestion, QuestionModel $question){
        //try{
            $this->data['uid'] = $this->uid;
            $answer = $this->data['answer'];
            $answer = explode(',',$answer);
            //根据回答的内容，匹配出对应的产品
            $strArr = [];
            if(!empty($answer)){
                foreach ($answer as $t=>$v){
                    $arr = explode('_',$v);
                    $strArr[]=$arr[0].'_'.$arr[1];
                }
            }
            $str = implode('-',$strArr);
            $this->data['answer'] = serialize($this->data['answer']);
            $this->data['sex'] = $this->data['sex'] == '男' ? 1 : 2;
            $this->data['str'] = $str;
            $this->data['timestamp'] = date('Y-m-d H:i:s');
            $list = QuestionGoods::where(['answer'=>$str])->find();
            $goods = [];
            if(!empty($list)){
                //查询产品
                //$where['id'] = ['in',[$list->goods['value']]];
                $goods = Goods::field('title,image,price,id')->all($list->goods['value']);
                if(!empty($goods)){
                    foreach ($goods as $key=>$value){
                        $goods[$key]['image'] = request()->domain().str_replace("\\", '/', $value['image']);
                    }
                }
            }
            if(!empty($goods)){
                $this->data['goods'] = $list->goods['value'];
            }
            if(!$userQuestion->allowField(true)->save($this->data)){
                return json(['StatusCode'=>50000,'message'=>'保存失败']);
            }


            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$goods]);
//        }catch (\Exception $exception){
//            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
//        }
    }

    public function newGoods(){
        //try{
        $this->data['uid'] = $this->uid;
        $info = UserQuestion::where(['uid'=>$this->uid])->order('id','desc')->find();
        $goods = [];
        if(!empty($info)){
            $list = QuestionGoods::where(['answer'=>$info['str']])->find();
            $Meal = Meal::where('id',1)->find();
            if(!empty($info['goods'])){
                //查询产品
                //$where['id'] = ['in',[$list->goods['value']]];
                $goods = Goods::field('title,image,price,id')->all($info['goods']);
                if(!empty($goods)){
                    foreach ($goods as $key=>$value){
                        $goods[$key]['image'] = request()->domain().str_replace("\\", '/', $value['image']);
                        $goods[$key]['price'] = $Meal['price'];
                    }
                }
            }
        }

        return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>['info'=>$info,'goods'=>$goods,'priceCount'=>$Meal['price']]]);
//        }catch (\Exception $exception){
//            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
//        }
    }

    public function newGoodsCart(){
        //try{
        Db::startTrans();
        $agent_id = $this->data['agent_ids'] ?? 0;
        $this->data['uid'] = $this->uid;
        $id = $this->data['id'];
            if(!empty($id)){
                //查询产品
                //$where['id'] = ['in',[$list->goods['value']]];
                $goods = Goods::all($id);
                if(!empty($goods)){
                    //创建订单
                $money = 0;
                $num = 0;
                $address_id = $this->data['address_id'] ?? 0;
                if($address_id){
                    $address = Db::name('user_address')->where(['id'=>$address_id])->find();
                }
                $freight_money = 0;//运费
                $orderGoodsArr = [];
                $Meal = Meal::where('id',1)->find();
                //校验套餐库存，有代理的，直接校验代理库存
                    $user = User::where('uid',$this->uid)->find();
                    if($user['agent_id'] > 0){
                        //校验代理库存
//                            $stock = AgentStock::where([['type','eq',2],['agent_id','eq',$user['agent_id']],['goods_id','eq',1]])->find();
//                            if(!$stock){
//                                return json(['StatusCode'=>50000,'message'=>'上级代理库存不足']);
//                            }
//                            if($stock['num'] < 1){
//                                return json(['StatusCode'=>50000,'message'=>'上级代理库存不足']);
//                            }
                    }else{
                        //判断当前是否是有带代理id
                        if($agent_id){
                            //带代理id,校验代理库存
//                            $stock = AgentStock::where([['type','eq',2],['agent_id','eq',$agent_id],['goods_id','eq',1]])->find();
//                            if(!$stock){
//                                return json(['StatusCode'=>50000,'message'=>'上级代理库存不足']);
//                            }
//                            if($stock['num'] < 1){
//                                return json(['StatusCode'=>50000,'message'=>'上级代理库存不足']);
//                            }
                        }

                    }
                if(!empty($goods)){
                    foreach ($goods as $item){
                        //金额相加
                        $money += ($Meal['price'] * 1);
                        //数量相加
                        $num += 1;
                        //商品数组
                        $orderGoods = [
                            'order_id'=>0,
                            'goods_id'=>$item['id'],
                            'number'=>1,
                            'image'=>$item['image'],
                            'title'=>$item['title'],
                            'price'=>$Meal['price'],
                            'total_money'=>$Meal['price'] * 1,
                            'created_at'=>date('Y-m-d H:i:s'),
                        ];
                        if($address_id){
                            //$freight_money += $this->freightCalculations($address,$item,1,$item['weight']);
                        }
                        $orderGoodsArr[] = $orderGoods;
                    }
                }
                $infos = Meal::where('id',1)->find();
                $freight_money = $this->freightCalculations($address,$infos,1,$infos['weight']);
                $orderInfo = [
                    'uid'=>$this->uid,
                    'order_sn'=>get_order_sn(),
                    'money'=>$Meal['price'] + $freight_money,
                    'order_type'=>2,
                    't_id'=>$Meal['id'],
                ];
                //计算运费

                if($address_id){
                    //计算运费
                    $pay_money = $Meal['price']; //+ $freight_money;
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

                if($user['agent_id'] > 0){
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
                        $orderGoodsArr[$key]['type'] = 2;
                        $orderGoodsArr[$key]['t_id'] = 1;
                    }
                }
                $orderGoodsDataRes = Db::table('bear_order_goods')->insertAll($orderGoodsArr);
                if(!$orderGoodsDataRes){
                    Db::rollback();
                    ServerResponse::message(Code::CODE_OTHER_FAIL,'订单创建失败');
                }
                if(!$user['agent_id'] && $orderInfo['agent_id']){
                    //给用户绑定代理关系
                    User::where('uid',$this->uid)->update(['agent_id'=>$agent_id]);
                }
                Db::commit();
                    return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>['order_id'=>$order_id]]);
                }else{
                    Db::rollback();
                    return json(['StatusCode'=>50000,'message'=>'请求失败']);
                }
            }else{
                Db::rollback();
                return json(['StatusCode'=>50000,'message'=>'请求失败']);
            }
//        }catch (\Exception $exception){
//            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
//        }
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