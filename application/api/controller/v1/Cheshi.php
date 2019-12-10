<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/10/14
 * Time: 19:06
 */

namespace app\api\controller\v1;
use Psr\Log\Test\DummyTest;
use think\Db;
use think\Controller;
class Cheshi extends Controller
{
    public function index(){
        $data = Db::name('agent')->where(['status'=>1])->field('agent_parent_id,id,status,level_id')->select();
//        if(!empty($data)){
//            foreach ($data as $key=>$value){
//                //获取当前用户的用户等级信息，只有联创才计算升级，
//                $is_log = false;
//                if($value['level_id'] != 1 && $value['level_id'] < 10){
//                    $levelInfo = Db::name('agent_level')->where(['id'=>$value['level_id']])->field('money,people,month,name,id')->find();
//                    //获取上一次结算记录，上一次结算和当前必须是相隔到设置的结算周期
//                    $lastLog = Db::name('agent_settlement')->where('agent_id',$value['id'])->where('type',2)->field('created_at')->order('created_at desc')->find();
//                    if($lastLog){
//                        //存在上一次结算记录，判断当前是否达到结算时间
//                        $month = $levelInfo['month'];//结算周期
//                        $start_date = date('Y-m-d',strtotime($lastLog['created_at']));
//                        $end_date = date('Y-m-d');
//                        $num_month = month_numbers($start_date,$end_date);
//                        if($month < $num_month){
//                            //达到要求，进行结算
//                            $agentList = $this->getAgentTeamList($value['id']);
//                            $agentList[] = $value['id'];
//                            if(Db::name('agent_stock_log')->where('goods_id',1)->where('agent_id',$value['id'])->where('type',2)->count() > 0){
//                                $one = Db::name('agent_stock_log')->where('goods_id',1)->where('agent_id',$value['id'])->where('type',2)->order('created_at asc')->find();
//                            }
//                            $start_time = date("Y-m-01 00:00:00", strtotime("-{$levelInfo['month']} month"));
//                            $ent_time = date("Y-m-01 00:00:00");
//                            //统计前N个月的消费情况
//                            $where[] = [
//                                'status','eq',1,
//                                'agent_parent_id','eq',$value['id']
//                            ];
//                            $money = 0;
//                            $moneyList = Db::name('agent_stock_log')->where('agent_id','in',implode(',',$agentList))->where('type',2)->where('id','neq',$one['id'] ?? 0)->whereBetweenTime('created_at',$start_time,$ent_time)->field('price,num,order_id')->select();
//                            if(!empty($moneyList)){
//                                foreach ($moneyList as $kk=>$vv){
//                                    $money += $vv['num']*$vv['price'];
//                                }
//                            }
//
//                            //获取用户达到的会员等级
//                            $levelInfoUpdate = Db::name('agent_level')->where('money','<=',$money)->order('money desc')->find();
//                            if($money >= $levelInfoUpdate['money'] && $levelInfoUpdate['id'] > $levelInfo['id']){
//                                //达到升级条件
//                                Db::name('agent')->where(['id'=>$value['id']])->update(['level_id'=>$levelInfoUpdate['id']]);
//                                //增加会员升级记录
//                                Db::name('agent_level_log')->insertGetId(['agent_id'=>$value['id'],'user_id'=>0,'msg'=>'升级到'.$levelInfoUpdate['name'],'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
//                            }
//                            $is_log = true;
//                        }
//                    }else{
//                        //不存在上一次记录，直接判断是否足够条件升级
//                        $month = $levelInfo['month'];//结算周期
//                        $start_date = date('Y-m-d',strtotime($lastLog['created_at']));
//                        $end_date = date('Y-m-d');
//                        $num_month = month_numbers($start_date,$end_date);
//                        //if($month < $num_month){
//                        //达到要求，进行结算
//                        $agentList = $this->getAgentTeamList($value['id']);
//                        $agentList[] = $value['id'];
//                        if(Db::name('agent_stock_log')->where('goods_id',1)->where('agent_id',$value['id'])->where('type',2)->count() > 0){
//                            $one = Db::name('agent_stock_log')->where('goods_id',1)->where('agent_id',$value['id'])->where('type',2)->order('created_at asc')->find();
//                        }
//                        //统计前N个月的消费情况
//                        $where[] = [
//                            'status','eq',1,
//                            'agent_parent_id','eq',$value['id']
//                        ];
//                        $money = 0;
//                        $moneyList = Db::name('agent_stock_log')->where('agent_id','in',implode(',',$agentList))->where('type',2)->where('id','neq',$one['id'] ?? 0)->field('price,num,order_id')->select();
//                        if(!empty($moneyList)){
//                            foreach ($moneyList as $kk=>$vv){
//                                $money += $vv['num']*$vv['price'];
//                            }
//                        }
//
//                        //获取用户达到的会员等级
//                        $levelInfoUpdate = Db::name('agent_level')->where('money','<',$money)->order('money desc')->find();
//                        if($money >= $levelInfoUpdate['money'] && $levelInfoUpdate['id'] > $levelInfo['id']){
//                            //达到升级条件
//                            Db::name('agent')->where(['id'=>$value['id']])->update(['level_id'=>$levelInfoUpdate['id']]);
//                            //增加会员升级记录
//                            Db::name('agent_level_log')->insertGetId(['agent_id'=>$value['id'],'user_id'=>0,'msg'=>'升级到'.$levelInfoUpdate['name'],'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
//                        }
//                        //}
//                        $is_log = true;
//                    }
//                }
//
//                //增加结算记录
//                if($is_log){
//                    Db::name('agent_settlement')->insert(['agent_id'=>$value['id'],'money'=>0,'type'=>2,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
//                }
//
//            }
//        }

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
                            $moneyList = Db::name('agent_stock_log')->where('agent_id','in',implode(',',$agentList))->whereBetweenTime('created_at',$start_time,$ent_time)->where('type',2)->field('price,num,order_id')->select();
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
                        if(Db::name('agent_stock_log')->where('goods_id',1)->where('agent_id',$value['id'])->where('type',2)->count() > 0){
                            $one = Db::name('agent_stock_log')->where('goods_id',1)->where('agent_id',$value['id'])->where('type',2)->order('created_at asc')->find();
                        }
                        $moneyList = Db::name('agent_stock_log')->where('agent_id','in',implode(',',$agentList))->where('type',2)->where('id','neq',$one['id'] ?? 0)->select();
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
    }


    public function agent(){
        $data = Db::name('agent')->where(['status'=>1])->field('agent_parent_id,id,status,level_id')->select();
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
                        //$month = $levelInfo['month'];//结算周期
                        $start_date = date('Y-m-01 00:00:00',strtotime($lastLog['created_at']));
                        $end_date = date("Y-m-01 00:00:00", strtotime("+{$levelInfo['month']} month",strtotime($start_date)));
                        //$num_month = month_numbers($start_date,$end_date);
                        if(time() > strtotime($end_date)){
                            //达到要求，进行结算

                            $agentList = $this->getAgentTeamList($value['id']);
                            //$start_time = date("Y-m-01 00:00:00", strtotime("-{$levelInfo['month']} month"));
                            //$ent_time = date("Y-m-01 00:00:00");
                            //统计前N个月的消费情况
                            $where[] = [
                                'status','eq',1,
                                'agent_parent_id','eq',$value['id']
                            ];
                            $money = 0;
                            $agentList[] = $value['id'];
                            $moneyList = Db::name('agent_stock_log')->where('status',2)->where('agent_id','in',implode(',',$agentList))->whereTime('created_at', 'between', [$start_date, $end_date])->where('type',2)->field('price,num,order_id')->select();
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
                            $moneyCounts = $this->getAgentTeamListMoney($value['id'],$money,['start'=>$start_date,'end'=>$end_date]);
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
    }

    public function agentUpdate(){
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
                            $moneyList = Db::name('agent_stock_log')->where('status',2)->where('agent_id','in',implode(',',$agentList))->where('type',2)->whereTime('created_at', 'between', [$start_date, $end_date])->field('price,num,order_id')->select();


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

        //更改用户的代理关系
        $user = Db::name('user')->select();
        if(!empty($user)){
            foreach ($user as $kkk=>$vvv){
                if($vvv['parent_id']){
                    $agent_id = $this->getUser($vvv['parent_id']);
                    if($agent_id){
                        if($vvv['agent_id'] != $agent_id){
                            //更新当前用户代理关系
                            Db::name('user')->where('uid',$vvv['id'])->update(['agent_id'=>$agent_id]);
                        }
                    }
                }
            }
        }
    }

    /**
     * 递归获取终端代理下面的终端代理
     * @param int $user_id 当前会员
     * @param int $tuser_id 上级会员
     * @return array
     */
    protected function getUser($user_id = 0){
        $agent_id = 0;
        $user = Db::name('user')->where('uid',$user_id)->field('parent_id,agent_id,openid')->find();
        //判断当前用户是否是代理
        $agent = Db::name('agent')->where('openid',$user['openid'])->find();
        if($agent){
            $agent_id = $agent['id'];
        }else{
            if($user['agent_id']){
                $agent_id = $user['agent_id'];
                //return $user['agent_id'];

            }else{
                //判断当前会员是否有代理，有代理的话直接返回，没有代理的话往上无线查
                if($user['parent_id']){
                    $agent_id = $this->getUser($user['parent_id']);
                }
            }
        }


        return $agent_id;

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
                    if(Db::name('agent_stock_log')->where('goods_id',1)->where('status',2)->where('agent_id',$value['id'])->where('type',2)->count() > 0){
                        $one = Db::name('agent_stock_log')->where('goods_id',1)->where('agent_id',$value['id'])->where('status',2)->where('type',2)->order('created_at asc')->find();
                    }
                    if(!empty($date)){
                        $moneyList = Db::name('agent_stock_log')->where('agent_id','in',implode(',',$UserList))->where('status',2)->where('goods_id',1)->where('id','neq',$one['id'] ?? 0)->whereBetweenTime('created_at',$date['start'],$date['end'])->select();
                    }else{
                        $moneyList = Db::name('agent_stock_log')->where('agent_id','in',implode(',',$UserList))->where('status',2)->where('goods_id',1)->where('id','neq',$one['id'] ?? 0)->select();
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

                        $money = $money -$money3;
                    }
                }
            }
        }

        return $money;
    }
}