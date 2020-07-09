<?php

/**
 * @Author: Jason
 * @Date:   2018-12-11 23:09:08
 * @Last Modified by:   Jason
 * @Last Modified time: 2019-02-22 02:25:49
 */

namespace app\common\model;
use \think\Model;
use \think\DB;

class OrderSpec extends Model{

	protected $pk = 'id';
    protected $name = 'order_spec';

    /**
     * 分类
     * @param $value
     * @return mixed
     */
    public function getCidAttr($value)
    {
        $info = CusCategory::where('id',$value)->value('name');
        return ['text' => $info, 'value' => $value];
    }
    /**
     * 数据完成
    **/
    public function saveData($data = [],$admin = []){
        $info = DetectionSpec::where('d_son_sn',$data['d_son_sn'])->find();
        $type = DetectionSon::where('id',$data['dsid'])->field('type')->find();
        $list = json_decode($info['spec'],true);
        
        
        $newData = [];
        
        foreach( $list as $key=>$item ){
            if( !empty($item['must']) && empty($data[$item['name']]) ){
                $this->error = $item['title'].'不能为空';
                return false;
            }

            if( empty($data[$item['name']]) ){
                $data[$item['name']] = '';
            }

            if( gettype($data[$item['name']]) == 'array'){
                $data[$item['name']] = json_encode($data[$item['name']]);
            }
            
            

            $newData[$key]['oid']       = $data['oid'];
            $newData[$key]['did']       = $data['did'];
            $newData[$key]['dsid']      = $data['dsid'];
            $newData[$key]['d_son_sn']  = $data['d_son_sn'];
            $newData[$key]['class_name'] = $item['name'];
            $newData[$key]['content']   = $data[$item['name']];
            $newData[$key]['updated_time']= date('Y-m-d H:i:s');
            
        }
        $save = [];

        if( $data['sort'] == -1 ){
            
            $osdata['oid']  = $data['oid'];
            $osdata['did']  = $data['did'];
            $osdata['dsid'] = $data['dsid'];
            $osdata['d_son_sn'] = $data['d_son_sn'];
            $osdata['sort'] = OrderS::where(['oid'=>$data['oid'],'dsid'=>$data['dsid']])->count();
            
            if( !empty($data['os_created_time']) ){
                $osdata['created_time'] = $data['os_created_time'];
            }else{
                $osdata['created_time'] = date('Y-m-d H:i:s');
            }
            //管理员id
            $osdata['aid'] = $admin['id'];
            $osdata['s_type'] = 2;
            $osdata['status'] = 0;

            $osdata['updated_time'] = date('Y-m-d H:i:s');
            DB::name('order_s')->insert($osdata);
            
            // 查询订单状态， 第二个参数添加， 修改订单状态
            $OSCres = orderStatusCheck($data['oid'], 1);

            $count = 0;
        }else{
            $count = 1;
            OrderS::where(['oid'=>$data['oid'],'dsid'=>$data['dsid'],'sort'=>$data['sort']])->update(['updated_time'=>date('Y-m-d H:i:s'),'created_time'=>$data['os_created_time']]);
        }
        
        foreach( $newData as $key=>$item ){
            
            //$specCount = $this->where(['oid'=>$item['oid'],'class_name'=>$item['class_name'],'sort'=>$data['sort']])->count();
            
            //判断检测单里面是否添加了表单列数据记录，没有新增，有就更新数据
            if( $count ){
                $res = $this->where(['oid'=>$item['oid'],'class_name'=>$item['class_name'],'sort'=>$data['sort']])->update($item);
                if( $res == 0 ){
                    $item['created_time']= date('Y-m-d H:i:s');
                    $item['sort'] = $data['sort'];
                    $save[] = $item;
                }
            }else{
                $item['created_time']= date('Y-m-d H:i:s');
                $item['sort'] = $osdata['sort'];
                $save[] = $item;
            }
        }
        if( !empty($save) ){

            
            
            $res = $this->saveAll($save);
        }
        if( $this->getError() ){
            $this->error = $this->getError() ? $this->getError() : '没有可修改的数据！';
            return false;
        } else {
            return true;
        }
        
        return false;
    }

    /**
     * 数据完成
    **/
    public function saveData1($data = [],$user = []){
        $dinfo = DetectionSon::where('id',$data['dsid'])->field('d_son_sn')->find();
        
        $data['d_son_sn'] = $dinfo['d_son_sn'];
        $info = DetectionSpec::where('d_son_sn',$data['d_son_sn'])->find();
        $type = DetectionSon::where('id',$data['dsid'])->field('type,time_status')->find();
        $list = json_decode($info['spec'],true);
        
        
        $newData = [];
        
        foreach( $list as $key=>$item ){
            if( !empty($item['must']) && empty($data[$item['name']]  ) && $data['sort'] == -1 ){
                $this->error = $item['title'].'不能为空';
                return false;
            }
            if ( $data['sort'] != -1 && in_array( $item['name'], $data['modify'] ) && !empty($item['must']) && empty($data[$item['name']]  )  )  {
                    $this->error = $item['title'].'不能为空';
                    return false;
            }

            if( empty($data[$item['name']]) ){
                $data[$item['name']] = '';
            }

            if( gettype($data[$item['name']]) == 'array'){
                $data[$item['name']] = json_encode($data[$item['name']]);
            }
            
            if( $data['sort'] == -1 || in_array( $item['name'], $data['modify'] ) ){
                
                $newData[$key]['oid']       = $data['oid'];
                $newData[$key]['did']       = $data['did'];
                $newData[$key]['dsid']      = $data['dsid'];
                $newData[$key]['d_son_sn']  = $data['d_son_sn'];
                $newData[$key]['class_name'] = $item['name'];
                

                if( $item['type'] == 'picker' ){
                    $picker = json_decode($data[$item['name']],true);
                    
                    $pickerS['province'] = $picker[0];  
                    $pickerS['city'] = $picker[1];  
                    $pickerS['county'] = $picker[2]; 
                    $newData[$key]['content'] = htmlspecialchars( json_encode($pickerS) );
                }else if( $item['type'] == 'textarea' ){
                    $newData[$key]['content']   =  contentremotepath( $data[$item['name']] );
                }else{
                    $newData[$key]['content']   = $data[$item['name']];
                }
                
                $newData[$key]['updated_time']= date('Y-m-d H:i:s');
            }
            
            
        }
        $save = [];
        if( $data['sort'] == -1 ){
            
            $osdata['oid']  = $data['oid'];
            $osdata['did']  = $data['did'];
            $osdata['dsid'] = $data['dsid'];
            $osdata['d_son_sn'] = $data['d_son_sn'];
            $osdata['sort'] = OrderS::where(['oid'=>$data['oid'],'dsid'=>$data['dsid']])->count();

            if( $type['time_status'] && !empty($data['created_time']) ){
                
                $osdata['created_time'] = $data['created_time'];
            }else{
                $osdata['created_time'] = date('Y-m-d H:i:s');
            }
            //用户id
            $osdata['sid'] = $user['id'];
            $osdata['s_type'] = 1;
            $osdata['status'] = 0;
            $osdata['updated_time'] = date('Y-m-d H:i:s');
            DB::name('order_s')->insert($osdata);
            
            // 查询订单状态， 第二个参数添加， 修改订单状态
            $OSCres = orderStatusCheck($data['oid'], 1);

            $count = 0;
        }else{
            $count = 1;
            OrderS::where(['oid'=>$data['oid'],'dsid'=>$data['dsid'],'sort'=>$data['sort']])->update(['updated_time'=>date('Y-m-d H:i:s')]);
        }
        
        
        foreach( $newData as $key=>$item ){
            
            //$specCount = $this->where(['oid'=>$item['oid'],'class_name'=>$item['class_name'],'sort'=>$data['sort']])->count();
            
            //判断检测单里面是否添加了表单列数据记录，没有新增，有就更新数据
            if( $count ){
                $res = $this->where(['oid'=>$item['oid'],'class_name'=>$item['class_name'],'sort'=>$data['sort']])->update($item);
                if( $res == 0 ){
                    $item['created_time']= date('Y-m-d H:i:s');
                    $item['sort'] = $data['sort'];
                    $save[] = $item;
                }
            }else{
                $item['created_time']= date('Y-m-d H:i:s');
                $item['sort'] = $osdata['sort'];
                $save[] = $item;
            }
        }
        if( !empty($save) ){

            
            
            $res = $this->saveAll($save);
        }
        if( $this->getError() ){
            $this->error = $this->getError() ? $this->getError() : '没有可修改的数据！';
            return false;
        } else {
            unlinkPhoto($data['del']);
            return true;
        }
        
        return false;
    }



}