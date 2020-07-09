<?php
namespace app\api\job;
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/8/13
 * Time: 16:50
 */
use think\Db;
use think\Exception;
use think\facade\Log;
use think\queue\Job;
class Agent
{
    /**
     * fire方法是消息队列默认调用的方法
     * @param Job            $job      当前的任务对象
     * @param array|mixed    $data     发布任务时自定义的数据
     */
    public function fire(Job $job,$data)
    {
        Db::name('log')->insert(['created_at'=>date('Y-m-d H:i:s'),'msg'=>'结算4']);
        //Db::startTrans();
        try{
            Db::name('log')->insert(['created_at'=>date('Y-m-d H:i:s'),'msg'=>'结算2']);
            $isJobDone = $this->doHelloJob($data);
            if ($isJobDone) {
                // 如果任务执行成功， 记得删除任务
                $job->delete();
                print("<info>Hello Job has been done and deleted"."</info>\n");
            }else{
                if ($job->attempts() > 3) {
                    //通过这个方法可以检查这个任务已经重试了几次了
                    print("<warn>Hello Job has been retried more than 3 times!"."</warn>\n");

                    $job->delete();

                    // 也可以重新发布这个任务
                    //print("<info>Hello Job will be availabe again after 2s."."</info>\n");
                    //$job->release(2); //$delay为延迟时间，表示该任务延迟2秒后再执行
                }
            }
            Db::name('log')->insert(['created_at'=>date('Y-m-d H:i:s'),'msg'=>'结算3']);
            //Db::commit();
        }catch (Exception $exception){
            //Db::rollback();
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
        //执行计算
        if(!empty($data)){
            foreach ($data as $key=>$value){

                //获取当前用户的用户等级信息
                $moneyCounts = 0;
                if($value['level_id'] != 1){
                    //代理结算
                    $levelInfo = Db::name('agent_level')->where(['id'=>$value['level_id']])->field('money,people,month,reward')->find();
                    //获取上一次结算记录，上一次结算和当前必须是相隔到设置的结算周期
                    $lastLog = Db::name('agent_settlement')->where('agent_id',$value['id'])->where('type',1)->field('created_at')->order('created_at desc')->find();
                    $is_log = false;
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
                            $moneyList = Db::name('agent_stock_log')->where('status',2)->where('agent_id','in',implode(',',$agentList))->whereBetweenTime('created_at',$start_time,$ent_time)->where('type',2)->field('price,num,order_id')->select();
                            if(!empty($moneyList)){
                                foreach ($moneyList as $kk=>$vv){
                                    $money += $vv['num']*$vv['price'];
                                }
                            }
                            //计算当前代理的所得佣金
                            $moneyCounts = $this->getAgentTeamListMoney($value['id'],$money,['start'=>$start_date,'end'=>$ent_time]);
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
                        $agentList[] = $value['id'];
                        $money = 0;
                        if(Db::name('agent_stock_log')->where('status',2)->where('goods_id',1)->where('agent_id',$value['id'])->where('type',2)->count() > 0){
                            $one = Db::name('agent_stock_log')->where('status',2)->where('goods_id',1)->where('agent_id',$value['id'])->where('type',2)->order('created_at asc')->find();
                        }
                        $moneyList = Db::name('agent_stock_log')->where('status',2)->where('agent_id','in',implode(',',$agentList))->where('type',2)->where('id','neq',$one['id'] ?? 0)->select();
                        if(!empty($moneyList)){
                            foreach ($moneyList as $kk=>$vv){
                                $money += $vv['num']*$vv['price'];
                            }
                        }
                        $money3 = 0;
                        if($levelInfo){
                            //超过当前计算的佣金，可获得超出部分的一半奖金
                            if($money%19600 == 0){
                                $num = $money/19600;
                                $money3 = ($levelInfo['reward'] * $num);
                            }else{
                                $n = $money%19600;
                                $m = 0;
                                if($n > 9800){
                                    $m = $levelInfo['reward']/2;
                                }
                                $num = floor($money/19600);
                                $money3 = ($levelInfo['reward'] * $num) + $m;
                            }
                        }
                        $money = $money3;
                        //计算当前代理的所得佣金
                        $moneyCounts = $this->getAgentTeamListMoney($value['id'],$money,[]);
                        //}
                        $is_log = true;
                    }
                    unset($money);
                    $money3 = 0;
                    if($moneyCounts > 0){
                        //给代理增加余额
                        Db::name('agent')->where('id',$value['id'])->setInc('money',$moneyCounts);
                        //增加当前会员的分销记录
                        $dataArr = [
                            'agent_id'=>$value['id'],
                            'user_id'=>0,
                            'created_at'=>date('Y-m-d H:i:s'),
                            'updated_at'=>date('Y-m-d H:i:s'),
                            'money'=>$moneyCounts,
                            'tuser_id'=>0,
                            'order_id'=>0,
                            'type'=>2,
                        ];
                        Db::name('user_commission')->insertGetId($dataArr);
                        unset($dataArr);
                    }
                    //增加结算记录
                    if($is_log){
                        Db::name('agent_settlement')->insert(['agent_id'=>$value['id'],'money'=>$moneyCounts,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
                    }

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

    /**
     * 获取当前代理得实际所得金额
     * @param $agent_id integer 代理id
     * @param $money integer 总业绩
     * @param $date array 结算开始时间,结束时间
     * @return integer 返回代理最后所得金额
     */
    protected function getAgentTeamListMoney($agent_id,$money = 0,$date = []){
        if($agent_id){
            $list = Db::name('agent')->where('agent_parent_id',$agent_id)->field('id,level_id')->select();
            if(!empty($list)){

                foreach ($list as $key=>$value){
                    //计算当前用户的返佣金额，首单不算
                    $UserList = $this->getAgentTeamList($value['id']);
                    $UserList[] = $value['id'];
                    if(Db::name('agent_stock_log')->where('status',2)->where('goods_id',1)->where('agent_id',$value['id'])->where('type',2)->count() > 0){
                        $one = Db::name('agent_stock_log')->where('status',2)->where('goods_id',1)->where('agent_id',$value['id'])->where('type',2)->order('created_at asc')->find();
                    }
                    if(!empty($date)){
                        $moneyList = Db::name('agent_stock_log')->where('status',2)->where('agent_id','in',implode(',',$UserList))->where('goods_id',1)->where('id','neq',$one['id'] ?? 0)->whereBetweenTime('created_at',$date['start'],$date['end'])->select();
                    }else{
                        $moneyList = Db::name('agent_stock_log')->where('status',2)->where('agent_id','in',implode(',',$UserList))->where('goods_id',1)->where('id','neq',$one['id'] ?? 0)->select();
                    }
                    $money2 = 0;
                    if(!empty($moneyList)){
                        foreach ($moneyList as $k=>$v){
                            $money2 += $v['num'] * $v['price'];
                        }
                    }
                    if($money2 > 0){
                        //计算当前用户可以获得的佣金
                        $level = Db::name('agent_level')->where('id','eq',$value['level_id'])->find();
                        $money3 = 0;
                        if($level){
                            //超过当前计算的佣金，可获得超出部分的一半奖金
                            if($money2%19600 == 0){
                                $num = $money2/19600;
                                $money3 = ($level['reward'] * $num);
                            }else{
                                $n = $money2%19600;
                                $m = 0;
                                if($n > 9800){
                                    $m = $level['reward']/2;
                                }
                                $num = floor($money2/19600);
                                $money3 = ($level['reward'] * $num) + $m;
                            }
                        }
//                        if($money3 > 0){
//                            //给代理增加余额
//                            Db::name('agent')->where('id',$value['id'])->setDec('money',$money3);
//                            //增加当前会员的分销记录
//                            $dataArr = [
//                                'agent_id'=>$value['id'],
//                                'user_id'=>0,
//                                'created_at'=>date('Y-m-d H:i:s'),
//                                'updated_at'=>date('Y-m-d H:i:s'),
//                                'money'=>$money3,
//                                'tuser_id'=>0,
//                                'order_id'=>0,
//                                'type'=>2,
//                            ];
//                            Db::name('user_commission')->insertGetId($dataArr);
//                            unset($dataArr);
//                        }
                        $money = $money - $money3;
                    }
                    unset($UserList);
                }
            }
        }
        return $money;
    }
}