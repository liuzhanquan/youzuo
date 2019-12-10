<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/8/6
 * Time: 10:29
 */

namespace app\api\controller\v1;
use app\common\model\Comment as CommentModel;
use app\api\controller\Api;
use app\common\model\Goods;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\User;
use hg\Code;
use hg\ServerResponse;
use think\Db;

class Comment extends Api
{
    /**
     * 提交评价
     */
    public function create(CommentModel $comment){
        try{
            Db::startTrans();
            $goods_id = $this->data['goods_id'];
            $content = $this->data['content'];
            $images = $this->data['images'];
            $order_id = $this->data['order_goods_id'];
            $orderGoods = OrderGoods::where(['id'=>$order_id])->find();
            if($orderGoods['is_comment'] == 2){
                return json(['StatusCode'=>50000,'message'=>'该订单商品已经评论']);
            }
            if(!$goods_id || !$content || !$order_id){
                ServerResponse::message(Code::CODE_INTERNAL_ERROR,'参数错误');
            }
            //处理评论
            $this->data['user_id'] = $this->uid;
            $this->data['created_at'] = date('Y-m-d H:i:s');
            $this->data['updated_at'] = date('Y-m-d H:i:s');
            if(!$images){
                unset($this->data['images']);
            }

            $res = $comment->allowField(true)->save($this->data);
            if(!$res){
                Db::rollback();
                return json(['StatusCode'=>50000,'message'=>'评论错误']);
            }
            //更新订单商品评论状态
            $orderGoods->is_comment = 2;
            if(!$orderGoods->save()){
                Db::rollback();
                return json(['StatusCode'=>50000,'message'=>'评论错误']);
            }
            Db::commit();
            return json(['StatusCode'=>20000,'message'=>'请求成功']);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR);
        }
    }

    /**
     * 商品评论列表
     * @return \think\response\Json
     */
    public function index(Goods $goods,Order $order,OrderGoods $orderGoods,\app\common\model\Comment $comment){
        try{
            $where['goods_id'] = $this->data['goods_id'];
            $where['status'] = 1;
            $list = $comment->where($where)->paginate(10,false,['query'=>request()->param()])->toArray();
            $data = $list['data'];
            if(!empty($data)){
                foreach ($data as $key=>$value){
                    //处理评论图片，用户信息
                    $images = $value['images'] ? explode(',',$value['images']) : [];
                    if(!empty($images)){
                        foreach ($images as $k=>$v){
                            if($v && $v != ''){
                                $images[$k] = request()->domain().str_replace("\\", '/', $v);
                            }
                        }
                    }
                    $data[$key]['images'] = $images;
                    $user = User::where(['uid'=>$value['user_id']])->field('nickname,headimgurl')->find();
                    $data[$key]['user'] = $user;
                }
            }
            //if(empty($data)) ServerResponse::message(Code::CODE_NO_CONTENT);
            $list['data'] = $data;
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$list]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,'内部服务器错误');
        }
    }
}