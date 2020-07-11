<?php
namespace app\common\services;
/**
 * Created by PhpStorm.
 * User: lyy
 * Date: 2020/7/9
 * Time: 16:52
 */



Class QemCodeCheck{

	protected $key = '';
	protected $url = '';


	public function __construct( $url, $key )
	{
		$this->url = $url;
		$this->key = $key;
	}

	public function codeCheck( $code, $ip = 0 ){
		if( empty($code) ) return ['code'=>400,'msg'=>"请输入防伪码"];

		if( empty($ip) ){
			$ip = $_SERVER['SERVER_ADDR'];
			if( empty($ip) ) return ['code'=>400,'msg'=>"请输入ip"];
		}

		$md_key = md5( $code.''.$this->key );
		$url = $this->url.'?fwcode='.$code.'&ip='.$ip.'&key='.$md_key;
		$data = curl($url);
		$data = json_decode($data,true);

		if( !isset( $data['parameter']['ID'] ) ) return ['code'=>400,'msg'=>"请求失败"];
		if( empty( $data['parameter']['ID'] ) ) return ['code'=>400,'msg'=>"核销失败，该防伪码不存在"];
		switch( $data['result'] ){
			case 0:
				return ['code'=>400,'msg'=>"必要参数为空!"];
				break;
			case 1:
				return ['code'=>$data['result'],'code'=>$data['parameter']['ID']];
				break;
			case 2:
				return ['code'=>$data['result'],'code'=>$data['parameter']['ID']];
				break;
			case 3:
				return ['code'=>400,'msg'=>"错误，不存在!"];
				break;
			case 5:
				return ['code'=>400,'msg'=>"系统维护!"];
				break;
			case 6:
				return ['code'=>400,'msg'=>"IP被限制!"];
				break;
			case 7:
				return ['code'=>400,'msg'=>"接口秘钥错误!"];
				break;
			default:
				return ['code'=>400,'msg'=>"请求失败!"];
				break;

		}
		return ['code'=>$data['result'],'id'=>$data['parameter']['ID']];



	}



}


