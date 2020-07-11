<?php
namespace app\backman\controller;
use app\backman\model\Meal;
use \app\common\controller\AuthBack;
use \app\common\model\Category;
use \app\common\model\Detection;
use app\common\model\Comment;
use \app\common\model\Goods;
use \app\common\model\Customer;
use \app\common\model\Order;
use think\Db;
use think\facade\App;
require_once App::getRootPath().'/extend/PHPExcel/PHPExcel.php';

class Rate extends AuthBack{


    public function initialize(){
        parent::initialize();
    }

    public function index(){
        $type = 1;
		$where = [];
        if( !empty(input('gid')) ){
            $type = 1;
            $gid = categorySelSon('category',input('gid'),true);
            $where[] = ['gid','in',$gid];
        }
        if( !empty(input('spec')) ){
            $type = 1;
            $where[] = ['spec', 'like', "%".input('spec')."%"];
        }
        if( !empty(input('customer')) ){
            $type = 2;
            $Cwhere[] = ['customer_name|phone|name|customer_sn', 'like', "%".input('customer')."%"];
        }
        //查询产品分类
		if( $type == 1 ){
			$totalNum = Detection::alias('d')->join('order o','d.goods_id = o.goods_id')->join('customer c','d.customer_id = c.id')->where($where)->group('d.id')->count();
		} else {
			$totalNum = Goods::alias('g')->join('detection d','g.id = d.goods_id')->join('order o','g.id = o.goods_id')->where($where)->group('g.id')->count();
		}

        if(request()->isAjax()){
            $page = isset($_GET['page']) ? $_GET['page'] : '1';
            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
            $startNum = ($page - 1) * $limit;
            $totalPage = ceil($totalNum/$limit);

	        if( $type == 2 ){
		        $list = Detection::alias('d')
			            ->join('customer c','c.id = d.customer_id')
			            ->where($where)
			            ->group('d.customer_id')
			            ->field('c.customer_sn as sn, c.customer_name as name, c.id, sum(d.count_num) as c_num')
			            ->order('c_num desc')
			            ->limit($startNum.','.$limit)
			            ->select();
	        } else {
		        $list = Detection::alias('d')
				        ->join('goods g','g.id = d.goods_id')
				        ->where($where)
				        ->group('d.goods_id')
				        ->field('g.good_sn as sn, g.title as name, g.id,sum(d.count_num) as c_num')
				        ->order('c_num desc')
				        ->limit($startNum.','.$limit)
				        ->select();
	        }

            
            if(empty($list)){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage,'count'=>$totalNum]);
            }elseif($page > $totalPage){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage+1,'count'=>$totalNum]);
            }else{
                $nList = array();
                if(!empty($list)){
                    foreach($list as $k=>$vo){
//                        $rate = $this->rateTotal($vo,$whereArr);
                        $nList[$k] = $vo;
                        $nList[$k]['sn'] = $vo['sn'];
                        $nList[$k]['name'] = $vo['name'];
	                    $nList[$k]['c_num'] = $vo['c_num'];
	                    if( $type == 1 ){
		                    $nList[$k]['res_num'] = Order::where('customer_id',$vo['id'])->count();
	                    } else {
		                    $nList[$k]['res_num'] = Order::where('goods_id',$vo['id'])->count();
	                    }


                        // $nList[$k]['op'] = url('option',['id'=>$vo['id']]);
                    }
                }
                $return = [
                    'code'=>0,
                    'msg'=>'',
                    'count'=>$totalNum,
                    'data'=>$nList
                ];
                return json($return);
            }
        }else{
            $this->assign('totalNum',$totalNum);
            return view();
        }
    }

    /**
     * 产品合格率、风险率、不合格率统计
     */
    public function rateTotal($item,$whereArr = []){
        $where[] = ['status','egt','2'];
        // if( $whereArr ){
        //     $where[] = ['cid','in',$whereArr]; 
        // }
        $where[] = ['gid','eq',$item['gid']['value']];
        $where[] = ['spec','eq',$item['spec']];
        $result['count'] = Order::where( $where )->where('engding','neq','0') -> count();
        
        $pass = Order::where( $where )->where('engding',1)-> count();
        if(  $pass ){
            $result['pass'] = (round($pass/$result['count'],2)*100).'%';
        }else{
            $result['pass'] = 0;
        }

        $warn = Order::where( $where )->where('engding',2)-> count();
        if(  $warn ){
            $result['warn'] = (round($warn/$result['count'],2)*100 ).'%';
        }else{
            $result['warn'] = 0;
        }

        $unpass = Order::where( $where )->where('engding',3)-> count();
        if(  $unpass ){
            $result['unpass'] = (round($unpass/$result['count'],2)*100).'%';
        }else{
            $result['unpass'] = 0;
        }

        $runing = Order::where( $where )->where('engding',0)-> count();
        if(  $runing ){
            $result['runing'] = $runing;
            $result['count'] = $result['count']+ $runing;
        }else{
            $result['runing'] = 0;
        }
        
        return $result;

    }




    /*
    * author: Jason
    * 产品批量导入
    */
    public function savestudentImport(){  
        $config = load_config('application/upload');
        $size = $config['upload_size']*1024*1024;

        $objPHPExcel  = new \PHPExcel();
        $root_path = \think\facade\Env::get('root_path').'public';
        //获取表单上传文件  
        $file = request()->file('file');  
        $info = $file->validate(['size'=>$size,'ext'=>'xlsx,xls,csv'])->move( $root_path . '/excel');  
        if($info){  
            $exclePath = $info->getSaveName();  //获取文件名  
            
            $file_name = $root_path . '/excel/'. $exclePath;   //上传文件的地址  
            
            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');  
            
            if(!$objReader->canRead($file_name)){
                $objReader = \PHPExcel_IOFactory::createReader('Excel5');
            }
            
            $obj_PHPExcel =$objReader->load($file_name, $encode = 'utf-8');  //加载文件内容,编码utf-8  
            $excel_array=$obj_PHPExcel->getsheet(0)->toArray();   //转换为数组格式  
            array_shift($excel_array);  //删除第一个数组(标题);  
            $data = [];  
            $i=0;  
            $resn = [];
            $category = DB::name('category')->where('type',1)->field('id,name')->order('id asc')->select();
            foreach($excel_array as $k=>$v) {  
                if( empty($v[2])){
                    continue;
                }
                
                // 查看是否设置员工号码 有就查询数据库， 重复记录返回
                if( empty( $v[0] ) ){
                    $data[$k]['good_sn'] = 'CP'.get_order_sn();
                }else{
                    $where[] = ['good_sn','eq',$v[0]];
                    $count = DB::name('goods')->where($where)->count();
                    if(!$count){
                        $data[$k]['good_sn'] = $v[0]; 
                    }else{
                        $resn[] = $v[0];
                    }
                    
                }

                if( empty($v[1]) ){
                    $data[$k]['cid'] = $category[0]['id'];
                }else{
                    foreach( $category as $item ){
                        if( $item['name'] == $v[1] ){
                            $data[$k]['cid'] = $item['id'];
                        }
                    }
                    if( empty($data[$k]['cid']) ){
                        $data[$k]['cid'] = $category[0]['id'];
                    }
                }
                $data[$k]['title'] = $v[2]; 
                
                $data[$k]['status'] = 0;
                $i++;  
            }  
            
           $success=Db::name('goods')->insertAll($data); //批量插入数据  这里的数据表改为你需要的。
           // 统计录入失败数量
           $error=$i-$success;
            // 删除文件
           unset($info);
           unset($obj_PHPExcel);
           unlink($file_name);

            $return = [
                'code'=>0,
                'msg'=>"导入成功{$success}条记录，失败{$error}条记录,单号重复".json_encode($resn),
            ];

            return json($return);
           
        }else{  
            // 上传失败获取错误信息  
              
            return json($file->getError());
        }  
  
    }

    

}