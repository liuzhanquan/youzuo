<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/4/18
 * Time: 21:19
 */

namespace app\api\controller\v1;
use hg\CodeMsg;
use hg\ServerResponse;
use hg\Code;
use think\Db;
use think\Exception;
use app\api\controller\Api;
use think\facade\Cache;

class Address extends Api
{
    /**
     * 获取个人收获地址列表
     */
    public function index(){
        try{
            $Address = Db::name('user_address')->where(['uid'=>$this->uid])->select();
            if(empty($Address)) ServerResponse::message(Code::CODE_NO_CONTENT);
            ServerResponse::message(Code::CODE_SUCCESS,'',$Address);
        }catch (Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }

    /**
     * 获取地址详情
     */
    public function detail(){
        try{
            $addressInfo = Db::name('user_address')->where(['id'=>$this->data['id'],'id'=>$this->data['id']])->find();
            ServerResponse::message(Code::CODE_SUCCESS,'',$addressInfo);
        }catch (Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }

    /**
     * 修改地址详情
     */
    public function update(){
        try{
            $data = $this->data;
            $validate = new \app\api\validate\Address;
            if (!$validate->check($data)) {
                ServerResponse::message(Code::CODE_UNAUTHORIZED, $validate->getError());
            }
            //对应匹配省市区id
            $data = [
                'name'=>$this->data['name'],
                'phone'=>$this->data['phone'],
                'province'=>$this->data['province_name'],
                'city'=>$this->data['city_name'],
                'area'=>$this->data['area_name'],
                'province_id'=>$this->data['province'],
                'city_id'=>$this->data['city'],
                'area_id'=>$this->data['area'],
                'address'=>$this->data['address']
            ];
            $res = db('user_address')->where(['id'=>$this->data['id'],'id'=>$this->data['id']])->update($data);
            if(!$res){
                ServerResponse::message(Code::CODE_INTERNAL_ERROR,CodeMsg::ADDRESS_UPDATE_ERROR);
            }
            ServerResponse::message(Code::CODE_SUCCESS);
        }catch (Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }

    /**
     * 创建收获地址
     */
    public function create(){
        try{
            $data = $this->data;
            $validate = new \app\api\validate\Address;
            if (!$validate->check($data)) {
                ServerResponse::message(Code::CODE_UNAUTHORIZED, $validate->getError());
            }
            $this->data['uid'] = $this->uid;
            //对应匹配省市区id
            $data = [
                'uid'=>$this->uid,
                'address'=>$this->data['address'],
                'is_default'=>0,
                'is_delete'=>0,
                'name'=>$this->data['name'],
                'phone'=>$this->data['phone'],
                'province'=>$this->data['province_name'],
                'city'=>$this->data['city_name'],
                'area'=>$this->data['area_name'],
                'province_id'=>$this->data['province'],
                'city_id'=>$this->data['city'],
                'area_id'=>$this->data['area'],
                'create_time'=>date('Y-m-d H:i:s'),
            ];
            $res = db('user_address')->insert($data);
            if(!$res){
                ServerResponse::message(Code::CODE_INTERNAL_ERROR,CodeMsg::ADDRESS_CREATE_ERROR);
            }
            ServerResponse::message(Code::CODE_SUCCESS);
        }catch (Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }

    /**
     * 删除收货地址
     */
    public function delete(){
        try{
            $res = db('user_address')->where(['uid'=>$this->uid,'id'=>intval($this->data['id'])])->delete();
            if(!$res){
                ServerResponse::message(Code::CODE_INTERNAL_ERROR,CodeMsg::ADDRESS_DELETE_ERROR);
            }
            ServerResponse::message(Code::CODE_SUCCESS);
        }catch (Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }

    /**
     * 选择默认地址
     */
    public function isDefault(){
        try{
            //先将所有记录改为不是默认状态
            Db::startTrans();
            $r = db('user_address')->where(['uid'=>$this->uid])->update(['is_default'=>0]);
//            if(!$r){
//                ServerResponse::message(Code::CODE_INTERNAL_ERROR);
//            }
            $res = db('user_address')->where(['uid'=>$this->uid,'id'=>intval($this->data['id'])])->update(['is_default'=>1]);
            if(!$res){
                Db::rollback();
                ServerResponse::message(Code::CODE_INTERNAL_ERROR,CodeMsg::ADDRESS_DELETE_ERROR);
            }
            Db::commit();
            ServerResponse::message(Code::CODE_SUCCESS);
        }catch (Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }

    /**
     * 虎丘默认收货地址
     */
    public function getDefault(){
        try{
            $address = Db::name('user_address')->where(['uid'=>$this->uid,'is_default'=>1])->find();
            if(!$address) $address = [];
            ServerResponse::message(Code::CODE_SUCCESS,'',$address);
        }catch (Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }

    /**
     * 获取省市区地址
     */
    public function city(){
        try{
            $Address = Cache::get('address_city');
            if(empty($Address)){
                $Address = Db::name('region')->where(['pid'=>0])->field('name,id,pid')->select();
                if(!empty($Address)){
                    foreach ($Address as $key=>$value){
                        $data = Db::name('region')->where(['pid'=>$value['id']])->field('name,id,pid')->select();
                        if(!empty($data)){
                            foreach ($data as $k=>$v){
                                $data[$k]['list'] = Db::name('region')->where(['pid'=>$v['id']])->field('name,id,pid')->select();
                            }
                        }
                        $Address[$key]['list'] =$data;
                    }
                }
                Cache::set('address_city',$Address,86400);
            }
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>$Address]);
            if(empty($Address)) ServerResponse::message(Code::CODE_NO_CONTENT);
            ServerResponse::message(Code::CODE_SUCCESS,'',$Address);
        }catch (Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }
}