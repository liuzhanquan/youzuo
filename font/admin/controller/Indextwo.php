<?php
namespace app\admin\controller;

use app\common\model\Friend;
use think\Db;

class Indextwo{


    public function initialize(){
        parent::initialize();
    }

    public function index(){
    	return view();
    }

    public function home(){
        //统计订单数等数据
        $info['userCount'] = 99;//会员总数
        $info['friendCount'] = 88;//素材总数
        $info['agentCount'] = 77;//代理总数

        // $order = Order::where(['pay_status'=>2])->field('pay_money')->select();
        $info['orderCount'] = 66;//订单总数
        //$order7Day = Order::where(['pay_status'=>2])->whereTime('pay_time', 'between', [date('Y-m-d',time()-(86400*7)), date('Y-m-d')])->select();
        //$order7DayMoney = 0;
        //foreach ($order7Day as $value){
        //     $order7DayMoney += $value['pay_money'];
        // }
        //统计今日收入金额和支出金额和交易金额
        //获取七个月的销售额
        
        $this->assign('info',$info);
    	return view();
    }
}



