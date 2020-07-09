<?php

/**
 * @Author: Azaz；QQ：826355918
 * @Date:   2018-12-11 23:09:08
 * @Last Modified by:   Azaz
 * @Last Modified time: 2019-02-22 02:25:49
 */

namespace app\common\model;
use \think\Model;
use \think\facade\Request;

class Photo extends Model{

	protected $pk = 'id';


    /**
     * 关联
     * @return \think\model\relation\HasMany
     */
    public function rule()
    {
        return $this->hasMany('Category','id','cid');
    }

    /**
     * 获取列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($where = [])
    {
        return $this->with(['rule'])->where($where)
            ->order(['id' => 'asc'])
            ->paginate(48, false, [
                'query' => Request::instance()->request()
            ]);
    }

    /**
     * 数据完成
    **/
    public function saveData($data = [],$type = 'add'){
        if($type == 'add'){
            $save = [];
            $connt = count($data['image']);
            for ($i = 0; $i < $connt; $i++) {

                if( $connt <= 1 ){
                    $name = $data['name'];
                }else{
                    $name = $data['name'].'-'.$i;
                }

                $save[] = [
                    'cid' => $data['cid'],
                    'image' => $data['image'][$i],
                    'name' => $name,
                    
                ];
            }
            $id = $state = $this->allowField(true)->saveAll($save);
        }
        if($type == 'edit'){
            $state = $this->allowField(true)->save($data,['id'=>$data['id']]);
            $id = $data['id'];
        }
        if($state){
            return $id;
        }else{
            $this->error = $this->getError() ? $this->getError() : '没有可修改的数据！';
            return false;
        }
        return false;
    }
    
}