<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/10/23
 * Time: 18:07
 */

namespace app\api\controller\v1;
use app\common\model\AgentStock;
use app\common\model\ZOrder;
use app\common\model\ZOrderLog;
use app\common\model\Logistics;
use app\api\controller\Api;
use hg\ServerResponse;
use hg\Code;
use think\Db;

class ZOrderAgent extends Api
{
    /**
     * 自提订单列表
     * @param ZOrder $ZOrder
     * @param ZOrderLog $ZOrderLog
     * @param Logistics $logistics
     * @return \think\response\Json
     */
    public function index(ZOrder $ZOrder , ZOrderLog $ZOrderLog, Logistics $logistics){
        try{
            //$where['uid'] = $this->uid;
            $where['agent_id'] = $this->data['agent_id'];
            $type = $this->data['type'] ?? 0;
            if($type){
                $where['type'] = $type;
            }
            $list = $ZOrder->where($where)->order('id','desc')->paginate(10,false,['query'=>request()->param()])->toArray();
            $data = $list['data'];
            if(!empty($data)){
                foreach ($data as $key=>$value){
                    $ZOrderLogList = $ZOrderLog->where(['z_order_id'=>$value['id']])->field('msg,created_at')->order('id desc')->select();
                    $data[$key]['log'] = $ZOrderLogList;
                    $data[$key]['type_name'] = $value['type'] == 1 ? '线下自提':'线上发货';
                    $data[$key]['goods_type_name'] = $value['goods_type'] == 1 ? '普通商品':'套盒商品';
                    $statusName = '';
                    if($value['status'] == 1){
                        $statusName = '待审核';
                    }elseif($value['status'] == 2){
                        $statusName = '审核通过，并已经发货';
                    }elseif($value['status'] == 3){
                        $statusName = '审核拒绝';
                    }
                    $data[$key]['status_name'] = $statusName;
                    $logisticsInfo = $logistics->where('id',$value['logistics_id'])->field('name')->find();
                    $data[$key]['logistics_name'] = $logisticsInfo['name'] ?? '';
                    $data[$key]['pay_status_name'] = $value['pay_status'] == 1 ? '待支付':'已支付';
                }
            }
            //if(empty($data)) ServerResponse::message(Code::CODE_NO_CONTENT);
            $list['data'] = $data;
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$list]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
            //return json(['StatusCode'=>50000,'message'=>$exception->getMessage()]);
        }
    }

    /**
     * 自提订单详情
     * @param ZOrder $ZOrder
     * @param ZOrderLog $ZOrderLog
     * @return \think\response\Json
     */
    public function details(ZOrder $ZOrder , ZOrderLog $ZOrderLog, Logistics $logistics){
        //try{
            //$where['uid'] = $this->uid;
            $where['agent_id'] = $this->data['agent_id'];
            $id = $this->data['id'];
            if($id){
                $where['id'] = $id;
            }else{
                return json(['StatusCode'=>50000,'message'=>'参数错误']);
            }
            $info = $ZOrder->where($where)->field('created_at,num,id,type,agent_id,status,logistics_id,logistics_sn,pay_status,pay_time,address_id,logistics_money,order_sn')->find();
            if(!empty($info)){
                    $ZOrderLogList = $ZOrderLog->where(['z_order_id'=>$info['id']])->field('msg,created_at')->order('id desc')->select();
                    $info['log'] = $ZOrderLogList;
                    $info['type_name'] = $info['type'] == 1 ? '线下自提':'线上发货';
                    $info['goods_type_name'] = $info['type'] == 1 ? '普通商品':'套盒商品';
                $statusName = '';
                if($info['status'] == 1){
                    $statusName = '待审核';
                }elseif($info['status'] == 2){
                    $statusName = '审核通过，并已经发货';
                }elseif($info['status'] == 3){
                    $statusName = '审核拒绝';
                }
                $info['pay_status_name'] = $info['pay_status'] == 1 ? '待支付':'已支付';
                $info['status_name'] = $statusName;
                $logisticsInfo = $logistics->where('id',$info['logistics_id'])->field('name')->find();
                $info['logistics_name'] = $logisticsInfo['name'];
                $info['address'] = Db::name('user_address')->where('id',$info['address_id'])->find();
                $info['goods'] = Db::name('z_order_goods')->where('z_order_id',$info['id'])->select();
            }
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$info]);
//        }catch (\Exception $exception){
//            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
//        }
    }

    /**
     * 自提订单申请
     * @param ZOrder $ZOrder
     * @param ZOrderLog $ZOrderLog
     * @return \think\response\Json
     */
    public function create(ZOrder $ZOrder , ZOrderLog $ZOrderLog){
        //try{
            $ZOrder->startTrans();
            $where['uid'] = $this->uid;
            $where['agent_id'] = $this->data['agent_id'];

            if(!$this->data['agent_id'] || !$this->data['goods_type'] || !$this->data['goods']|| !$this->data['type']){
                return json(['StatusCode'=>50000,'message'=>'参数错误']);
            }
            $address_id = $this->data['address_id'] ?? 0;
            if($this->data['type'] == 2){
                if(!$address_id){
                    return json(['StatusCode'=>50000,'message'=>'请选择收货地址']);
                }
            }

            if($address_id){
                $address = Db::name('user_address')->where(['id'=>$address_id])->find();
            }
            //插入订单商品
            $goods = $this->data['goods'];
            $goods = explode(',',$goods);
            $arr = [];
            $money = 0;
            $goodsNum = 0;
            $freight_money = 0;
            $weight = 0;
            if(!empty($goods)){
                foreach ($goods as $k=>$v){
                    $info = explode('-',$v);
                    if($this->data['goods_type'] == 1){
                        $goodsInfo = Db::name('goods')->where('id',$info[0])->find();
                    }else{
                        $goodsInfo = Db::name('meal')->where('id',$info[0])->find();
                    }
                    $data = [
                        'num'=>$info[1],
                        'goods_id'=>$info[0],
                        'price'=>$goodsInfo['price'],
                        'money'=>$goodsInfo['price'] * $info[1],
                        'created_at'=>date('Y-m-d H:i:s'),
                        'updated_at'=>date('Y-m-d H:i:s'),
                    ];
                    $weight += $goodsInfo['weight'] * $info[1];
                    if($address_id){
                        $freight_money += $this->freightCalculations($address,$goodsInfo,$info[1],$goodsInfo['weight']);
                    }
                    if($this->data['goods_type'] == 1){
                        $name = Db::name('goods')->where('id',$data['goods_id'])->value('title');
                    }else{
                        $name = Db::name('meal')->where('id',$data['goods_id'])->value('name');
                    }
                            $num = AgentStock::where('agent_id',$where['agent_id'])->where('goods_id',$data['goods_id'])->field('*')->find();
                            if($num['num'] < $data['num']){

                                $ZOrder->rollback();
                                return json(['StatusCode'=>50000,'message'=>"商品：‘{$name}’库存不足"]);
                            }
                            $data['goods_name'] = $name;

                    $arr[] = $data;
                    $money += $goodsInfo['price'] * $info[1];
                    $goodsNum += $info[1];
                }
            }
            $this->data['num'] = $goodsNum;
            $this->data['money'] = $money;
            $this->data['created_at'] = date('Y-m-d H:i:s');
            $this->data['updated_at'] = date('Y-m-d H:i:s');
            $this->data['logistics_money'] = $freight_money;
            $this->data['user_id'] = $this->uid;
            $this->data['order_sn'] = get_order_sn();
            $this->data['weight'] = $weight;
            $this->data['remarks'] = $this->data['remarks'] ?? '无';
            if($this->data['type'] == 2){
                $this->data['pay_status'] = 1;
            }else{
                $this->data['pay_status'] = 2;
                $this->data['pay_time'] = date('Y-m-d H:i:s');
            }
            $res = $ZOrder->allowField(true)->save($this->data);
            if(!$res){
                $ZOrder->rollback();
                return json(['StatusCode'=>50000,'message'=>'请求失败，请稍后重试']);
            }
            $id = $ZOrder->getLastInsID();
            foreach ($arr as $key=>$item){
                $arr[$key]['z_order_id'] = $id;
            }
            $r = Db::name('z_order_goods')->insertAll($arr);
            if(!$r){
                $ZOrder->rollback();
                return json(['StatusCode'=>50000,'message'=>'请求失败，请稍后重试']);
            }
            $ZOrder->commit();
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>['order_id'=>$id,'order_sn'=>$this->data['order_sn'],'money'=>$freight_money,'weight'=>$weight]]);
//        }catch (\Exception $exception){
//            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
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