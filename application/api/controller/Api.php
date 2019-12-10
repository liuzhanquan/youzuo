<?php
namespace app\api\controller;

use app\common\model\Goods;
use think\Controller;
use think\Request;
use app\api\controller\Send;
use app\api\controller\Oauth;
use hg\ServerResponse;
use hg\Code;
use think\Db;

/**
 * api 入口文件基类，需要控制权限的控制器都应该继承该类
 */
class Api
{	
	use Send;
	/**
     * @var \think\Request Request实例
     */
    protected $request;

    protected $clientInfo;

    /**
     * 不需要鉴权方法
     */
    protected $noAuth = [];

	/**
	 * 构造方法
	 * @param Request $request Request对象
	 */
	public function __construct(Request $request)
	{
		$this->request = $request;
		$this->init();
		$this->uid = $this->clientInfo['uid'];
		$this->data = $request->param();
		//$this->openid = $this->clientInfo['openid'];

	}

	/**
	 * 初始化
	 * 检查请求类型，数据格式等
	 */
	public function init()
	{	
		//所有ajax请求的options预请求都会直接返回200，如果需要单独针对某个类中的方法，可以在路由规则中进行配置
		if($this->request->isOptions()){

			return ServerResponse::message(Code::CODE_BAD_REQUEST);
		}
		if(!Oauth::match($this->noAuth)){
			$oauth = app('app\api\controller\Oauth');   //tp5.1容器，直接绑定类到容器进行实例化
    		return $this->clientInfo = $oauth->authenticate();
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
                                    }else{
                                        $one_money = 0;
                                    }
                                }


                            $oneMoney = $one_money;
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
                                        }else{
                                            $two_money = 0;
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
  


	/**
	 * 空方法
	 */
	public function _empty()
    {
        return ServerResponse::message(Code::CODE_BAD_REQUEST);
    }
}