<?php
namespace app\common\services;
use app\common\model\Order;
use think\Db;

/**
 * Created by PhpStorm.
 * User: lyy
 * Date: 2020/7/9
 * Time: 16:52
 */



Class CodeLogInDb
{

	protected $model = '';
	protected $return
		= [
			'code' => 400,
			'msg'  => '',
			'data' => []
		];
	protected $user = [];
	protected $config
		= [
			'url' => "http://120.76.23.183:838",
			'key' => "YOjGScn9tZK2AcKG"
		];

	public function __construct($user)
	{
		$this->model = new Order();
		$this->user  = $user;
	}

	// 调用录入方法
	public function order($keyword)
	{
		if (!preg_match('/^[a-zA-Z0-9]+$/u', $keyword)) {
			$this->return['msg'] = '请输入正确的防伪码';
			return $this->return;
		}
		$check_log = $this->checkLog($keyword);
		if ($check_log) $this->CodeCheck($keyword);
		return $this->return;
	}

	// 防伪码查询流水号
	private function CodeCheck($keyword)
	{

		$qemCodeCheck = new QemCodeCheck($this->config['url'], $this->config['key']);
		$codeCheck    = $qemCodeCheck->codeCheck($keyword);
		if ($codeCheck['code'] == 400) {
			$this->return['msg'] = $codeCheck['msg'];
			return false;
		} else {
			return $this->orderData($keyword, $codeCheck['code']);
		}
	}

	// 查询订单是否已经录入
	private function checkLog($order_sn)
	{
		$find = $this->model->where('order_sn', $order_sn)->find();
		if ($find) {
			$this->return['data'] = $find;
			$this->return['code'] = 401;
			$this->return['msg']  = '核销失败，该防伪码已核销过';
			return false;
		} else {
			return true;
		}
	}

	// 设置订单信息
	private function orderData($order_sn, $code)
	{
		$detection = $this->detectionLog($code);
		if (!isset($detection['id']) || empty($detection)) {
			$this->return['msg'] = '防伪码未分配，请联系管理员';
			return false;
		}
		if ($detection['status'] != 1) {
			$this->return['msg'] = '配送的二维码被冻结，请联系管理员';
			return false;
		}
		$data = [
			'order_sn'     => $order_sn,
			'code'         => $code,
			'customer_id'  => $detection['customer_id'],
			'goods_id'     => $detection['goods_id'],
			'staff_id'     => $this->user['id'],
			'detection_id' => $detection['id'],
			'status'       => 1,
			'update_time'  => date("Y-m-d H:i:s"),
			'create_time'  => date("Y-m-d H:i:s")
		];

		Db::startTrans();
		try {
			$res              = $this->orderInDb($data);
			$data['id']       = $res;
			$data['goods_id'] = ['text' => $this->getGoodsTitle($data['goods_id'])];
			$this->return     = ["code" => 200, "msg" => '核销成功', "data" => $data];
			if ($detection['log_status'] == 2) $this->detectionEditStatus($detection['id']);
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			$this->return['msg'] = $e->getMessage();
			return false;
		}


	}

	// 获取流水号分配记录
	private function detectionLog($code)
	{
		return Db::name('detection')->where('start_num', '<=', $code)->where('end_num', '>=', $code)->find();
	}

	// 添加扫码记录
	private function orderInDb($data)
	{
		return $this->model->insertGetId($data);
	}

	// 修改配送码记录，添加录入记录
	private function detectionEditStatus($id)
	{
		return Db::name('detection')->where('id', $id)->update(['log_status' => 1, 'update_time' => date('Y-m-d H:i:s')]);
	}

	private function getGoodsTitle( $id ){
		return Db::name('goods')->where('id',$id)->value('title');
	}

}