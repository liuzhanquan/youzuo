<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/8/7
 * Time: 19:09
 */

namespace app\api\controller\v1;
use app\backman\model\Agent as AgentModel;
use app\api\controller\Api;
use app\backman\model\AgentLevel;
use app\backman\model\Meal;
use app\common\model\AgentStock;
use app\common\model\AgentStockLog;
use app\common\model\AgentStockOrder;
use app\common\model\AgentWithdraw;
use app\common\model\Friend;
use app\common\model\FriendCollection;
use app\common\model\FriendLike;
use app\common\model\Goods;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\RechargeMoney;
use app\common\model\RechargeOrder;
use hg\Code;
use hg\ServerResponse;
use app\common\model\User;
use think\Db;
use think\Exception;
use think\facade\Cache;
use think\facade\Env;
use think\facade\Request;

class Agent extends Api
{
    /**
     * 根据用户获取代理提交的信息
     */
    public function getAgentInfo(){
        try{
            $user = User::where('uid',$this->uid)->field('openid')->find();
            $info = \app\backman\model\Agent::where('openid',$user['openid'])->find();
            if($info){
                    if($info['agent_parent_id']){
                        $info['code'] = \app\backman\model\Agent::where('id',$info['agent_parent_id'])->value('code');
                    }else{
                        $info['code'] = '';
                    }
            }
            return json(['StatusCode'=>20000,'message'=>'请求成功！','data'=>$info ?? []]);
        }catch (\Exception $exception){

        }
    }




    /**
     * 代理申请
     */
    public function create(AgentModel $agent){
        //try{
            $pid = $this->data['agent_parent_id'] ?? 0;
            $code = $this->data['code'] ?? '';
            $info = User::where(['uid'=>$this->uid])->field('openid,nickname,headimgurl')->find();
            $agent = AgentModel::where(['openid'=>$info['openid']])->field('id,status')->find();
            if(!empty($agent)){
                if($agent['status'] == 1){
                    return json(['StatusCode'=>50000,'message'=>'已经是代理，无需重复申请']);
                }
                if($agent['status'] == 0){
                    return json(['StatusCode'=>50000,'message'=>'系统审核中']);
                }
                //更新数据
                $this->data['pwd'] = md5($this->data['pwd']);
                $this->data['openid'] = $info['openid'];
                $this->data['headimgurl'] = $info['headimgurl'];
                $this->data['nickname'] = json_decode($info['nickname']);
                $this->data['status'] = 0;
                if($code){
                    $this->data['agent_parent_id'] = \app\backman\model\Agent::where('code',$code)->value('id') ?? 0;
                }
                if($pid){
                    $this->data['agent_parent_id'] = $pid;
                }
                $a = new \app\backman\model\Agent();
                $this->data['code'] = rands(5);
                if(!$a->allowField(true)->save($this->data,['id'=>$agent['id']])){
                    return json(['StatusCode'=>50000,'message'=>'申请失败！']);
                }
                return json(['StatusCode'=>20000,'message'=>'申请成功！']);
            }


            $this->data['pwd'] = md5($this->data['pwd']);
            $this->data['openid'] = $info['openid'];
            $this->data['headimgurl'] = $info['headimgurl'];
            $this->data['nickname'] = json_decode($info['nickname']);
            if($code){
                $this->data['agent_parent_id'] = \app\backman\model\Agent::where('code',$code)->value('id') ?? 0;
            }
            if($pid){
                $this->data['agent_parent_id'] = $pid;
            }
            $l = new \app\backman\model\Agent();
            $this->data['code'] = rands(5);
            $this->data['reg_time'] = date('Y-m-d H:i:s');
            if(!$l->save($this->data)){
                return json(['StatusCode'=>50000,'message'=>'申请失败！']);
            }
            //更改用户的代理关系
            $user = Db::name('user')->select();
            if(!empty($user)){
                foreach ($user as $kkk=>$vvv){
                    if($vvv['parent_id']){
                        $agent_id = $this->getUser($vvv['parent_id']);
                        if($agent_id){
                            if($vvv['agent_id'] != $agent_id){
                                //更新当前用户代理关系
                                Db::name('user')->where('uid',$vvv['uid'])->update(['agent_id'=>$agent_id]);
                            }
                        }
                    }
                }
            }
            return json(['StatusCode'=>20000,'message'=>'申请成功！']);
//        }catch (\Exception $exception){
//            return json(['StatusCode'=>50000,'message'=>'内部服务器错误']);
//        }
    }

    /**
     * 递归获取终端代理下面的终端代理
     * @param int $user_id 当前会员
     * @param int $tuser_id 上级会员
     * @return array
     */
    protected function getUser($user_id = 0){
        $agent_id = 0;
        $user = Db::name('user')->where('uid',$user_id)->field('parent_id,agent_id,openid')->find();
        //判断当前用户是否是代理
        $agent = Db::name('agent')->where('openid',$user['openid'])->find();
        if($agent){
            $agent_id = $agent['id'];
        }else{
            if($user['agent_id']){
                $agent_id = $user['agent_id'];
                //return $user['agent_id'];

            }else{
                //判断当前会员是否有代理，有代理的话直接返回，没有代理的话往上无线查
                if($user['parent_id']){
                    $agent_id = $this->getUser($user['parent_id']);
                }
            }
        }


        return $agent_id;

    }

    /**
     * 修改密码
     */
    public function updatePwd(){
        try{
            $pwd = $this->data['pwd'];
            $new_pwd = $this->data['new_pwd'];
            $agent_id  = $this->data['agent_id'];
            $openid = User::where(['uid'=>$this->uid])->field('openid')->find();
            $agent = AgentModel::where(['openid'=>$openid])->field('id,pwd')->find();
            if(!$agent || $agent['id'] != $agent_id){
                return json(['StatusCode'=>50000,'message'=>'非法操作']);
            }
            if(md5($new_pwd) != md5($pwd)){
                return json(['StatusCode'=>50000,'message'=>'原密码错误']);
            }
            if(!$agent->save(['pwd'=>md5($new_pwd)],['id'=>$agent_id])){
                return json(['StatusCode'=>50000,'message'=>'密码修改失败']);
            }
        }catch (Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }

    /**
     * 代理登陆
     * @param AgentModel $agent
     */
    public function login(AgentModel $agent){
        try{
            $phone = $this->data['phone'];
            $pwd = md5($this->data['pwd']);
            $user = User::where('uid',$this->uid)->field('openid')->find();
            $info = AgentModel::where(['phone'=>$phone,'pwd'=>$pwd,'openid'=>$user['openid']])->find();
            if(!$info){
                return json(['StatusCode'=>50000,'message'=>'手机号码或者密码错误']);
            }
            if($info['status'] == 0){
                return json(['StatusCode'=>50000,'message'=>'系统审核中！']);
            }
            if($info['status'] == 2){
                return json(['StatusCode'=>50000,'message'=>'审核被拒绝，请重新申请！']);
            }
            return json(['StatusCode'=>20000,'message'=>'登陆成功','data'=>$info]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR);
        }
    }

    /**
     * 获取代理邀请码
     */
    public function getAgentCode(){
        try{
            $id = $this->data['agent_id'];
            $info = AgentModel::where(['id'=>$id])->field('code')->find();
            if(!$info){
                return json(['StatusCode'=>50000,'message'=>'代理信息不存在'.$id]);
            }
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$info]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR);
        }
    }

    /**
     * 获取某个代理团队进货记录（不包括自己）
     */
    public function getTeamAchievement(){
        try{
            $id = $this->data['agent_id'] ?? 0;
            if(!$id){
                return json(['StatusCode'=>50000,'message'=>'参数错误']);
            }
            //获取代理结算周期，
            $agent = Db::name('agent')->where('id',$id)->field('level_id')->find();
            $level = Db::name('agent_level')->where('id',$agent['level_id'])->field('month')->find();
            $list = Cache::get('agent_list_'.$id);
            //if(!$list){
                $list = $this->getAgentTeamList($id);
                //当前用户的业绩也得加上，并且给第一单打上标签
                $list[] = $id;
                //缓存一天
                Cache::set('agent_list_'.$id,$list,86400);
            //}
            //查询从上一次结算日期开始计算，判断上一次结算是否存在，存在的就从结算日期开始计算
            $jie = Db::name('agent_settlement')->where('agent_id',$id)->order('created_at desc')->find();       //获取当前用户的第一单
            if(Db::name('agent_stock_log')->where('is_settlement',1)->where('goods_id',1)->where('agent_id',$id)->where('type',2)->count() > 0){
                $one = Db::name('agent_stock_log')->where('is_settlement',1)->where('goods_id',1)->where('agent_id',$id)->where('type',2)->order('created_at asc')->find();
            }

            if($jie){
                $start_date = $jie['created_at'];
                $list = Db::name('agent_stock_log')->where('is_settlement',1)->where('status',2)->where('id','neq',$one['id'] ?? 0)->where('goods_id',1)->where('agent_id','in',implode(',',$list))->where('type',2)->whereTime('created_at', 'between', [$start_date, date('Y-m-d H:i:s')])->paginate(10,false,['query'=>request()->param()])->toArray();
            }else{
                $list = Db::name('agent_stock_log')->where('is_settlement',1)->where('status',2)->where('goods_id',1)->where('agent_id','in',implode(',',$list))->where('type',2)->where('id','neq',$one['id'] ?? 0)->paginate(10,false,['query'=>request()->param()])->toArray();
            }
            $data = $list['data'];
            $moneyNum = 0;
            if(!empty($data)){
                foreach ($data as $key=>$value){
//                    if($id == $value['agent_id'] && $value['is_one'] == 1){
//                        continue;
//                    }
                    $info = Db::name('meal')->where('id',$value['goods_id'])->find();
                    $data[$key]['name'] = $info['name'];
                    $data[$key]['money'] = $value['num'] * $value['price'];
                    $moneyNum += $value['num'] * $value['price'];
                    $data[$key]['agent_name'] = Db::name('agent')->where('id',$value['agent_id'])->value('name');
                }
            }
            Cache::set('agent_list_money_'.$id,$moneyNum,86400);
            //缓存一天
            if(!Cache::get('agent_list_money_'.$id)) Cache::set('agent_list_money_'.$id,$moneyNum,86400);
            //if(empty($data)) ServerResponse::message(Code::CODE_NO_CONTENT);
            $list['data'] = $data;
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$list]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR);
        }
    }


    /**
 * 获取某个代理的所有下级（无限级）
 * @param $agent_id int 代理id
 * @return array 返回代理id
 */
    protected function getAgentTeamList($agent_id){
        $arr = array();
        if($agent_id){
            $list = Db::name('agent')->where('agent_parent_id',$agent_id)->field('id')->select();
            if(!empty($list)){
                foreach ($list as $key=>$value){
                    $info = Db::name('agent')->where('id',$value['id'])->find();
                    if($info['level_id'] != 1){
                        $arr[] = $value['id'];
                    }
                    $arr = array_merge($this->getAgentTeamList($value['id']),$arr);
                }
            }
        }
        return $arr;
    }


    /**
     * 代理详情
     * @return \think\response\Json
     */
    public function details(){
        try{
            $id = $this->data['agent_id'];
            $info = AgentModel::where(['id'=>$id])->find();
            $info['level_name'] = AgentLevel::where(['id'=>$info['level_id']])->value('name');
            $info['avatar'] = User::where(['uid'=>$this->uid])->value('headimgurl');
            $info['collect_num'] = FriendCollection::where(['user_id'=>$this->uid])->count('id');
            $info['goods_stock'] = AgentStock::where(['user_id'=>$this->uid,'agent_id'=>$id])->sum('num');
            $info['agent_team_money'] =  0;
            if(Cache::get('agent_list_money_'.$id)) $info['agent_team_money'] = Cache::get('agent_list_money_'.$id);
            if(!$info){
                return json(['StatusCode'=>50000,'message'=>'代理信息不存在']);
            }
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$info]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR);
        }
    }

    /**
     * 获取充值金额
     */
    public function rechargeMoney(RechargeMoney $rechargeMoney){
        try{
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$rechargeMoney->where(['status'=>1])->order('money','asc')->field('money')->select()]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR);
        }
    }

    /**
     * 代理充值记录
     */
    public function rechargeMoneyLog(){
        try{
            $list = RechargeOrder::where('agent_id',$this->data['agent_id'])->field('pay_status,created_at,money')->paginate(10,false,['query'=>request()->param()])->toArray();
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$list]);


            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>RechargeOrder::where('agent_id',$this->data['agent_id'])->field('pay_status,created_at,money')->select()]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR);
        }
    }

    /**
     * 代理充值
     */
    public function rechargeMoneyPay(RechargeOrder $rechargeOrder){
        //try{
            if($this->data['money']){
                $data['pay_status'] = 1;
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['user_id'] = $this->uid;
                $data['agent_id'] = $this->data['agent_id'];
                $data['order_sn'] = get_order_sn();
                $data['money'] = $this->data['money'];
                $data['type'] = $this->data['type'];
                $data['voucher'] = $this->data['voucher'];
                if(!$rechargeOrder->allowField(true)->save($data)){
                    return json(['StatusCode'=>50000,'message'=>'充值失败']);
                }
                return json(['StatusCode'=>20000,'message'=>'请求成功']);
//                $openid = User::where(['uid'=>$this->uid])->value('openid');
//                $pay = unserialize(config('config.site')['weixin']);
//                $orderMoney = $data['money'];
//                $order_sn = $data['order_sn'];
//                $request = Request::instance();
//                $jsApi = [
//                    'appid'=>$pay['appid'],
//                    'appsecret'=>$pay['secret'],
//                    'mchid'=>$pay['mch_id'],
//                    'key'=>$pay['apikey'],
//                    'apiclient_cert'=> Env::get("root_path").$pay['cert_file'],
//                    'apiclient_key'=> Env::get("root_path").$pay['key_file'],
//                    'openid'=>$openid,
//                    'body'=>'订单支付',
//                    'out_trade_no'=>$order_sn,
//                    'total_fee'=>$orderMoney * 100,
//                    'notify_url'=>request()->domain().'/index.php/v1/chongzhi_notify',
//                    'spbill_create_ip'=>$request->ip(),
//                ];
//                $paySdk = new \wechat\Jspay($jsApi);
//                $return = $paySdk->getParameters();
//                if($return){
//                    return ServerResponse::message(Code::CODE_SUCCESS, '', $return);
//                }else{
//                    ServerResponse::message(Code::CODE_INTERNAL_ERROR,'订单状态错误');
//                }

            }else{
                ServerResponse::message(Code::CODE_INTERNAL_ERROR,'充值错误');
            }
//        }catch (\Exception $exception){
//            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
//        }
    }

    /**
     * 代理收藏素材列表
     * @param FriendCollection $friendCollection
     * @return \think\response\Json
     */
    public function agentCollect(FriendCollection $friendCollection,Friend $friend){
        try{
            $list = $friendCollection->where(['user_id'=>$this->uid])->paginate(10,false,['query'=>request()->param()])->toArray();
            $data = $list['data'];
            if(!empty($data)){
                foreach ($data as $key=>$value){
                    $info = $friend->where(['id'=>$value['friend_id']])->find();

                    $image = [];
                    if($info['type'] == 1){
                        $image = unserialize($info['image']);
                        if(!empty($image)){
                            foreach ($image as $k=>$v){
                                $image[$key] = request()->domain().str_replace("\\", '/', $v);
                            }
                        }
                    }
                    //判断当前用户是否收藏或者点赞
                    if(FriendLike::where(['user_id'=>$this->uid,'friend_id'=>$info['id']])->count('id')){
                        $info['is_like'] = 1;
                    }else{
                        $info['is_like'] = 2;
                    }
                    $info['is_collection'] = 1;

                    $info['image'] = $image;
                    $info['video'] = request()->domain().str_replace("\\", '/', $info['video']);
                    $info['created_at_str'] = format_datetime($info['timestamp'],1);
                    $data[$key]['friend'] = $info;
                }
            }
            //if(empty($data)) ServerResponse::message(Code::CODE_NO_CONTENT);
            $list['data'] = $data;
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$list]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }

    /**
     * 代理进货申请
     */
    public function agentStockApple(AgentStock $agentStock,AgentStockLog $agentStockLog,AgentStockOrder $agentStockOrder){
        //try{
            Db::startTrans();
            $goods = $this->data['goods'];
            $agent_id = $this->data['agent_id'];
            $type = $this->data['type'] ?? 1;
            $remarks = $this->data['remarks'] ?? '无';
            $arr = [];
            $num = 0;
            $money = 0;
            $goods = explode(',',$goods);
            if(!empty($goods)){
                //获取当前代理等级
                $agent = \app\backman\model\Agent::where('id',$agent_id)->field('level_id')->find();
                if($type == 1){
                    foreach ($goods as $value){
                        $info = explode('_',$value);
                        $goods = Goods::where(['id'=>$info[0]])->field('price,dis_price,level_data')->find();
                        $price = unserialize($goods['level_data']);
                        $arr[] = [
                            'goods_id'=>$info[0],
                            'num'=>$info[1],
                            'user_id'=>$this->uid,
                            'agent_id'=>$agent_id,
                            'price'=>$price[$agent['level_id']],
                            'created_at'=>date('Y-m-d H:i:s'),
                            'updated_at'=>date('Y-m-d H:i:s'),
                        ];
                        $money += $price[$agent['level_id']] * $info[1];
                        $num += $info[1];
                    }
                }else{
                    foreach ($goods as $value){
                        $info = explode('_',$value);
                        $goods = Meal::where(['id'=>$info[0]])->find();
                        $price = unserialize($goods['data']);
                        $arr[] = [
                            'goods_id'=>$info[0],
                            'num'=>$info[1],
                            'user_id'=>$this->uid,
                            'agent_id'=>$agent_id,
                            'price'=>$price[$agent['level_id']],
                            'created_at'=>date('Y-m-d H:i:s'),
                            'updated_at'=>date('Y-m-d H:i:s'),
                        ];
                        $money += $price[$agent['level_id']] * $info[1];
                        $num += $info[1];
                    }
                }
            }
            if($type == 1){

                $order_id = Db::name('agent_stock_order')->insertGetId(['order_sn'=>get_order_sn(),'money'=>$money,'num'=>$num,'user_id'=>$this->uid,'agent_id'=>$agent_id,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s'),'order_type'=>$type,'remarks'=>$remarks]);
            }else{
                if($agent['level_id'] != 1){
                    $level = AgentLevel::where('id',$agent['level_id'])->field('money')->find();
                    if($money < 9800){
                        Db::rollback();
                        return json(['StatusCode'=>50000,'message'=>'代理进货金额不能低于9800元']);
                    }
                }

                $order_id = Db::name('agent_stock_order')->insertGetId(['order_sn'=>get_order_sn(),'money'=>$money,'num'=>$num,'user_id'=>$this->uid,'agent_id'=>$agent_id,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s'),'order_type'=>$type,'remarks'=>$remarks]);
            }

            if(!$order_id){
                Db::rollback();
                ServerResponse::message(Code::CODE_OTHER_FAIL,'订单创建失败');
            }
            if(!empty($arr)){
                foreach ($arr as $key=>$value){
                    $arr[$key]['order_id'] = $order_id;
                    if($type == 2){
                        $arr[$key]['type'] = $type;
                    }
                    if($agent['level_id'] == 1){
                        $arr[$key]['is_settlement'] = 2;
                    }else{
                        $arr[$key]['is_settlement'] = 1;
                    }
                }
            }
            $orderGoodsDataRes = Db::name('agent_stock_log')->insertAll($arr);
            if(!$orderGoodsDataRes){
                Db::rollback();
                ServerResponse::message(Code::CODE_OTHER_FAIL,'订单创建失败');
            }

            Db::commit();
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>['order_id'=>$order_id]]);

//        }catch (\Exception $exception){
//            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
//        }
    }

    /**
     * 代理商品库存记录
     * @param AgentStock $agentStock
     */
    public function agentStockGoods(AgentStock $agentStock){
        //try{
        $type = $this->data['type'];
            $list = $agentStock->where(['user_id'=>$this->uid,'agent_id'=>$this->data['agent_id'],'type'=>$type])->order('num desc')->paginate(10,false,['query'=>request()->param()])->toArray();
            $data = $list['data'];
            if(!empty($data)){
                foreach ($data as $key=>$value){
                    if($type == 1){
                        $goods = Goods::where(['id'=>$value['goods_id']])->field('title,image,dis_price,price,id')->find();
                        $goods['image'] = request()->domain().str_replace("\\", '/', $goods['image']);
                        $data[$key]['goods'] = $goods;
                    }else{
                        $goods = Meal::where(['id'=>$value['goods_id']])->field('name as title')->find();
                        $data[$key]['goods'] = $goods;
                    }
                }
            }
            //if(empty($data)) ServerResponse::message(Code::CODE_NO_CONTENT);
            $list['data'] = $data;
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$list]);
//        }catch (\Exception $exception){
//            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
//        }
    }

    /**
     * 代理进货申请列表
     * @return \think\response\Json
     */
    public function agentStockOrderList(AgentStockOrder $agentStockOrder){
        //try{
            $list = $agentStockOrder->where(['user_id'=>$this->uid,'agent_id'=>$this->data['agent_id']])->order('num desc')->paginate(10,false,['query'=>request()->param()])->toArray();
            $data = $list['data'];
            if(!empty($data)){
                foreach ($data as $key=>$value){
                    $goods = AgentStockLog::where(['id'=>$value['id']])->select();
                    if(!empty($goods)){
                        foreach ($goods as $k=>$v){
                            $goodsInfo = Goods::where(['id'=>$v['goods_id']])->find();
                            $goodsInfo['image'] = request()->domain().str_replace("\\", '/', $goodsInfo['image']);
                            $goods[$k]['goods'] = $goodsInfo;
                        }
                    }
                    $data[$key]['order_goods'] = $goods;
                }
            }
            //if(empty($data)) ServerResponse::message(Code::CODE_NO_CONTENT);
            $list['data'] = $data;
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$list]);
//        }catch (\Exception $exception){
//            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
//        }
    }

    /**
     * 线上付款和线下打款
     */
    public function agentStockPay(AgentStockOrder $agentStockOrder){
        //try{
            Db::startTrans();
            $order_id = $this->data['order_id'];
            $type = $this->data['type'];
            $agent_id = $this->data['agent_id'];
            if($type == 1){
                //线上付款，扣除余额
                $agent = AgentModel::where(['id'=>$agent_id])->field('money')->find();
                $agentStockOrders = AgentStockOrder::where(['id'=>$order_id])->field('money,id,order_type')->find();
                if($agent['money'] < $agentStockOrders['money']){
                    return json(['StatusCode'=>50000,'message'=>'余额不足，请充值']);
                }
                //扣除余额，修改订单状态，增加商品库存
                //扣除代理商余额
                $res = Db::name('agent')->where('id',$agent_id)->update(['money'=>$agent['money'] - $agentStockOrders->money]);
                //$agent->money = $agent->money - $agentStockOrders->money;
                if(!$res){

                    return json(['StatusCode'=>50000,'message'=>'支付失败']);
                }
                $datas = [
                    'agent_id'=>$agent_id,
                    'order_id'=>$agentStockOrders->id,
                    'type'=>1,
                    'created_at'=>date('Y-m-d H:i:s'),
                    'updated_at'=>date('Y-m-d H:i:s'),
                    'money'=>$agentStockOrders->money,
                ];
                Db::name('agent_money_log')->insertGetId($datas);
                if($agentStockOrders['order_type'] == 1){
                    //增加商品库存
                    $order_goods = AgentStockLog::where(['order_id'=>$order_id])->select();
                    foreach ($order_goods as $good){
                        $sInfo = AgentStock::where(['goods_id'=>$good['goods_id'],'agent_id'=>$agent_id,'user_id'=>$this->uid])->find();
                        if($sInfo){
                            //更新商品库存
                            $sInfo->num = $sInfo->num + $good['num'];
                            $res = $sInfo->save();
                        }else{
                            //增加商品库存
                            $res = AgentStock::create(['goods_id'=>$good['goods_id'],'num'=>$good['num'],'agent_id'=>$agent_id,'user_id'=>$this->uid,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:I:s'),'type'=>$agentStockOrders->order_type]);
                        }
                        if(!$res){
                            Db::rollback();
                            return json(['StatusCode'=>50000,'message'=>'余额不足，请充值']);
                        }
                    }
                }
                $status = 1;
                if(!AgentStockOrder::where(['id'=>$order_id])->update(['status'=>$status,'type'=>$type,'pay_at'=>date('Y-m-d H:i:s')])){
                    Db::rollback();
                    return json(['StatusCode'=>50000,'message'=>'请求失败']);
                }

                Db::name('agent_stock_log')->where('order_id',$order_id)->update(['status'=>2]);
            }else{
                //线下付款，后台审核，直接修改订单状态，类型
                $status = 3;
                $small_ticket = $this->data['small_ticket'];
                if(!AgentStockOrder::where(['id'=>$order_id])->update(['status'=>$status,'type'=>$type,'small_ticket'=>$small_ticket])){
                    Db::rollback();
                    return json(['StatusCode'=>50000,'message'=>'请求失败']);
                }
            }
            Db::commit();
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>['type'=>$type]]);
//        }catch (\Exception $exception){
//            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
//        }
    }

    /**
     * 代理提现
     */
    public function withdraw(AgentWithdraw $agentWithdraw){
        try{
            $type = $this->data['type'] ?? 1;
            $this->data['created_at'] = date('Y-m-d H:i:s');
            $this->data['updated_at'] = date('Y-m-d H:i:s');
            $cash = Db::name('config')->where('name','cash')->find();
            //$cash = config('site')['cash'];
            $cash = unserialize($cash['value']);
            if($cash['off'] == '2'){
                return json(['StatusCode'=>50000,'message'=>'提现功能关闭']);
            }

            if($cash['min'] > $this->data['money'] || $cash['max'] < $this->data['money']){
                return json(['StatusCode'=>50000,'message'=>'提现金额只能在'.$cash['min'].'-'.$cash['max']]);
            }
            //判断当日提现最大金额
            $day_max = AgentWithdraw::where(['type'=>$type])->whereTime('created_at','today')->sum('money');
            if($day_max + $this->data['money'] > $cash['day_max']){
                return json(['StatusCode'=>50000,'message'=>'已达当日提现上限']);
            }
            $this->data['rate'] = $cash['rate'];
            $this->data['rel_money'] = $this->data['money'] - round(($this->data['money'] *$cash['rate']),2);
            Db::startTrans();
            if($type == 1){
                //代理提现
                if(AgentModel::where(['id'=>$this->data['agent_id']])->value('money') < $this->data['money']){
                    return json(['StatusCode'=>50000,'message'=>'余额不足']);
                }
                $this->data['user_id'] = $this->uid;
                $this->data['order_sn'] = get_order_sn();
                if(!$agentWithdraw->save($this->data)){
                    Db::rollback();
                    return json(['StatusCode'=>50000,'message'=>'请求失败']);
                }
                //扣除余额
                if(!AgentModel::where(['id'=>$this->data['agent_id']])->setDec('money',$this->data['money'])){
                    Db::rollback();
                    return json(['StatusCode'=>50000,'message'=>'请求失败']);
                }
            }else{
                //普通会员提现
                if(User::where(['uid'=>$this->uid])->value('money') < $this->data['money']){
                    return json(['StatusCode'=>50000,'message'=>'余额不足']);
                }

                $this->data['user_id'] = $this->uid;
                if(!$agentWithdraw->save($this->data)){
                    Db::rollback();
                    return json(['StatusCode'=>50000,'message'=>'请求失败']);
                }
                //扣除余额
                if(!User::where(['uid'=>$this->uid])->setDec('money',$this->data['money'])){
                    Db::rollback();
                    return json(['StatusCode'=>50000,'message'=>'请求失败']);
                }

            }
            Db::commit();
            if($cash['type'] == 1){
                $this->data['id'] = $agentWithdraw->getLastInsID();
                \think\Queue::push('app\api\job\CashPay@fire',$this->data);
            }
            return json(['StatusCode'=>20000,'message'=>'请求成功']);

        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }


    /**
     * 获取代理等级
     * @return \think\response\Json
     */
    public function agentLevel(){
        try{
            $agent_id = $this->data['agent_id'] ?? 0;
            $agent = \app\backman\model\Agent::where(['id'=>$agent_id])->field('level_id')->find();
            if(!empty($agent)){
                if($agent['level_id'] == 1){
                    $list = AgentLevel::where(['parent_id'=>0,'id'=>1])->select();
                }else{
                    $list = AgentLevel::where('parent_id',0)->select();
                }
            }else{
                $list = AgentLevel::where('parent_id',0)->select();
            }

            return json(['StatusCode' => 20000, 'message' => '请求成功', 'data' => $list]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR, '内部服务器错误');
        }
    }

    /**
     * 获取代理用户订单列表
     * @param OrderModel $orderModel
     * @param Goods $goods
     * @param OrderGoods $orderGoods
     * @return \think\response\Json
     */
    public function agentOrder(Order $orderModel,Goods $goods, OrderGoods $orderGoods)
    {
        //try {
            //$where['uid'] = $this->uid;
            $where['is_delete'] = 0;
            $status = $this->data['status'];
            $agent_id = $this->data['agent_id'];
            if ($status != 99) {
                $where['status'] = $this->data['status'];
            }
            if ($agent_id) {
                $where['agent_id'] = $this->data['agent_id'];
            }
            $list = $orderModel->where($where)->field('status,order_sn,id,pay_money,num,uid')->paginate(10, false, ['query' => request()->param()])->toArray();
            $data = $list['data'];
            if (!empty($data)) {
                foreach ($data as $key => $value) {
                    //$data[$key]['goods'] = $goods->where(['id'=>$value['goods_id']])->find()->toArray();
                    $order_goods = $orderGoods->where(['order_id' => $value['id']])->field('number,image,title,price,total_money')->select();
                    if (!empty($order_goods)) {
                        foreach ($order_goods as $k => $v) {
                            $order_goods[$k]['image'] = request()->domain() . str_replace("\\", '/', $v['image']);
                        }
                    }
                    $data[$key]['goods'] = $order_goods;
                    $data[$key]['user'] = User::where(['uid'=>$value['uid']])->field('nickname,headimgurl')->find();

                }
            }
            //if(empty($data)) ServerResponse::message(Code::CODE_NO_CONTENT);
            $list['data'] = $data;
            return json(['StatusCode' => 20000, 'message' => '请求成功', 'data' => $list]);
//        } catch (\Exception $exception) {
//            ServerResponse::message(Code::CODE_INTERNAL_ERROR, '内部服务器错误');
//        }
    }

    /**
     * 代理推荐伙伴列表
     */
    public function agentRecommend(AgentModel $agent){
        //try {
            $parent_id = $this->data['agent_id'];
            $where = [];
            if ($parent_id) {
                $where['agent_parent_id'] = $parent_id;
            }
            $list = $agent->where($where)->field('level_id,name,reg_time,openid')->paginate(10, false, ['query' => request()->param()])->toArray();
            $data = $list['data'];
            if (!empty($data)) {
                foreach ($data as $key => $value) {
                    //$data[$key]['goods'] = $goods->where(['id'=>$value['goods_id']])->find()->toArray();
                    $user = User::where(['openid' => $value['openid']])->find();
                    $data[$key]['headimgurl'] = $user['headimgurl'];
                    $data[$key]['level_name'] = AgentLevel::where(['id'=>$value['level_id']])->value('name');
                }
            }
            //if(empty($data)) ServerResponse::message(Code::CODE_NO_CONTENT);
            $list['data'] = $data;
            return json(['StatusCode' => 20000, 'message' => '请求成功', 'data' => $list]);
//        } catch (\Exception $exception) {
//            ServerResponse::message(Code::CODE_INTERNAL_ERROR, '内部服务器错误');
//        }
    }

    /**
     * 提现列表
     * @return \think\response\Json
     */
    public function withdrawList(AgentWithdraw $agentWithdraw){
        //try {
        $type = $this->data['type'];
        $agent_id = $this->data['agent_id'] ?? '';

        $where['user_id'] = $this->uid;
        if($type){
            $where['type'] = $type;
        }
        if($agent_id){
            $where['agent_id'] = $agent_id;
        }
        $list = $agentWithdraw->where($where)->field('created_at,crad,money,status')->paginate(10, false, ['query' => request()->param()])->toArray();
        $data = $list['data'];
        if(!empty($data)){
            foreach ($data as $key=>$value){
                $data[$key]['crad'] = '**** **** ****'.substr($value['crad'],-4);
            }
        }
        //if(empty($data)) ServerResponse::message(Code::CODE_NO_CONTENT);
        $list['data'] = $data;
        return json(['StatusCode' => 20000, 'message' => '请求成功', 'data' => $list]);
//        } catch (\Exception $exception) {
//            ServerResponse::message(Code::CODE_INTERNAL_ERROR, '内部服务器错误');
//        }
    }

    /**
     * 获取代理授权书
     * @return \think\response\Json
     */
    public function authBook(){
        try {
        $agent_id = $this->data['agent_id'] ?? '';
        $agent = \app\backman\model\Agent::where(['id'=>$agent_id])->field('auth_book,level_id,name,id,phone,weixin,reg_time,status')->find();
        if(!$agent || $agent['status'] != 1){
            return json(['StatusCode' => 50000, 'message' => '暂时不能申请']);
        }
        $returnFile =  '/' . 'uploads/certificate/b_' . $agent['id'] .'_'.$agent['level_id']. '.jpg'; // 生成文件
        if(!file_exists($returnFile)){
            $levelInfo = Db::name('agent_level')->find($agent['level_id']);
            if(!empty($levelInfo['position'])) {
                $position = unserialize($levelInfo['position']);
                $textArr = array();
                // 授权名称
                if (!empty($position['name'])) {
                    if (!empty($position['name']['position'])) {
                        $posArr = explode(',', $position['name']['position']);
                        $textArr['name']['left'] = $posArr[0];
                        $textArr['name']['top'] = $posArr[1] + 20;
                    }
                    $textArr['name']['text'] = $agent['name'];
                    $textArr['name']['fontSize'] = $position['name']['size'];
                    $textArr['name']['fontColor'] = hex2rgba($position['name']['color']);
                    $textArr['name']['fontPath'] = Env::get('root_path'). 'font/font.ttf';
                    $textArr['name']['angle'] = 0;
                    $textArr['name']['center'] = 0;
                }
                // 微信号
                if (!empty($position['card'])) {
                    if (!empty($position['card']['position'])) {
                        $posArr = explode(',', $position['card']['position']);
                        $textArr['card']['left'] = $posArr[0];
                        $textArr['card']['top'] = $posArr[1] + 20;
                    }
                    $textArr['card']['text'] = $agent['weixin'];
                    $textArr['card']['fontSize'] = $position['card']['size'];
                    $textArr['card']['fontColor'] = hex2rgba($position['card']['color']);
                    $textArr['card']['fontPath'] = Env::get('root_path') . 'font/font.ttf';
                    $textArr['card']['angle'] = 0;
                    $textArr['card']['center'] = 0;
                }
                // 手机号码
                if (!empty($position['phone'])) {
                    if (!empty($position['phone']['position'])) {
                        $posArr = explode(',', $position['phone']['position']);
                        $textArr['phone']['left'] = $posArr[0];
                        $textArr['phone']['top'] = $posArr[1] + 20;
                    }
                    $textArr['phone']['text'] = $agent['phone'];
                    $textArr['phone']['fontSize'] = $position['phone']['size'];
                    $textArr['phone']['fontColor'] = hex2rgba($position['phone']['color']);
                    $textArr['phone']['fontPath'] = Env::get('root_path') . 'font/font.ttf';
                    $textArr['phone']['angle'] = 0;
                    $textArr['phone']['center'] = 0;
                }
                // 代理级别
                if (!empty($position['grape'])) {
                    if (!empty($position['grape']['position'])) {
                        $posArr = explode(',', $position['grape']['position']);
                        $textArr['grape']['left'] = $posArr[0];
                        $textArr['grape']['top'] = $posArr[1] + 20;
                    }
                    $textArr['grape']['text'] = $levelInfo['name'];
                    $textArr['grape']['fontSize'] = $position['grape']['size'];
                    $textArr['grape']['fontColor'] = hex2rgba($position['grape']['color']);
                    $textArr['grape']['fontPath'] = Env::get('root_path') . 'font/font.ttf';
                    $textArr['grape']['angle'] = 0;
                    $textArr['grape']['center'] = 0;
                }
                // 授权期限开始时间
                if (!empty($position['start'])) {
                    if (!empty($position['start']['position'])) {
                        $posArr = explode(',', $position['start']['position']);
                        $textArr['start']['left'] = $posArr[0];
                        $textArr['start']['top'] = $posArr[1] + 20;
                    }
                    $textArr['start']['text'] = date('Y年m月d日', strtotime($agent['reg_time']));
                    $textArr['start']['fontSize'] = $position['start']['size'];
                    $textArr['start']['fontColor'] = hex2rgba($position['start']['color']);
                    $textArr['start']['fontPath'] = Env::get('root_path') . 'font/font.ttf';
                    $textArr['start']['angle'] = 0;
                    $textArr['start']['center'] = 0;
                }
                $isImg = Env::get('root_path') . 'public' . str_replace("\\", '/', $levelInfo['auth_book_image']);
                // 头像
                $config = array(
                    'text' => $textArr,
                    'background' => $isImg, // 背景图
                );
                createPoster($config, Env::get('root_path') . 'public'.$returnFile);
            }
        }

        $agent['auth_book'] = request()->domain() . str_replace("\\", '/', $returnFile);
        return json(['StatusCode' => 20000, 'message' => '请求成功', 'data' => $agent]);
        } catch (\Exception $exception) {
            ServerResponse::message(Code::CODE_INTERNAL_ERROR, '内部服务器错误');
        }
    }

    /**
     * 更新代理授权证书
     */
    public function updateAuthBook(){
        try {

        $agent_id = $this->data['agent_id'] ?? '';
        $agent = \app\backman\model\Agent::where(['id'=>$agent_id])->field('auth_book,level_id,name,id,phone,weixin,reg_time,status')->find();
        if(!$agent || $agent['status'] != 1){
            return json(['StatusCode' => 50000, 'message' => '暂时不能申请']);
        }
        $returnFile =  '/' . 'uploads/certificate/b_' . $agent['id'] .'_'.$agent['level_id']. '.jpg'; // 生成文件
            $levelInfo = Db::name('agent_level')->find($agent['level_id']);
            if(!empty($levelInfo['position'])) {
                $position = unserialize($levelInfo['position']);
                $textArr = array();
                // 授权名称
                if (!empty($position['name'])) {
                    if (!empty($position['name']['position'])) {
                        $posArr = explode(',', $position['name']['position']);
                        $textArr['name']['left'] = $posArr[0];
                        $textArr['name']['top'] = $posArr[1] + 20;
                    }
                    $textArr['name']['text'] = $agent['name'];
                    $textArr['name']['fontSize'] = $position['name']['size'];
                    $textArr['name']['fontColor'] = hex2rgba($position['name']['color']);
                    $textArr['name']['fontPath'] = Env::get('root_path'). 'font/font.ttf';
                    $textArr['name']['angle'] = 0;
                    $textArr['name']['center'] = 0;
                }
                // 微信号
                if (!empty($position['card'])) {
                    if (!empty($position['card']['position'])) {
                        $posArr = explode(',', $position['card']['position']);
                        $textArr['card']['left'] = $posArr[0];
                        $textArr['card']['top'] = $posArr[1] + 20;
                    }
                    $textArr['card']['text'] = $agent['weixin'];
                    $textArr['card']['fontSize'] = $position['card']['size'];
                    $textArr['card']['fontColor'] = hex2rgba($position['card']['color']);
                    $textArr['card']['fontPath'] = Env::get('root_path') . 'font/font.ttf';
                    $textArr['card']['angle'] = 0;
                    $textArr['card']['center'] = 0;
                }
                // 手机号码
                if (!empty($position['phone'])) {
                    if (!empty($position['phone']['position'])) {
                        $posArr = explode(',', $position['phone']['position']);
                        $textArr['phone']['left'] = $posArr[0];
                        $textArr['phone']['top'] = $posArr[1] + 20;
                    }
                    $textArr['phone']['text'] = $agent['phone'];
                    $textArr['phone']['fontSize'] = $position['phone']['size'];
                    $textArr['phone']['fontColor'] = hex2rgba($position['phone']['color']);
                    $textArr['phone']['fontPath'] = Env::get('root_path') . 'font/font.ttf';
                    $textArr['phone']['angle'] = 0;
                    $textArr['phone']['center'] = 0;
                }
                // 代理级别
                if (!empty($position['grape'])) {
                    if (!empty($position['grape']['position'])) {
                        $posArr = explode(',', $position['grape']['position']);
                        $textArr['grape']['left'] = $posArr[0];
                        $textArr['grape']['top'] = $posArr[1] + 20;
                    }
                    $textArr['grape']['text'] = $levelInfo['name'];
                    $textArr['grape']['fontSize'] = $position['grape']['size'];
                    $textArr['grape']['fontColor'] = hex2rgba($position['grape']['color']);
                    $textArr['grape']['fontPath'] = Env::get('root_path') . 'font/font.ttf';
                    $textArr['grape']['angle'] = 0;
                    $textArr['grape']['center'] = 0;
                }
                // 授权期限开始时间
                if (!empty($position['start'])) {
                    if (!empty($position['start']['position'])) {
                        $posArr = explode(',', $position['start']['position']);
                        $textArr['start']['left'] = $posArr[0];
                        $textArr['start']['top'] = $posArr[1] + 20;
                    }
                    $textArr['start']['text'] = date('Y年m月d日', strtotime($agent['reg_time']));
                    $textArr['start']['fontSize'] = $position['start']['size'];
                    $textArr['start']['fontColor'] = hex2rgba($position['start']['color']);
                    $textArr['start']['fontPath'] = Env::get('root_path') . 'font/font.ttf';
                    $textArr['start']['angle'] = 0;
                    $textArr['start']['center'] = 0;
                }
                $isImg = Env::get('root_path') . 'public' . str_replace("\\", '/', $levelInfo['auth_book_image']);
                // 头像
                $config = array(
                    'text' => $textArr,
                    'background' => $isImg, // 背景图
                );
                createPoster($config, Env::get('root_path') . 'public'.$returnFile);
            }

        $agent['auth_book'] = request()->domain() . str_replace("\\", '/', $returnFile);
        return json(['StatusCode' => 20000, 'message' => '请求成功', 'data' => $agent]);
        } catch (\Exception $exception) {
            ServerResponse::message(Code::CODE_INTERNAL_ERROR, '内部服务器错误');
        }
    }

    /**
     * 获取代理推荐二维码
     */
    public function agsentQrcode(){
        //try {
        $agent_id = $this->data['id'] ?? '';
        $agent_level = $this->data['agent_level'] ?? '';
        //判断代理二维码是否存在，不存在的重新生成
        $user = Db::name('user')->where(['uid'=>$this->uid])->find();
        $agent = Db::name('agent')->where('id',$agent_id)->find();
        $path = Env::get('root_path') . "public";
        $file = "/uploads/agent/agent_".$agent_id.'_'.$agent_level. ".png";
        if(!file_exists($path.$file)){
            //生成图片
            $file = $this->getShopQrcode2($user,$agent,$agent_level);
        }
        //ServerResponse::message(Code::CODE_INTERNAL_ERROR,'小程序未正式上线！无法获取小程序码');
        $file = request()->domain().str_replace("\\", '/', $file);
        return json(['StatusCode' => 20000, 'message' => '请求成功', 'data' => ['url'=>$file]]);
    }

    /**
     * 获取代理店铺二维码
     */
    public function agentsQrcode(){
        //try {
        $agent_id = $this->data['id'] ?? '';
        $user = Db::name('user')->where(['uid'=>$this->uid])->find();
        $agent = Db::name('agent')->where('openid',$user['openid'])->find();
        //判断代理二维码是否存在，不存在的重新生成
        $path = Env::get('root_path') . "public";
        $file = "/uploads/agent/agent_".$agent['id']. ".png";
        if(!file_exists($path.$file)){
            //生成图片
            $file = $this->getShopQrcode($user,$agent);
        }
        //ServerResponse::message(Code::CODE_INTERNAL_ERROR,'小程序未正式上线！无法获取小程序码');
        $file = request()->domain().str_replace("\\", '/', $file);
        return json(['StatusCode' => 20000, 'message' => '请求成功', 'data' => ['url'=>$file]]);
    }


    /**
     * 生成商品小程序二维码
     */
    protected function getShopQrcode($user,$agent){
        $fiel = Env::get('root_path') . "public/uploads/user/weixin" . ".jpg";
        $postdata['scene']="{$user['uid']}-{$agent['id']}-";
        $postdata['width']=430;
        $postdata['page']='pages/index/index';

        $postdata['auto_color']=false;

        $postdata['line_color']=['r'=>'0','g'=>'0','b'=>'0'];
        $postdata['is_hyaline']=false;
        $post_data = json_encode($postdata);

        $url="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$this->get_token();
        $result=$this->http_post($url,$post_data);
// 保存二维码
        $res = file_put_contents($fiel,$result);
        $path = Env::get('root_path') . 'public/uploads/user/';
        $pathUser = Env::get('root_path') . "public/uploads/user/user_".$user['uid'].'.jpg';
        if(!file_exists($pathUser)){
            //用户头像存在
            $image = $user['headimgurl'];
            $file = $this->download_remote_pic($image,$path,$user['uid']);
            $avatar = $path.$file;
        }else{
            $avatar = $pathUser;
        }

        $avatarYuan = $this->yuanjiao($avatar,Env::get('root_path') . 'public/uploads/user/');
        //生成新的店铺小程序二维码
        if (!empty($avatar)) {
            $imgArr['qrcode']['left'] = 115;
            $imgArr['qrcode']['top'] = 115;
            $imgArr['qrcode']['width'] = "200";
            $imgArr['qrcode']['height'] = "200";
            $imgArr['qrcode']['opacity'] = "100";
            $imgArr['qrcode']['url'] = $avatarYuan;
            $imgArr['qrcode']['stream'] = 0;
            $imgArr['qrcode']['center'] = 0;
        }
        $config = array(
            'text' => [],//文字
            'image' => $imgArr,//图片
            'background' => $fiel, //背景图-》对应的就是小程序二维码
        );
        // 生成文件
        $returnFile = Env::get('root_path') . "public/uploads/agent/agent_" .$agent['id'] . ".png";
        createPoster2($config, $returnFile);
        return "/uploads/agent/agent_" .$agent['id']. ".png";
    }

    /**
     * 生成代理推荐小程序二维码
     */
    protected function getShopQrcode2($user,$agent,$agent_level){
        $fiel = Env::get('root_path') . "public/uploads/user/weixin" . ".jpg";
        $postdata['scene']="{$user['uid']}--{$agent['id']}";
        $postdata['width']=430;
        $postdata['page']='pages/registered/registered';

        $postdata['auto_color']=false;

        $postdata['line_color']=['r'=>'0','g'=>'0','b'=>'0'];
        $postdata['is_hyaline']=false;
        $post_data = json_encode($postdata);

        $url="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$this->get_token();
        $result=$this->http_post($url,$post_data);
// 保存二维码
        $res = file_put_contents($fiel,$result);
        $path = Env::get('root_path') . 'public/uploads/user/';
        $pathUser = Env::get('root_path') . "public/uploads/user/user_".$user['uid'].'.jpg';
        if(!file_exists($pathUser)){
            //用户头像存在
            $image = $user['headimgurl'];
            $file = $this->download_remote_pic($image,$path,$user['uid']);
            $avatar = $path.$file;
        }else{
            $avatar = $pathUser;
        }

        $avatarYuan = $this->yuanjiao($avatar,Env::get('root_path') . 'public/uploads/user/');
        //生成新的店铺小程序二维码
        if (!empty($avatar)) {
            $imgArr['qrcode']['left'] = 115;
            $imgArr['qrcode']['top'] = 115;
            $imgArr['qrcode']['width'] = "200";
            $imgArr['qrcode']['height'] = "200";
            $imgArr['qrcode']['opacity'] = "100";
            $imgArr['qrcode']['url'] = $avatarYuan;
            $imgArr['qrcode']['stream'] = 0;
            $imgArr['qrcode']['center'] = 0;
        }
        $config = array(
            'text' => [],//文字
            'image' => $imgArr,//图片
            'background' => $fiel, //背景图-》对应的就是小程序二维码
        );
        // 生成文件
        $returnFile = Env::get('root_path') . "public/uploads/agent/agent_{$agent['id']}-{$agent_level}.png";
        createPoster2($config, $returnFile);
        return "/uploads/agent/agent_{$agent['id']}-{$agent_level}.png";
    }

    protected function download_remote_pic($url,$path,$uid){
        $header = [
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:45.0) Gecko/20100101 Firefox/45.0',
            'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
            'Accept-Encoding: gzip, deflate',
        ];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $data = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($code == 200) {//把URL格式的图片转成base64_encode格式的！
            $imgBase64Code = "data:image/jpeg;base64," . base64_encode($data);
        }
        $img_content=$imgBase64Code;//图片内容
        //echo $img_content;exit;
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $img_content, $result)) {
            $type = $result[2];//得到图片类型png?jpg?gif?
            $file = 'user_'.$uid.".jpg";
            $new_file = $path.$file;
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $img_content)))) {
                return $file;
            }
        }
    }


    /*
     * 将图片切成圆角
     */
    protected function yuanjiao($imgpath,$path){
        $ext= pathinfo($imgpath);
        $dest_path = $path.uniqid().'.png';
        $src_img = null;
        switch($ext['extension']) {
            case 'jpg':
                $src_img = imagecreatefromjpeg($imgpath);
                break;
            case 'png':
                $src_img = imagecreatefrompng($imgpath);
                break;
        }
        $wh= getimagesize($imgpath);
        $w= $wh[0];
        $h= $wh[1];
        $w= min($w, $h);
        $h=$w;
        $img = imagecreatetruecolor($w, $h);
//这一句一定要有
        imagesavealpha($img, true);
//拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
        imagefill($img, 0, 0, $bg);
        $r = $w / 2; //圆半径
        $y_x = $r; //圆心X坐标
        $y_y = $r; //圆心Y坐标
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($src_img, $x, $y);
                if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                    imagesetpixel($img, $x, $y, $rgbColor);
                }
            }
        }
        imagesavealpha($img, true);
        imagepng($img, $dest_path);
        imagedestroy($img);
        // unlink($url);
        return $dest_path;
    }

    protected function http_post($url, $param, $post_file = false)
    {

        $oCurl = curl_init();

        if (stripos($url, "https://") !== FALSE) {

            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);

            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);

            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1

        }

        if (PHP_VERSION_ID >= 50500 && class_exists('\CURLFile')) {

            $is_curlFile = true;

        } else {

            $is_curlFile = false;

            if (defined('CURLOPT_SAFE_UPLOAD')) {

                curl_setopt($oCurl, CURLOPT_SAFE_UPLOAD, false);

            }

        }

        if (is_string($param)) {

            $strPOST = $param;

        } elseif ($post_file) {

            if ($is_curlFile) {

                foreach ($param as $key => $val) {

                    if (substr($val, 0, 1) == '@') {

                        $param[$key] = new \CURLFile(realpath(substr($val, 1)));

                    }

                }

            }

            $strPOST = $param;

        } else {

            $aPOST = array();

            foreach ($param as $key => $val) {

                $aPOST[] = $key . "=" . urlencode($val);

            }

            $strPOST = join("&", $aPOST);

        }

        curl_setopt($oCurl, CURLOPT_URL, $url);

        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($oCurl, CURLOPT_POST, true);

        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);

        $sContent = curl_exec($oCurl);

        $aStatus = curl_getinfo($oCurl);

        curl_close($oCurl);

        if (intval($aStatus["http_code"]) == 200) {

            return $sContent;

        } else {

            return false;

        }

    }

    // 获取access_token

    protected function get_token()
    {
        $api = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx634330702a3ffecd&secret=abe0791a91e7b3e4b57d5f8f353a71a3";
        $res = curl_get_contents($api);
        $json = json_decode($res, true);
        $access_token = $json['access_token'];
        return $access_token;
    }
}