<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/9/28
 * Time: 16:50
 */

namespace app\api\command;
use think\Db;
use think\facade\Log;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class AgentGrade extends Command
{
    protected function configure(){
        $this->setName('agentGrade')->setDescription("代理等级结算");
    }

    protected function execute(Input $input, Output $output){
        //代理推荐代理等级结算
        Db::name('log')->insert(['created_at'=>date('Y-m-d H:i:s'),'msg'=>'升级']);
        $data = Db::name('agent')->where(['status'=>1])->field('agent_parent_id,id,status,level_id')->select();
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
//                        $start_date = date('Y-m-d',strtotime($lastLog['created_at']));
//                        $end_date = date('Y-m-d');
                        $start_date = date('Y-m-01 00:00:00',strtotime($lastLog['created_at']));
                        $end_date = date("Y-m-01 00:00:00", strtotime("+{$levelInfo['month']} month",strtotime($start_date)));
                        $num_month = month_numbers($start_date,$end_date);
                        if(time() > strtotime($end_date)){
                            //达到要求，进行结算
                            $agentList = $this->getAgentTeamList($value['id']);
                            $agentList[] = $value['id'];
//                            if(Db::name('agent_stock_log')->where('status',2)->where('goods_id',1)->where('agent_id',$value['id'])->where('type',2)->count() > 0){
//                                $one = Db::name('agent_stock_log')->where('status',2)->where('goods_id',1)->where('agent_id',$value['id'])->where('type',2)->order('created_at asc')->find();
//                            }
                            //$start_time = date("Y-m-01 00:00:00", strtotime("-{$levelInfo['month']} month"));
                            //$ent_time = date("Y-m-01 00:00:00");
                            //统计前N个月的消费情况
                            $where[] = [
                                'status','eq',1,
                                'agent_parent_id','eq',$value['id']
                            ];
                            $money = 0;
                            $moneyList = Db::name('agent_stock_log')->where('is_settlement',1)->where('status',2)->where('agent_id','in',implode(',',$agentList))->where('type',2)->whereBetweenTime('created_at',$start_date,$end_date)->field('price,num,order_id')->select();
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
                        $agentList[] = $value['id'];
                        if(Db::name('agent_stock_log')->where('is_settlement',1)->where('status',2)->where('goods_id',1)->where('agent_id',$value['id'])->where('type',2)->count() > 0){
                            $one = Db::name('agent_stock_log')->where('is_settlement',1)->where('status',2)->where('goods_id',1)->where('agent_id',$value['id'])->where('type',2)->order('created_at asc')->find();
                        }
                        //统计前N个月的消费情况
                        $where[] = [
                            'status','eq',1,
                            'agent_parent_id','eq',$value['id']
                        ];
                        $money = 0;
                        $moneyList = Db::name('agent_stock_log')->where('is_settlement',1)->where('status',2)->where('agent_id','in',implode(',',$agentList))->where('type',2)->where('id','neq',$one['id'] ?? 0)->field('price,num,order_id')->select();
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
        //\think\Queue::push('app\api\job\AgentGrade@fire',$data);
        Db::name('log')->insert(['created_at'=>date('Y-m-d H:i:s'),'msg'=>'升级1']);
        echo 'ok';
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
                    $info = Db::name('agent')->where('id',$value['id'])->find();
                    if($info['level_id'] != 1){
                        $arr[] = $value['id'];
                    }
                    $arr = array_merge($this->getAgentTeamList($value['id']),$arr);
                }
            }
        }
        return $arr;
    }
}