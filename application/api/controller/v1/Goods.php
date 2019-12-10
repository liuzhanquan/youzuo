<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/8/5
 * Time: 15:40
 */

namespace app\api\controller\v1;
use app\api\controller\Api;
use app\backman\model\Agent;
use app\backman\model\Meal;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\User;
use hg\Code;
use hg\ServerResponse;
use app\common\model\Goods as GoodsModel;
use app\common\model\Comment;
use think\Db;
use think\Request;
class Goods
{
    /**
     * 获取商品列表
     */
    public function index(GoodsModel $GoodsModel){
        try{
            $type = request()->post('type') ?? 1;
            if($type == 3){
                $Meal = new Meal();
                $list = $Meal->order('id asc')->paginate(10,false,['query'=>request()->param()])->toArray();
                $data = $list['data'];
                $agent_id = request()->post('agent_id') ?? 0;
                $agent = Agent::where('id',$agent_id)->field('level_id')->find();
                if(!empty($data)){
                    foreach ($data as $key=>$value){
                        $price = unserialize($value['data']);
                        $data[$key]['price'] = $price[$agent['level_id']];
                    }
                }
                //if(empty($data)) ServerResponse::message(Code::CODE_NO_CONTENT);
                $list['data'] = $data;
                return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$list]);
            }
            $where[] = ['cid','neq',51];
            $where[] = ['status','eq',1];
            $list = $GoodsModel->where($where)->order('sort asc')->paginate(10,false,['query'=>request()->param()])->toArray();
            if($type == 2){
                //代理进货申请的时候，必须传代理id
                $agent_id = request()->post('agent_id');
                $agent = Agent::where('id',$agent_id)->field('level_id')->find();
            }
            $data = $list['data'];
            if(!empty($data)){
                foreach ($data as $key=>$value){
                    $data[$key]['image'] = request()->domain().str_replace("\\", '/', $value['image']);
                    $price = unserialize($value['level_data']);
                    if($type == 2 && $agent){
                        $data[$key]['price'] = $price[$agent['level_id']];
                    }

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
     * 获取最近购买过改商的用户
     */
    public function getLatelyGoods(Request $request){
        //try{
            $list = OrderGoods::where('goods_id',$request->post('goods_id'))->where('id_distribution',2)->group('order_id')->order('created_at','desc')->field('goods_id,order_id')->limit(15)->select();
            if(!empty($list)){
                foreach ($list as $key=>$value){
                    $order = Order::where('id',$value['order_id'])->field('uid,pay_status')->find();
                    if($order['pay_status'] == 2){
                        $list[$key]['user'] = User::where('uid',$order['uid'])->field('nickname,headimgurl')->find();
                    }else{
                        unset($list[$key]);
                    }
                }
            }
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$list]);
//        }catch (\Exception $exception){
//            ServerResponse::message(Code::CODE_INTERNAL_ERROR);
//        }
    }

    /**
     * 获取商品详情
     * @param Request $request
     * @param GoodsModel $GoodsModel
     */
    public function details(Request $request, GoodsModel $GoodsModel){
        //try{
            $list = $GoodsModel->where(['status'=>1,'id'=>$request->post('goods_id')])->find();
            if(!empty($list)){
                $list['image'] = request()->domain().str_replace("\\", '/', $list['image']);
                $list['content'] = str_replace("src=&quot;/uploads/", 'src=&quot;'.request()->domain().'/uploads/', $list['content']);
                $list['photo'] = unserialize($list['photo']);
                $arr = [];
                if(!empty($list['photo'])){
                    foreach ($list['photo'] as $k=> $v){
                        $arr[] = request()->domain().str_replace("\\", '/', $v);
                    }
                }
                $list['photo'] = $arr;
                //获取商品评论
                 $CommentList= Comment::where(['goods_id'=>$request->post('goods_id')])->paginate(10)->toArray();
                $data = $CommentList['data'];
                if(!empty($data)){
                    foreach ($data as $key=>$value){
                        $images = $value['images'] ? explode(',',$value['images']) : [];
                        if(!empty($images)){
                            foreach ($images as $j=>$v){
                                if($v){
                                    $images[$j] = request()->domain().str_replace("\\", '/', $v);
                                }
                            }
                        }
                        $data[$key]['images'] = $images;
                    }
                }
                //if(empty($data)) ServerResponse::message(Code::CODE_NO_CONTENT);
                $list['comment'] = $data;
                //获取正在查看此商品的用户
                $agent_id = request()->post('agent_ids') ?? 0;
                $goods_id = request()->post('goods_id') ?? 0;
                $user = Db::name('goods_watch')->where('agent_id',$agent_id)->where('goods_id',$goods_id)->field('user_id,updated_at,id')->order('updated_at','desc')->select();
                if(!empty($user)){
                    foreach ($user as $k=>$v){
                        $user[$k]['avatar'] = User::where('uid',$v['user_id'])->value('headimgurl');
                    }
                }
                $list['user'] = $user;

            }

            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$list]);
            ServerResponse::message(Code::CODE_SUCCESS,'',$list);
//        }catch (\Exception $exception){
//            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
//        }
    }
}