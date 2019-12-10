<?php
namespace app\index\controller;
use app\common\exception\BaseException;
use Intervention\Image\ImageManager;
use Intervention\Image\ImageManagerStatic as Image;
use think\facade\Env;
use think\Db;

class Index extends Api
{
    public function index()
    {
        $data = Db::name('agent')->where(['status'=>1])->field('agent_parent_id,id,status,level_id')->select();
        //执行计算
        if(!empty($data)){
            foreach ($data as $key=>$value){
                //获取当前用户的用户等级信息，只有联创才计算升级，
                $is_log = false;
                if($value['level_id'] != 1 && $value['level_id'] < 10){
                    $levelInfo = Db::name('agent_level')->where(['id'=>$value['level_id']])->field('money,people,month,name,id')->find();
                    //获取上一次结算记录，上一次结算和当前必须是相隔到设置的结算周期
                    $lastLog = Db::name('agent_settlement')->where('agent_id',$value['id'])->where('type',2)->field('created_at')->order('created_at desc')->find();
                    if($lastLog){
                        //存在上一次结算记录，判断当前是否达到结算时间
                        $month = $levelInfo['month'];//结算周期
                        $start_date = date('Y-m-d',strtotime($lastLog['created_at']));
                        $end_date = date('Y-m-d');
                        $num_month = month_numbers($start_date,$end_date);
                        if($month < $num_month){
                            //达到要求，进行结算
                            $agentList = $this->getAgentTeamList($value['id']);
                            $start_time = date("Y-m-01 00:00:00", strtotime("-{$levelInfo['month']} month"));
                            $ent_time = date("Y-m-01 00:00:00");
                            //统计前N个月的消费情况
                            $where[] = [
                                'status','eq',1,
                                'agent_parent_id','eq',$value['id']
                            ];
                            $money = 0;
                            $moneyList = Db::name('agent_stock_log')->where('agent_id','in',implode(',',$agentList))->whereBetweenTime('created_at',$start_time,$ent_time)->field('price,num,order_id')->select();
                            if(!empty($moneyList)){
                                foreach ($moneyList as $kk=>$vv){
                                    $money += $vv['num']*$vv['price'];
                                }
                            }
                            //获取用户达到的会员等级
                            $levelInfoUpdate = Db::name('agent_level')->where('money','<=',$money)->order('money desc')->find();
                            if($money >= $levelInfoUpdate['money'] && $levelInfoUpdate['id'] > $levelInfo['id']){
                                //达到升级条件
                                Db::name('agent')->where(['id'=>$value['id']])->update(['level_id'=>$levelInfoUpdate['id']]);
                                //增加会员升级记录
                                Db::name('agent_level_log')->insertGetId(['agent_id'=>$value['id'],'user_id'=>0,'msg'=>'升级到'.$levelInfoUpdate['name'],'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
                            }
                            $is_log = true;
                        }
                    }else{
                        //不存在上一次记录，直接判断是否足够条件升级
                        $month = $levelInfo['month'];//结算周期
                        $start_date = date('Y-m-d',strtotime($lastLog['created_at']));
                        $end_date = date('Y-m-d');
                        $num_month = month_numbers($start_date,$end_date);
                        //if($month < $num_month){
                            //达到要求，进行结算
                            $agentList = $this->getAgentTeamList($value['id']);
                            //统计前N个月的消费情况
                            $where[] = [
                                'status','eq',1,
                                'agent_parent_id','eq',$value['id']
                            ];
                            $money = 0;
                            $moneyList = Db::name('agent_stock_log')->where('agent_id','in',implode(',',$agentList))->field('price,num,order_id')->select();
                            if(!empty($moneyList)){
                                foreach ($moneyList as $kk=>$vv){
                                    $money += $vv['num']*$vv['price'];
                                }
                            }
                        //获取用户达到的会员等级
                        $levelInfoUpdate = Db::name('agent_level')->where('money','<=',$money)->order('money desc')->find();
                        if($money >= $levelInfoUpdate['money'] && $levelInfoUpdate['id'] > $levelInfo['id']){
                                //达到升级条件
                                Db::name('agent')->where(['id'=>$value['id']])->update(['level_id'=>$levelInfoUpdate['id']]);
                                //增加会员升级记录
                                Db::name('agent_level_log')->insertGetId(['agent_id'=>$value['id'],'user_id'=>0,'msg'=>'升级到'.$levelInfoUpdate['name'],'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
                            }
                        //}
                        $is_log = true;
                    }
                }

                //增加结算记录
                if($is_log){
                    Db::name('agent_settlement')->insert(['agent_id'=>$value['id'],'money'=>0,'type'=>2,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
                }

            }
        }
















// create an image manager instance with favored driver
        //Image::configure(array('driver' => 'gd'));

// 最后创建 image 实例
        //$image = Image::make(Env::get('root_path').'public/uploads/user/IMG_0277.JPG')->resize(300, 200);



// 修改指定图片的大小
        //$img = Image::make(Env::get('root_path').'public/uploads/user/IMG_0277.JPG')->resize(200, 200);

// 插入水印, 水印位置在原图片的右下角, 距离下边距 10 像素, 距离右边距 15 像素
       // $img->insert(Env::get('root_path').'public/uploads/user/5d53e04ab35b3.png', 'bottom-right', 15, 10);

// 将处理后的图片重新保存到其他路径
        //$img->save(Env::get('root_path').'public/uploads/user/weixin2.jpg');



//        $manager = new ImageManager(array('driver' => 'gd'));
//
//// to finally create image instances
//        $manager->make(Env::get('root_path').'public/uploads/user/IMG_0277.JPG')->resize(200, 200)->insert(Env::get('root_path').'public/uploads/user/weixin.jpg', 'bottom-right', 15, 10);
//        //$image = $manager->make(Env::get('root_path').'public/uploads/user/5d53e04ab35b3.png');
//
//        $a = $manager->save(Env::get('root_path').'public/uploads/user/weixin2.jpg');
//// apply stronger blur
//        //$a = $image->colorize(-100, 0, 100);;
//        dump($a);die;
    	//throw new BaseException(['msg' => $image]);die;
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
                    $arr[] = $value['id'];
                    $arr = array_merge($this->getAgentTeamList($value['id']),$arr);
                }
            }
        }
        return $arr;
    }

    /**
     * 获取当前代理得实际所得金额
     * @param $agent_id integer 代理id
     * @param $money integer 总业绩
     * @param $date array 结算开始时间,结束时间
     * @return integer 返回代理最后所得金额
     */
    protected function getAgentTeamListMoney($agent_id,$money = 0,$date = []){
        dump($money.$agent_id);
        if($agent_id){
            $list = Db::name('agent')->where('agent_parent_id',$agent_id)->field('id')->select();
            if(!empty($list)){
                foreach ($list as $key=>$value){
                    //计算当前用户的返佣金额，首单不算
                    if(!empty($date)){
                        $moneyList = Db::name('agent_stock_log')->where('agent_id',$value['id'])->where('goods_id',1)->whereBetweenTime('created_at',$date['start'],$date['end'])->select();
                    }else{
                        $moneyList = Db::name('agent_stock_log')->where('agent_id',$value['id'])->where('goods_id',1)->select();
                    }

                    $money2 = 0;
                    if(count($moneyList) > 1){
                        foreach ($moneyList as $k=>$v){
                            if($k > 0){
                                $money2 += $v['num'] + $v['price'];
                            }
                        }
                    }
                    if($money2 > 0){
                        //计算当前用户可以获得的佣金
                        $level = Db::name('agent_level')->where('id','>',1)->where('money','<=',$money2)->order('money desc')->find();
                        $money3 = 0;
                        if($level){
                            //超过当前计算的佣金，可获得超出部分的一半奖金
                            if($money2%19600 == 0){
                                $num = $money2/19600;
                                $money3 = ($level['reward'] * $num);
                            }else{
                                $num = floor($money2/19600);
                                $money3 = ($level['reward'] * $num) + ($level['reward']/2);
                            }
                        }
                        if($money3 > 0){
                            //给代理增加余额
                            Db::name('agent')->where('id',$value['id'])->setDec('money',$money3);
                            //增加当前会员的分销记录
                            $dataArr = [
                                'agent_id'=>$value['id'],
                                'user_id'=>0,
                                'created_at'=>date('Y-m-d H:i:s'),
                                'updated_at'=>date('Y-m-d H:i:s'),
                                'money'=>$money3,
                                'tuser_id'=>0,
                                'order_id'=>0,
                                'type'=>2,
                            ];
                            Db::name('user_commission')->insertGetId($dataArr);
                            unset($dataArr);
                        }

                        $money = $money - $money3;
                    }
                    $money = $money - $this->getAgentTeamListMoney($value['id'],$money,$date);
                }
            }
        }
        return $money;
    }


    public function index2(){
    	throw new BaseException(['msg' => '缺少必要的参数：wxapp_id']);
    }
}
