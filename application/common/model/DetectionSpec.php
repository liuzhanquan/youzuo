<?php

/**
 * @Author: Jason
 * @Date:   2018-12-11 23:09:08
 * @Last Modified by:   Jason
 * @Last Modified time: 2019-02-22 02:25:49
 */

namespace app\common\model;
use \think\Model;
use think\Db;

class DetectionSpec extends Model{

	protected $pk = 'id';
    protected $name = 'detection_spec';

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
    public function saveData($data = [],$type = 'add'){
        if( empty($data['must']) ){
            $data['must'] = [];
        }
        $table = tablePreg($data['text'],$data['must']);
        
        if( empty($table) ){
            $this->error = '自定义表格不能为空';
            return false;
        }
        // 关联数据字典方法
        $this->specdataAdd($table,$data);
        $ndata['d_son_sn'] = $data['d_son_sn'];
        //$ndata['htmltext'] = $data['text'];
        $ndata['spec'] = json_encode($table);

        if($type == 'add'){
            $data['created_time'] = date('Y-m-d H:i:s');
            $data['updated_time'] = date('Y-m-d H:i:s');
            if ( empty($data['d_son_sn']) ) { 
                $data['d_son_sn'] = get_order_sn();
            }
            $id = $state = $this->allowField(true)->save($ndata);
        }
        if($type == 'edit'){
            $data['updated_time'] = date('Y-m-d H:i:s');
            $state = $this->allowField(true)->save($ndata,['d_son_sn'=>$data['d_son_sn']]);
            $id = $data['d_son_sn'];
        }
        if($state){
            return $id;
        }else{
            $this->error = $this->getError() ? $this->getError() : '没有可修改的数据！';
            return false;
        }
        return false;
    }

    // 关联数据字典方法
    public function specdataAdd($table, $data){
       
        $num = 0;
        $ndata = [];
        DB::name('spec_data')->where('d_son_sn',$data['d_son_sn'])->delete();
        foreach( $table as $item ){
            $arr = [];
            if( $item['type'] == 'radio' ){
                $arr['did'] = $data['datacate'][$num];
                // type = 1 数据字典  0 其他表格
                $arr['type'] = 1;
                $arr['cid']  = 0;
                $arr['table_name'] = '';
            }

            if( $item['type'] == 'checkbox' ){
                $arr['did'] = $data['datacate'][$num];
                // type = 1 数据字典  0 其他表格
                $arr['type'] = 1;
                $arr['cid']  = 0;
                $arr['table_name'] = '';
            }

            if( $item['type'] == 'select' ){
                $arr['did'] = $data['datacate'][$num];
                // type = 1 数据字典  0 其他表格
                $arr['type'] = $data['type'][$num];
                $arr['cid']  = $data['cid'][$num];
                if( $data['type'][$num] == 'datacate' ){
                    $arr['type'] = 1;
                    $arr['table_name'] = 'data_category';

                }else{
                    $arr['type'] = 0;
                    $arr['table_name'] = $data['type'][$num];
                }
            }
            if( $arr ){
                $arr['d_son_sn'] = $data['d_son_sn'];
                $arr['class_name'] = $item['name'];
                $ndata[] = $arr;
            }
            $num++;

        }
        
        
        DB::name('spec_data')->insertAll($ndata);

    }




}