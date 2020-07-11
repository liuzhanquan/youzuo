<?php

/**
 * @Author: Jason
 * @Date:   2019-12-17 14:09:08
 * @Last Modified by:   Jason
 * @Last Modified time: 2019-12-17 02:25:49
 */

namespace app\common\model;
use \think\Model;
use \think\DB;

class Order extends Model{

	protected $pk = 'id';
    protected $name = 'order';

    /**
     * 客户
     * @param $value
     * @return mixed
     */
    public function getCustomerIdAttr($value)
    {
        $info = Customer::where('id',$value)->value('customer_name');
        return ['text' => $info, 'value' => (int)$value];
    }
	public function getGoodsIdAttr($value)
	{
		$info = Goods::where('id',$value)->value('title');
		return ['text' => $info, 'value' => (int)$value];
	}
    public function getStaffIdAttr( $value ){
    	$info = Staff::where('id',$value)->value('name');
    	return ['text'=> $info, 'value'=>(int)$value];
    }
    public function getRequiredStatusAttr($value)
    {
        
        if( $value ){
            $arr = explode(',',$value);
        }else{
            $arr = [];
        }
        return $arr;
    }
    /**
     * 产品
     * @param $value
     * @return mixed
     */
    public function getGidAttr($value)
    {
        $info = Goods::where('id',$value)->field('title,good_sn')->find();
        return ['text' => $info['title'],'good_sn'=>$info['good_sn'], 'value' => (int)$value];
    }

    /**
     * 检测流程
     * @param $value
     * @return mixed
     */
    public function getDidAttr($value)
    {
        $info = Detection::where('id',$value)->value('name');
        return ['text' => $info, 'value' => (int)$value];
    }

    /**
     * 建单人
     * @param $value
     * @return mixed
     */
    // public function getSidAttr($value)
    // {
    //     if( $this->s_type == 1 ){
    //         $info = '员工:'.Staff::where('id',$value)->value('name');
    //     }else{
    //         $info = '管理员：'.Admin::where('id',$value)->value('username');
    //     }
        
    //     return ['text' => $info, 'value' => $value];
    // }

    /**
     * 数据完成
    **/
    public function saveData($data = [],$type = 'add',$admin = []){
        $data['updated_time'] = date('Y-m-d H:i:s');
        if( empty($data['order_sn']) ){
            $data['order_sn'] = 'UZ'.order_sn_rand().'PH';
        }else{
            $where[] = ['order_sn','eq',$data['order_sn']];
            if( $data['id'] ){
                $where[] = ['id','neq',$data['id']];
            }
            $count = $this->where($where)->count();
            if( $count ){
                $this->error = '单号已存在';
                return false;
            }
        }
        $gsid = $data['gsid'];
        
        if( !empty($data['is_show']) ){
            $data['is_show'] = implode(',',$data['is_show']);
        }
        if( !empty($data['required_status']) ){
            $data['required_status'] = implode(',',$data['required_status']);
        }

        if( gettype($data['gsid']) == 'array' ){
            $data['gsid'] = htmlspecialchars(json_encode( ($data['gsid']) ) );
        }
        
        if($type == 'add'){
            $data['sid'] = $admin['id'];
            $data['s_type'] = 2;
            $data['created_time'] = date('Y-m-d H:i:s');
            
            $id = $state = $this->allowField(true)->save($data);
            
            
        }
        
        if($type == 'edit'){
            $info = $this->where('id',$data['id'])->field('order_sn,did,gsid,status')->find();
            
            if( $info['status'] > 0 ){
                $data['order_sn'] = $info['order_sn'];
                $data['did'] = $info['did']['value'];
                $data['gsid'] = $info['gsid'];
            }
            $state = $this->allowField(true)->save($data,['id'=>$data['id']]);
            $id = $data['id'];
        }
        if($state){
            
            if( !empty( $data['gsid'] ) ){
                $this->order_d_s( $data['gsid'], $this->id );
            }
            return $id;
        }else{
            $this->error = $this->getError() ? $this->getError() : '没有可修改的数据！';
            return false;
        }
        return false;
    }

    /**
     * 数据完成
    **/
    public function saveData1($data = [],$type = 'add',$admin = []){
        
        $data['updated_time'] = date('Y-m-d H:i:s');
        if( empty($data['order_sn']) ){
            $data['order_sn'] = 'UZ'.order_sn_rand().'PH';
        }else{
            $where[] = ['order_sn','eq',$data['order_sn']];
            if( !empty($data['id']) ){
                $where[] = ['id','neq',$data['id']];
            }
            $count = $this->where($where)->count();
            if( $count ){
                $this->error = '单号已存在';
                return false;
            }
        }
        if( !empty($data['is_show']) ){
            $data['is_show'] = implode(',',$data['is_show']);
        }
        if( !empty($data['required_status']) ){
            $data['required_status'] = implode(',',$data['required_status']);
        }
        
        // $gsid = explode(',',$data['gsid']);
        $gsid = $data['gsid'];
        
        $data['gsid'] = htmlspecialchars( json_encode($gsid) );  
        
        if($type == 'add'){
            // $data['sid'] = Staff::where('id',$admin['id'])->value('name');
            $data['sid'] = $admin['id'];
            $data['s_type'] = 1;
            $data['created_time'] = date('Y-m-d H:i:s');
            
            $id = $state = $this->allowField(true)->save($data);
            
        }
        
        if($type == 'edit'){
            $required = $this->where('id',$data['id'])->value('required_status');
            $is_show = $this->where('id',$data['id'])->value('is_show');
           
            
            if( !empty($required) ){
                $show_arr = [];
                $require_arr = explode(',',$required);
                $show_arr = explode(',',$is_show);
                foreach( $data as $key=>$item ){
                    if( in_array($key,$require_arr) ){
                        
                        if( $key == 'composition'  && in_array($key,$show_arr)  ){
                            if( empty($data[$key]) )  return_ajax(40001,'材料成分不能为空');
                        }
                        if( $key == 'supplier'  && in_array($key,$show_arr)  ){
                            if( empty($data[$key]) )  return_ajax(40001,'供应商不能为空');
                        }
                        if( $key == 'machine'  && in_array($key,$show_arr)  ){
                            if( empty($data[$key]) )  return_ajax(40001,'注塑机台不能为空');
                        }
                        if( $key == 'contract_sn'  && in_array($key,$show_arr)  ){
                            if( empty($data[$key]) )  return_ajax(40001,'合同编号、物流编号不能为空');
                        }
                        if( $key == 'remark' ){
                            if( empty($data[$key])  && in_array($key,$show_arr)  )  return_ajax(40001,'备注不能为空');
                        }
                        if( $key == 'test_type'  && in_array($key,$show_arr)  ){
                            if( empty($data[$key]) )  return_ajax(40001,'检测类型不能为空');
                        }
                    }
                }
            }
            $info = $this->where('id',$data['id'])->field('order_sn,did,gsid,status')->find();
            
            if( $info['status'] > 0 ){
                $data['order_sn'] = $info['order_sn'];
                $data['did'] = $info['did']['value'];
                $data['gsid'] = $info['gsid'];
            }
            
            $state = $this->allowField(true)->save($data,['id'=>$data['id']]);
            $id = $data['id'];
        }
        if($state){
            $this->order_d_s( $data['gsid'], $this->id );
            return $id;
        }else{
            $this->error = $this->getError() ? $this->getError() : '没有可修改的数据！';
            return false;
        }
        return false;
    }

    /**
     * author: Jason
     * 二维码生成
     */
    public function create_qrcode($url,$data){

        // if( is_array($data['order_sn']) ){
        //     $data['order_sn'] = implode(',',$data['order_sn']);
        // }
        
        $order = $this->where('order_sn',$data['order_sn'])->field('order_sn,created_time')->find();
        // $order_sn = $this->where('id',$data['id'])->field('order_sn')->find();
        // 单号加密
        // $data['order_sn'] = authcode($data['order_sn']);
        $url = $url.'?order_sn='.$data['order_sn'];
        $res = create_qrcode($url,$order['order_sn'],$order['created_time']);
        return $res;
    }

    /**
     * author: Jason
     * 二维码压缩包
     */
    public function create_qrcode_down($url,$data){

        
        $order = $this->where('order_sn',$data)->field('order_sn,created_time')->find();
        $url = $url.'?order_sn='.$data;
        $res = create_qrcode($url,$order['order_sn'],$order['created_time']);
        return $res;
    }

    /**
     * author: Jason
     * 检测单环节状态数据添加
     */
    public function order_d_s( $gsid, $id ){
        
        $gsid = json_html_decode($gsid);
        $data = [];
        DB::name('order_d_s')->where('oid',$id)->delete();
        foreach( $gsid as $key=>$item ){
            // $count = DB::name('order_d_s')->where(['oid'=>$id,'dsid'=>$item])->count();
            // if(!$count){
                $arr = [];
                $arr['oid'] = $id;
                $arr['dsid'] = $item;
                $data[] = $arr; 
            // }
        }

        DB::name('order_d_s')->insertAll($data);

    }




    /**
     * author : Jason
     * 复制检测单
     */
    public function copyOrder( $data, $admin, $type="2"){
        $OS = new OrderSpec;
        //查询检测单信息
        $info = DB::name('order')->where('id',$data['id'])->find();

        //查询检测单下流程
        $detection = Detection::where('id',$info['did'])->find();
        $gsidS = '';
        $gsid = '';
        if(!empty($info['gsid'])){
            $gsidS = json_decode( html_entity_decode($info['gsid']),true );
            $gsid = implode( ',',json_decode( html_entity_decode($info['gsid']),true ) );
        }
        
        //查询检测单下所有的环节
        $where[] = ['parent_id','=',$info['did']];
        $where[] = ['id','in',$gsid];
        $detection_son = DetectionSon::where($where)->select();

        $d_son_sn = [];
        foreach($detection_son as $item){
            $d_son_sn[] = $item['d_son_sn'];
            
        }
        
        $nArr = [];
        //查询检测单所应用的表格规则
        $specList = DetectionSpec::where('d_son_sn','in',implode(',',$d_son_sn))->select(); 
        
        //统计所有的class名称 name字段
        $specClass = [];
        foreach( $specList as $item ){
            $spec = json_decode($item['spec'], true);
            foreach( $spec as $s ){
                $specClass[] = $s['name'];
            }
            
        }
        //拿到所有的已填写表单信息
        $osWhere[] = ['oid','=',$info['id']];
        $osWhere[] = ['class_name','in',implode(',',$specClass)];
        $orderSpec = OrderSpec::where($osWhere)->select();
        
        $old_id = $info['id'];
        //复制表单信息
        $info['id'] = '';
        $info['order_sn'] = 'UZ'.order_sn_rand().'PH';
        $info['status'] = 0;
        $info['engding'] = 0;
        $info['s_type'] = $type;
        if( $type == 1 ){
            // $name = DB::name('staff')->where('id',$admin['id'])->field('name')->find();
            // $name = DB::name('staff')->where('id',$admin['id'])->field('name')->find();
        }
        if( $type == 2 ){
            // $name = DB::name('admin')->where('id',$admin['id'])->field('username as name')->find();
            // $name = DB::name('admin')->where('id',$admin['id'])->field('username as name')->find();
        }
        $info['sid']  = $admin['id'];
        $info['created_time'] = date('Y-m-d H:i:s');
        $info['updated_time'] = date('Y-m-d H:i:s');
        $res = $this->save($info);
        // 表单信息复制
        if( $res ){
            // $this->copyOrderS($old_id,$this->id);
            // $this->copyOrderDS($old_id,$this->id);
            //循环表单信息，复制
            // foreach( $orderSpec as $key=>$item ){
            //     $orderSpec[$key]['id'] = '';
            //     $orderSpec[$key]['oid'] = $this->id;
            // }
            // $orderSpec = json_decode( json_encode($orderSpec) ,true );
            // $result = Db::name('order_spec')->insertAll($orderSpec);

            // $result = $OS->saveAll($orderSpec);
            return $this->id;
            
        }else{
            $this->error = $this->getError() ? $this->getError() : '复制表格信息错误！';
            return false;
        }
        
    }
    

    public function copyOrderS($old_id,$new_id){
        $list = OrderS::where('oid',$old_id)->select();
        foreach( $list as $key=>$item ){
            $list[$key]['id'] = '';
            $list[$key]['oid'] = $new_id;
        }
        $list = json_decode( json_encode($list) ,true );
        $result = Db::name('order_s')->insertAll($list);
    }
    public function copyOrderDS($old_id,$new_id){
        $list = OrderDS::where('oid',$old_id)->select();
        foreach( $list as $key=>$item ){
            $list[$key]['id'] = '';
            $list[$key]['oid'] = $new_id;
        }
        $list = json_decode( json_encode($list) ,true );
        $result = Db::name('order_d_s')->insertAll($list);
    }



}