<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/8/5
 * Time: 16:39
 */

namespace app\api\controller\v1;
use app\common\model\Cart as CartModel;
use app\api\controller\Api;
use hg\Code;
use hg\ServerResponse;
use app\common\model\Goods;

class Cart extends Api
{
    /**
     * 获取用户购物车列表
     * @param CartModel $cartModel
     */
    public function index(CartModel $cartModel,Goods $goods){
        try{
            $list = $cartModel->where(['user_id'=>$this->uid])->paginate(10,false,['query'=>request()->param()])->toArray();
            $data = $list['data'];
            if(!empty($data)){
                foreach ($data as $key=>$value){
                    $goods = $goods->where(['id'=>$value['goods_id']])->find();
                    $goods['image'] = request()->domain().str_replace("\\", '/', $goods['image']);
                    $data[$key]['goods'] =$goods;
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
     * 加入购物车
     */
    public function create(CartModel $cartModel,Goods $goods){
        try{
            $cart = $cartModel->where(['goods_id'=>$this->data['goods_id'],'user_id'=>$this->uid])->find();
            if($cart){

                //更新
                $data = [
                    'num' =>  $cart->num + $this->data['num'],
                ];
                if($cartModel->save($data,['goods_id'=>$this->data['goods_id'],'user_id'=>$this->uid])){
                    //创建成功
                    return json(['StatusCode'=>20000,'message'=>'请求成功']);
                }
            }else{
                $data = [
                    'user_id'  =>  $this->uid,
                    'goods_id' =>  $this->data['goods_id'],
                    'num' =>  $this->data['num'],
                    'updated_at' =>  date('Y-m-d H:i:s'),
                    'created_at' =>  date('Y-m-d H:i:s'),
                ];
                if($cartModel->save($data)){
                    //创建成功
                    return json(['StatusCode'=>20000,'message'=>'请求成功']);
                }
            }
            ServerResponse::message(Code::CODE_OTHER_FAIL,'请求失败');
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }

    /**
     * 增加减购物车数量
     */
    public function updateNum(CartModel $cartModel,Goods $goods){
        try{
            $cart = $cartModel->where(['id'=>$this->data['cart_id']])->find();
            if($this->data['type'] == 1){
                //增加
                $num = $cart->num + $this->data['num'];
            }else{
                //减少
                $num = $cart->num - $this->data['num'];
            }
            if($num){
                //更新数据
                $cart->num = $num;
                if(!$cart->save()){
                    ServerResponse::message(Code::CODE_OTHER_FAIL,'更新失败');
                }

            }else{
                //直接删除该条数据
                $res = CartModel::destroy($this->data['cart_id']);
                if(!$res){
                    ServerResponse::message(Code::CODE_OTHER_FAIL,'更新失败');
                }
            }
            return json(['StatusCode'=>20000,'message'=>'请求成功']);

        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }

    /**
     * 删除购物车
     */
    public function delete(CartModel $cartModel){
        try{
            $cartId = explode(',',$this->data['cart_id']);
            if(!$cartId){
                ServerResponse::message(Code::CODE_OTHER_FAIL,'更新失败');
            }
//            if(!$cartModel->where(['user_id'=>$this->uid,'cart_id'=>$this->data['cart_id']])->find()){
//                ServerResponse::message(Code::CODE_OTHER_FAIL,'非法操作');
//            }
            //直接删除该条数据
            $res = CartModel::destroy($this->data['cart_id']);
            if(!$res){
                ServerResponse::message(Code::CODE_OTHER_FAIL,'更新失败');
            }
            return json(['StatusCode'=>20000,'message'=>'请求成功']);

        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }
}