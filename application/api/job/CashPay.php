<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/10/16
 * Time: 17:27
 */

namespace app\api\job;

use think\Db;
use think\Exception;
use think\facade\Log;
use think\queue\Job;
use Naixiaoxin\ThinkWechat\Facade;
class CashPay
{
    /**
     * fire方法是消息队列默认调用的方法
     * @param Job            $job      当前的任务对象
     * @param array|mixed    $data     发布任务时自定义的数据
     */
    public function fire(Job $job,$data)
    {
        Db::name('log')->insert(['created_at'=>date('Y-m-d H:i:s'),'msg'=>'提现打款']);
        //Db::startTrans();
        try{
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
        //执行企业付款计划
        if(!empty($data) && ($data['make_money_status'] == 1 || $data['make_money_status'] == 4)){
            $payment = Facade::payment(); // 微信支付
            //$openPlatform = Facade::openPlatform(); // 开放平台
            //$miniProgram = Facade::miniProgram(); // 小程序
            $userInfo = Db::name('user')->where('uid',$data['user_id'])->field('openid')->find();
            $res = $payment->transfer->toBalance([
                'partner_trade_no' => $data['order_sn'], // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
                'openid' => $userInfo['openid'],
                'check_name' => 'NO_CHECK', // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
                're_user_name' => '', // 如果 check_name 设置为FORCE_CHECK，则必填用户真实姓名
                'amount' => $data['rel_money'] * 100, // 企业付款金额，单位为分
                'desc' => '零钱付款', // 企业付款操作说明信息。必填
            ]);

            if($res['result_code'] == 'FAIL'){
                //更新订单状态
                Db::name('agent_withdraw')->where('id',$data['id'])->update(['updated_at'=>date('Y-m-d H:i:s'),'make_money_status'=>4,'status'=>3]);
                Db::name('agent_withdraw_log')->insert(['agent_withdraw_id'=>$data['id'],'status'=>4,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
                return true;
            }
            //更新订单状态，增加打款记录
            Db::name('agent_withdraw')->where('id',$data['id'])->update(['updated_at'=>date('Y-m-d H:i:s'),'make_money_status'=>3,'status'=>2,'make_money_time'=>date('Y-m-d H:i:s')]);
            Db::name('agent_withdraw_log')->insert(['agent_withdraw_id'=>$data['id'],'status'=>3,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
        }
        return true;
    }
}