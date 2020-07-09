<?php
namespace app\backman\controller;
use \app\common\controller\AuthBack;
use \app\backman\model\AgentLevel;
use \app\backman\model\Agent as Item;
use app\common\model\AgentStock;
use app\common\model\AgentStockLog;
use app\common\model\AgentStockOrder;
use \app\common\model\Config;
use app\common\model\Goods;
use app\common\model\User;
use lib\CashStatus;
use think\Db;
use think\facade\Env;
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;



class Agent extends AuthBack{


    public function initialize(){
        parent::initialize();
    }

    /**
     * 设置代理授权证书
     */
    public function settpl($id = 0){
        if(!isPost()) {
            $info = AgentLevel::where('id', $id)->find();
            $data = array();
            $position = unserialize($info['position']);
            if (!empty($position)) {
                foreach ($position as $k => $vo) {
                    $style = 'position:absolute;';
                    if (!empty($vo['position'])) {
                        $posArr = explode(',', $vo['position']);
                        $style .= "left:{$posArr[0]}px;top:{$posArr[1]}px;";
                    }
                    $style .= "font-size:" . $vo['size'] . "px;";
                    $style .= "font-weight:" . $vo['bold'] . ";";
                    $style .= "font-style:" . $vo['italic'] . ";";
                    $style .= "color:" . $vo['color'] . ";";
                    $data[$k]['style'] = $style;
                }
            }
            $this->assign('data', $data);
            $this->assign('info', $info);
            $this->assign('position', $position);
            return view();
        }else{
            $data = $_POST;
            $udata['position'] = serialize($data);
            $state = Db::name('agent_level')->where('id',$id)->update($udata);
            if($state){
                return $this->success('操作成功',url('level'));
            }else{
                return $this->error('操作失败！');
            }
        }
    }

    public function stock($id = 0){
        $where = [];
        if($id){
            $where['agent_id'] = $id;
        }
        $name = isset($_GET['name']) ? $_GET['name'] : '';
        if($name){
            //查询商品
            $goods_id = \app\backman\model\Agent::where([['name','like',"%{$name}%"]])->field('id')->select();
            $arr = [];

            if(!empty($goods_id)){
                foreach ($goods_id as $key=>$value){
                    $arr[] = $value['id'];
                }
            }
            $goods = '';
            if(!empty($arr)){
                $goods = implode(',',$arr);
            }
            if($goods){
                $where[] = ['agent_id','in',[$goods]];
            }
        }
        $totalNum = AgentStock::where($where)->count();
        if(request()->isAjax()){
            $page = isset($_GET['page']) ? $_GET['page'] : '1';
            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
            $startNum = ($page - 1) * $limit;
            $totalPage = ceil($totalNum/$limit);
            $list = AgentStock::where($where)->limit($startNum.','.$limit)->order('id desc')->select();
            if(empty($list)){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage,'count'=>$totalNum]);
            }elseif($page > $totalPage){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage+1,'count'=>$totalNum]);
            }else{
                $nList = array();
                if(!empty($list)){
                    foreach($list as $k=>$vo){
                        $nList[$k] = $vo;

                        $user = User::where('uid',$vo['user_id'])->field('nickname,headimgurl')->find();
                        $nList[$k]['goods_name'] = Goods::where('id',$vo['goods_id'])->value('title');
                        $nList[$k]['user_name'] = $user['nickname'];
                        $nList[$k]['headimgurl'] = $user['headimgurl'];
                        $nList[$k]['agent_name'] = \app\backman\model\Agent::where('id',$vo['agent_id'])->value('name');
                        //$nList[$k]['text'] = $vo['cid']['text'];
                        $nList[$k]['op'] = url('option',['id'=>$vo['id']]);
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

    public function order($id = 0){
        $where = [];
        if($id){
            $where['agent_id'] = $id;
        }
        $name = isset($_GET['name']) ? $_GET['name'] : '';
        if($name){
            //查询商品
            $goods_id = \app\backman\model\Agent::where([['name','like',"%{$name}%"]])->field('id')->select();
            $arr = [];

            if(!empty($goods_id)){
                foreach ($goods_id as $key=>$value){
                    $arr[] = $value['id'];
                }
            }
            $goods = '';
            if(!empty($arr)){
                $goods = implode(',',$arr);
            }
            if($goods){
                $where[] = ['agent_id','in',[$goods]];
            }
        }
        $totalNum = AgentStockOrder::where($where)->count();
        if(request()->isAjax()){
            $page = isset($_GET['page']) ? $_GET['page'] : '1';
            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
            $startNum = ($page - 1) * $limit;
            $totalPage = ceil($totalNum/$limit);
            $list = AgentStockOrder::where($where)->limit($startNum.','.$limit)->order('id desc')->select();
            if(empty($list)){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage,'count'=>$totalNum]);
            }elseif($page > $totalPage){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage+1,'count'=>$totalNum]);
            }else{
                $nList = array();
                if(!empty($list)){
                    foreach($list as $k=>$vo){
                        $nList[$k] = $vo;
                        $orderGoods = AgentStockLog::where('order_id',$vo['id'])->field('goods_id')->order('id desc')->find();
                        $nList[$k]['goods_name'] = Goods::where('id',$orderGoods['goods_id'])->value('title');
                        $nList[$k]['status_name'] = CashStatus::AGENT_ORDER_STATUS[$vo['status']];
                        $nList[$k]['type_name'] = CashStatus::AGENT_ORDER_TYPE[$vo['type']];
                        $nList[$k]['user_name'] = User::where('uid',$vo['user_id'])->value('nickname');
                        $nList[$k]['agent_name'] = \app\backman\model\Agent::where('id',$vo['agent_id'])->value('name');
                        //$nList[$k]['text'] = $vo['cid']['text'];
                        $nList[$k]['op'] = url('details',['id'=>$vo['id']]);
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
            $this->assign('id',$id);
            return view();
        }
    }


    public function details($id = 0){
        if(!request()->isPost()){
            $order = \app\common\model\AgentStockOrder::where('id',$id)->find();
            $user = User::where('uid',$order['user_id'])->field('nickname')->find();
            $order['user_name'] = $user['nickname'];
//            if($order['user_name'] && is_string($order['user_name'])){
//                $order['user_name'] = unserialize($order['user_name']);
//            }
            //$order['user_name'] = $order['user_name'];
            $goods = AgentStockLog::where('order_id',$id)->field('goods_id,num,price')->select();
            if(!empty($goods)){
                foreach ($goods as $key=>$value){
                    $goods[$key]['info'] = Goods::where('id',$value['goods_id'])->find();
                }
            }
            $order['goods_list'] = $goods;
            $order['status_name'] = CashStatus::AGENT_ORDER_STATUS[$order['status']];
            $order['type_name'] = CashStatus::AGENT_ORDER_TYPE[$order['type']];
            $this->assign('order',$order);
            return view();
        }else{
            Db::startTrans();
            $data = $this->request->param();
            $id = $data['id'];
            $status = $data['status'];
            $state = AgentStockOrder::where('id',$id)->update(['status'=>$status,'pay_at'=>date('Y-m-d H:i:s')]);
            if($state){
                //更新代理库存记录
                $goods = AgentStockLog::where('order_id',$id)->field('num,goods_id,agent_id,user_id')->select();
                if(!empty($goods)){
                    foreach ($goods as $key=>$value){
                        $r = AgentStock::where(['agent_id'=>$value['agent_id'],'user_id'=>$value['user_id'],'goods_id'=>$value['goods_id']])->find();
                        if($r){
                            //更新数据
                            $rr = $r->allowField(true)->save(['num'=>$r->num + $value['num']],['id'=>$r->id]);
                        }else{
                            //创建数据
                            $data = [
                                'goods_id'=>$value['goods_id'],
                                'num'=>$value['num'],
                                'agent_id'=>$value['agent_id'],
                                'user_id'=>$value['user_id'],
                                'updated_at'=>date('Y-m-d H:i:s'),
                                'created_at'=>date('Y-m-d H:i:s'),
                            ];
                            $rr = AgentStock::create($data);
                            //$rr = $r->allowField(true)->save($value);
                        }

                        if(!$rr){
                            Db::rollback();
                            return $this->error('失败');
                        }
                    }
                }

                Db::commit();
                return $this->success('操作成功',url('order'));
            }else{
                Db::rollback();
                return $this->error('失败');
            }
        }
    }

    public function purchase($id = 0){
        $name = isset($_GET['name']) ? $_GET['name'] : '';
        $where = [];
        if($name){
            //查询商品
            $goods_id = \app\backman\model\Agent::where([['name','like',"%{$name}%"]])->field('id')->select();
            $arr = [];

            if(!empty($goods_id)){
                foreach ($goods_id as $key=>$value){
                    $arr[] = $value['id'];
                }
            }
            $goods = '';
            if(!empty($arr)){
                $goods = implode(',',$arr);
            }
            if($goods){
                $where[] = ['agent_id','in',[$goods]];
            }
        }
        if($id){
			$where[] = ['agent_id','eq',$id];
        }
        $totalNum = AgentStockLog::where($where)->count();
        if(request()->isAjax()){
            $page = isset($_GET['page']) ? $_GET['page'] : '1';
            $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
            $startNum = ($page - 1) * $limit;
            $totalPage = ceil($totalNum/$limit);
            $list = AgentStockLog::where($where)->limit($startNum.','.$limit)->order('id desc')->select();
            if(empty($list)){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage,'count'=>$totalNum]);
            }elseif($page > $totalPage){
                return json(['code'=>0,'data'=>[],'total'=>$totalPage+1,'count'=>$totalNum]);
            }else{
                $nList = array();
                if(!empty($list)){
                    foreach($list as $k=>$vo){
                        $nList[$k] = $vo;
                        $user = User::where('uid',$vo['user_id'])->field('nickname,headimgurl')->find();
                        $nList[$k]['goods_name'] = Goods::where('id',$vo['goods_id'])->value('title');
                        $nList[$k]['name'] = \app\backman\model\Agent::where('id',$vo['agent_id'])->value('name');
                        //$nList[$k]['user_name'] = $user['nickname'];
                        $nList[$k]['headimgurl'] = $user['headimgurl'];
                        //$nList[$k]['text'] = $vo['cid']['text'];
                        $nList[$k]['op'] = url('option',['id'=>$vo['id']]);
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

    public function index(){
        $model = new Item;
        $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
        $name = isset($_GET['name']) ? $_GET['name'] : '';
        $phone = isset($_GET['phone']) ? $_GET['phone'] : '';
        $where = [];
        if($name){
            $where[] = ['name','like',"%{$name}%"];
        }
        if($phone){
            $where[] = ['phone','like',"%{$phone}%"];
        }
        $page = $model->getlist($where,$limit,'id desc');
        if(request()->isAjax()){
            if($this->data['page'] > $page->lastPage()){
                return json(['code'=>0,'data'=>[],'total'=>$page->lastPage()+1,'count'=>$page->total()]);
            }else{
                $nList = array();
                foreach($page as $k=>$vo){
                    $nList[$k] = $vo;
                    $user = User::where('openid',$vo['openid'])->field('nickname,headimgurl')->find();
                    $name = '无';
                    if($vo['agent_parent_id']){
                        $name = Db::name('agent')->where('id',$vo['agent_parent_id'])->value('name');
                    }
                    $nList[$k]['parent'] = $name;//getAgentParent($vo['agent_parent_id']);
                    $nList[$k]['level_name'] = $vo['level']['name'];
                    $nList[$k]['status_name'] = CashStatus::AGENT_STATUS[$vo['status']];
                    $nList[$k]['headimgurl'] = $user['headimgurl'];
                    $nList[$k]['nickname'] = $user['nickname'];
                    $nList[$k]['op'] = url('option',['id'=>$vo['id']]);
                    $nList[$k]['stock'] = url('stock',['id'=>$vo['id']]);
                    $nList[$k]['goodss'] = url('goods',['id'=>$vo['id']]);
                  $nList[$k]['purchase'] = url('purchase',['id'=>$vo['id']]);
                }
                $return = ['code'=>0,'msg'=>'','count'=>$page->total(),'data'=>$nList];
                return json($return);
            }
        }
        $this->assign('totalNum',$page->total());
        $this->assign('limit',$page->listRows());
        return view();
    }

    public function index2(){
        $model = new Item;
        $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
        $name = isset($_GET['name']) ? $_GET['name'] : '';
        $phone = isset($_GET['phone']) ? $_GET['phone'] : '';
        $where[] = ['status','eq',1];
        if($name){
            $where[] = ['name','like',"%{$name}%"];
        }
        if($phone){
            $where[] = ['phone','like',"%{$phone}%"];
        }
        $page = $model->getlist($where,$limit,'id desc');
        if(request()->isAjax()){
            if($this->data['page'] > $page->lastPage()){
                return json(['code'=>0,'data'=>[],'total'=>$page->lastPage()+1,'count'=>$page->total()]);
            }else{
                $nList = array();
                foreach($page as $k=>$vo){
                    $nList[$k] = $vo;
                    $user = User::where('openid',$vo['openid'])->field('nickname,headimgurl')->find();
                    $name = '无';
                    if($vo['agent_parent_id']){
                        $name = Db::name('agent')->where('id',$vo['agent_parent_id'])->value('name');
                    }
                    $nList[$k]['parent'] = $name;//getAgentParent($vo['agent_parent_id']);
                    $nList[$k]['level_name'] = $vo['level']['name'];
                    $nList[$k]['status_name'] = CashStatus::AGENT_STATUS[$vo['status']];
                    $nList[$k]['headimgurl'] = $user['headimgurl'];
                    $nList[$k]['nickname'] = $user['nickname'];
                    $nList[$k]['op'] = url('option',['id'=>$vo['id']]);
                    $nList[$k]['stock'] = url('stock',['id'=>$vo['id']]);
                    $nList[$k]['goodss'] = url('goods',['id'=>$vo['id']]);
                      $nList[$k]['purchase'] = url('purchase',['id'=>$vo['id']]);
                }
                $return = ['code'=>0,'msg'=>'','count'=>$page->total(),'data'=>$nList];
                return json($return);
            }
        }
        $this->assign('totalNum',$page->total());
        $this->assign('limit',$page->listRows());
        return view();
    }

    public function index3(){
        $model = new Item;
        $limit = isset($_GET['limit']) ? $_GET['limit'] : '20';
        $name = isset($_GET['name']) ? $_GET['name'] : '';
        $phone = isset($_GET['phone']) ? $_GET['phone'] : '';
        $where[] = ['status','neq',1];
        if($name){
            $where[] = ['name','like',"%{$name}%"];
        }
        if($phone){
            $where[] = ['phone','like',"%{$phone}%"];
        }
        $page = $model->getlist($where,$limit,'id desc');
        if(request()->isAjax()){
            if($this->data['page'] > $page->lastPage()){
                return json(['code'=>0,'data'=>[],'total'=>$page->lastPage()+1,'count'=>$page->total()]);
            }else{
                $nList = array();
                foreach($page as $k=>$vo){
                    $nList[$k] = $vo;
                    $user = User::where('openid',$vo['openid'])->field('nickname,headimgurl')->find();
                    $name = '无';
                    if($vo['agent_parent_id']){
                        $name = Db::name('agent')->where('id',$vo['agent_parent_id'])->value('name');
                    }
                    $nList[$k]['parent'] = $name;//getAgentParent($vo['agent_parent_id']);
                    $nList[$k]['level_name'] = $vo['level']['name'];
                    $nList[$k]['status_name'] = CashStatus::AGENT_STATUS[$vo['status']];
                    $nList[$k]['headimgurl'] = $user['headimgurl'];
                    $nList[$k]['nickname'] = $user['nickname'];
                    $nList[$k]['op'] = url('option',['id'=>$vo['id']]);
                    $nList[$k]['stock'] = url('stock',['id'=>$vo['id']]);
                    $nList[$k]['goodss'] = url('goods',['id'=>$vo['id']]);
                  $nList[$k]['purchase'] = url('purchase',['id'=>$vo['id']]);
                }
                $return = ['code'=>0,'msg'=>'','count'=>$page->total(),'data'=>$nList];
                return json($return);
            }
        }
        $this->assign('totalNum',$page->total());
        $this->assign('limit',$page->listRows());
        return view();
    }

    /**
     * 编辑代理信息
     * @param int $id
     * @return \think\response\View|void
     */
    public function option($id = 0){
        if(!request()->isPost()){
            $info = \app\backman\model\Agent::where('id',$id)->find();
            $level = AgentLevel::where('parent_id',0)->select();
            if(!empty($level)){
                foreach ($level as $key=>$value){
                    $level[$key]['list'] = AgentLevel::where('parent_id',$value['id'])->select();
                }
            }
            $this->assign('level',$level);
            $this->assign('info',$info);
            return view();
        }else{
            $obj = new \app\backman\model\Agent();
            if($this->data['id']){
                // 编辑
                AdminLog($this->admin['id'],'修改代理【'.$this->data['name'].'】信息');
                $data['phone'] = $this->data['phone'];
                $data['name'] = $this->data['name'];
                $data['status'] = $this->data['status'];
                $data['level_id'] = $this->data['level_id'];
                $data['card_id'] = $this->data['card_id'];
                $data['weixin'] = $this->data['weixin'];
                $data['province'] = $this->data['provid'];
                $data['city'] = $this->data['cityid'];
                $data['area'] = $this->data['areaid'];
              $data['money'] = $this->data['money'];
                if($this->data['pwd']){
                    $data['pwd'] = md5($this->data['pwd']);
                }
                $state = $obj->allowField(true)->save($data,['id'=>$id]);
            }
            if($state){
                return $this->success('操作成功',url('index'));
            }else{
                return $this->error($obj->getError());
            }
        }
    }

    public function addlink(){

        
    }

    public function store(){
        $time = isset($_GET['time']) ? $_GET['time'] : date('Y/m/d H:i:s',strtotime('-1 month')).' - '.date('Y/m/d H:i:s');
        $this->assign('time',$time);
        return view();
    }

    public function charge(){

        $time = isset($_GET['time']) ? $_GET['time'] : date('Y/m/d H:i:s',strtotime('-1 month')).' - '.date('Y/m/d H:i:s');
        $key = isset($_GET['state']) ? $_GET['state'] : '';
        $this->assign('key',$key);
        $this->assign('time',$time);
        return view();
    }


    public function setting(){
        if(!request()->isPost()){
            $tpl_data = $this->config['agent'];
            $show_data = unserialize($tpl_data);
            $this->assign('info',$show_data);
            $list = AgentLevel::select();
            $cateObj = new \lib\Category(['id','parent_id','name','cname']);
            $relist = $cateObj->getTree($list);
            $this->assign('list',$relist);
            return view();
        }else{
            $obj = new Config();
            $updata['agent'] = serialize($this->data);
            $state = $obj->saveInfo($updata);
            if(!$state){
                return $this->error('保存失败');
            }else{
                AdminLog($this->admin['id'],'修改代理规则配置');
                return $this->success('保存成功');
            }
        }
    }

    public function level(){
        $list = AgentLevel::select();
        $cateObj = new \lib\Category(['id','parent_id','name','cname']);
        $relist = $cateObj->getTree($list);
        $this->assign('list',$relist);
        return view();
    }

    public function level_op($id = 0){
    	if(!request()->isPost()){
            $info = AgentLevel::get($id);
            $group = AgentLevel::where('parent_id','0')->select();
            $this->assign('group',$group);
            $this->assign('info',$info);
            return view();
        }else{
            $obj = new AgentLevel();
            if($this->data['id']){
                // 编辑
                AdminLog($this->admin['id'],'修改代理等级【'.$this->data['name'].'】信息');
                $state = $obj->saveData($this->data,'edit');
            }else{
                // 新增
                AdminLog($this->admin['id'],'新增代理等级【'.$this->data['name'].'】');
                $state = $obj->saveData($this->data);
            }
            if($state){
                return $this->success('操作成功',url('level'));
            }else{
                return $this->error($obj->getError());
            }
        }
    }

    /**
     * 编辑代理商品库存
     */
    public function goods($id = 0){
        if(!request()->isPost()){
            $goods = Goods::where('status',1)->select();
            if(!empty($goods)){
                //查询代理商品库存
                $goodsList = AgentStock::where('agent_id',$id)->select();
                foreach ($goods as $k=>$v){
                    $goodsNum = 0;
                    foreach ($goodsList as $key=>$value){
                        if($value['goods_id'] == $v['id']){
                            $goodsNum = $value['num'];
                        }
                    }
                    $goods[$k]['stock'] = $goodsNum;
                }
            }
            $this->assign('goods',$goods);
            $this->assign('id',$id);
            return view();
        }else{
            //保存数据
            $data = $this->request->param();
            $goods = $data['goods'];
            Db::startTrans();
            $user = \app\backman\model\Agent::where('id',$id)->field('openid')->find();
            $u = User::where('openid',$user['openid'])->field('uid')->find();
            if(!empty($goods)){
                foreach ($goods as $k=>$v){
                    if($v > 0){
                        $goodsList = AgentStock::where('agent_id',$id)->where('goods_id',$k)->field('id,goods_id,num')->find();
                        if(!empty($goodsList)){
                            //更新数据
                            $res = AgentStock::where('agent_id',$id)->where('goods_id',$k)->update(['num'=>$goodsList['num']+$v]);
                        }else{
                            //创建数据
                            $addData = [
                                'num'=>$v,
                                'agent_id'=>$id,
                                'created_at'=>date('Y-m-d H:i:s'),
                                'updated_at'=>date('Y-m-d H:i:s'),
                                'goods_id'=>$k,
                                'user_id'=>$u['uid']
                            ];
                            $res = AgentStock::create($addData);
                        }
                    }

                }
                Db::commit();
                return $this->success('操作成功',url('stock'));
            }else{
                return $this->error('操作失败');
            }
        }


    }

}