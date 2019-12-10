<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/9/28
 * Time: 16:51
 */

namespace app\api\job;
use think\Db;
use think\facade\Log;
use think\queue\Job;

class AgentGrade
{
    /**
     * fire方法是消息队列默认调用的方法
     * @param Job            $job      当前的任务对象
     * @param array|mixed    $data     发布任务时自定义的数据
     */
    public function fire(Job $job,$data)
    {
        Db::name('log')->insert(['created_at'=>date('Y-m-d H:i:s'),'msg'=>'升级2']);
        $isJobDone = $this->doHelloJob($data);
        if ($isJobDone) {
            // 如果任务执行成功， 记得删除任务
            $job->delete();
            print("<info>Hello Job has been done and deleted" . "</info>\n");
        } else {
            if ($job->attempts() > 3) {
                //通过这个方法可以检查这个任务已经重试了几次了
                print("<warn>Hello Job has been retried more than 3 times!" . "</warn>\n");

                $job->delete();

                // 也可以重新发布这个任务
                //print("<info>Hello Job will be availabe again after 2s."."</info>\n");
                //$job->release(2); //$delay为延迟时间，表示该任务延迟2秒后再执行
            }
        }
    }

    /**
     * 有些消息在到达消费者时,可能已经不再需要执行了
     * @param array|mixed    $data     发布任务时自定义的数据
     * @return boolean                 任务执行的结果
     */
    private function checkDatabaseToSeeIfJobNeedToBeDone($data){
        return true;
    }

    /**
     * 根据消息中的数据进行实际的业务处理...
     */
    private function doHelloJob($data)
    {
        //判断当前是否是1号，不是一号直接返回，不进行结算
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
                            $agentList[] = $value['id'];
                            if(Db::name('agent_stock_log')->where('status',2)->where('goods_id',1)->where('agent_id',$value['id'])->where('type',2)->count() > 0){
                                $one = Db::name('agent_stock_log')->where('status',2)->where('goods_id',1)->where('agent_id',$value['id'])->where('type',2)->order('created_at asc')->find();
                            }
                            $start_time = date("Y-m-01 00:00:00", strtotime("-{$levelInfo['month']} month"));
                            $ent_time = date("Y-m-01 00:00:00");
                            //统计前N个月的消费情况
                            $where[] = [
                                'status','eq',1,
                                'agent_parent_id','eq',$value['id']
                            ];
                            $money = 0;
                            $moneyList = Db::name('agent_stock_log')->where('status',2)->where('agent_id','in',implode(',',$agentList))->where('type',2)->where('id','neq',$one['id'] ?? 0)->whereBetweenTime('created_at',$start_time,$ent_time)->field('price,num,order_id')->select();
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
                        if(Db::name('agent_stock_log')->where('status',2)->where('goods_id',1)->where('agent_id',$value['id'])->where('type',2)->count() > 0){
                            $one = Db::name('agent_stock_log')->where('status',2)->where('goods_id',1)->where('agent_id',$value['id'])->where('type',2)->order('created_at asc')->find();
                        }
                        //统计前N个月的消费情况
                        $where[] = [
                            'status','eq',1,
                            'agent_parent_id','eq',$value['id']
                        ];
                        $money = 0;
                        $moneyList = Db::name('agent_stock_log')->where('status',2)->where('agent_id','in',implode(',',$agentList))->where('type',2)->where('id','neq',$one['id'] ?? 0)->field('price,num,order_id')->select();
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
        return true;
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
}