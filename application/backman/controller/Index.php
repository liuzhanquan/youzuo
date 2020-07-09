<?php
namespace app\backman\controller;
use \app\common\controller\AuthBack;
use app\common\model\Order;
use app\common\model\Customer;
use app\common\model\Detection;
use app\common\model\Friend;
use think\Db;

class Index extends AuthBack{


    public function initialize(){
        parent::initialize();
    }

    public function index(){
    	return view();
    }

    public function home(){
        //统计订单数等数据
        
        $info['customerCount'] = Customer::where('status','egt','0')->count();//客户总数
        $info['orderCount'] = Order::where('status','egt','0')->count();;//检测单总数
        // $OIwhere[] = ['status','gt', '0' ];
        // $OIwhere[] = ['status','lt', '2' ];
        

        $info['orderIngCount'] = Order::where( 'status', '1' )->count();//进行中的检测单总数

        // $order = Order::where(['pay_status'=>2])->field('pay_money')->select();
        $info['detectionCount'] = Detection::where('status','egt','0')->count();//检测流程总数
        //$order7Day = Order::where(['pay_status'=>2])->whereTime('pay_time', 'between', [date('Y-m-d',time()-(86400*7)), date('Y-m-d')])->select();
        //$order7DayMoney = 0;
        //foreach ($order7Day as $value){
        //     $order7DayMoney += $value['pay_money'];
        // }
        $time=array();
        $currentTime = time();
        $cyear = floor(date("Y",$currentTime));
        $cMonth = floor(date("m",$currentTime));
        $yearArr = [];
        for($i=11;$i>=-1;$i--){
            $nMonth = $cMonth-$i;
            $nyear = $nMonth == 0 ? ($cyear-1) : $cyear;
            if( $nMonth > 12 ){
                $nMonth = $nMonth-12;
                $nyear = $cyear+1;
            }else{
                $nMonth = $nMonth <= 0 ? 12+$nMonth : $nMonth;
            }
            
            if( $nMonth < 10 ){
                $nMonth = '0'.$nMonth;
            }
            $time[] = date('Y-m-d H:i:s',strtotime($nyear.'-'.$nMonth));
            if( $i >= 0 ){
                $yearArr['time'][] = $nyear.'-'.$nMonth;
            }
        }
        
       
        for( $i = 0; $i < count($time)-1; $i++ ){
            $yearArr['total'][] = DB::name('order')->where('created_time','between time',[$time[$i],$time[$i+1]])->count();
        }
        
        $info['time'] = json_encode( $yearArr['time']  );
        $info['total'] = json_encode( $yearArr['total']  );
        //统计今日收入金额和支出金额和交易金额
        //获取七个月的销售额
        
        $this->assign('info',$info);
    	return view();
    }
	
	
	
	
	
	
	
	
	
	
	
}



