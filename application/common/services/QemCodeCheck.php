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
		if( empty( $data['parameter']['ID'] ) ) return ['code'=>400,'msg'=>"防伪码有误"];
		return ['code'=>$data['result'],'id'=>$data['parameter']['ID']];



	}



}


