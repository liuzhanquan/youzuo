<?php
/**
 * Created by PhpStorm.
 * User: LHG
 * Date: 2019/8/8
 * Time: 16:12
 */

namespace app\api\controller\v1;
use app\api\controller\Api;
use app\common\model\Bank as BankModel;
use hg\ServerResponse;
use hg\Code;

class Bank extends Api
{
    /**
     * 银行卡
     * @param BankModel $bank
     * @return \think\response\Json
     */
    public function index(BankModel $bank){
        try{
            $Address = $bank->select();
            return json(['StatusCode'=>20000,'message'=>'请求成功','data'=>['data'=>$Address]]);
        }catch (\Exception $exception){
            ServerResponse::message(Code::CODE_INTERNAL_ERROR,$exception->getMessage());
        }
    }
}