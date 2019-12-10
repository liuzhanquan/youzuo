<?php
namespace app\index\controller;
use app\common\exception\BaseException;
use app\api\model\User as UserModel;

class Api extends \think\Controller{


	const JSON_SUCCESS_STATUS = 1;
    const JSON_ERROR_STATUS = 0;
	
    public function _initialize(){

    }
    
    /**
     * 获取当前用户信息
     * @param bool $is_force
     * @return UserModel|bool|null
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    protected function getUser($is_force = true)
    {
        if (!$token = $this->request->param('token')) {
            $is_force && $this->throwError('缺少必要的参数：token', -1);
            return false;
        }
        if (!$user = UserModel::getUser($token)) {
            $is_force && $this->throwError('没有找到用户信息', -1);
            return false;
        }
        return $user;
    }
    
    /**
     * 输出错误信息
     * @param int $code
     * @param $msg
     * @throws BaseException
     */
    protected function throwError($msg, $code = 0)
    {
        throw new BaseException(['code' => $code, 'msg' => $msg]);
    }

    /**
     * 返回封装后的 API 数据到客户端
     * @param int $code
     * @param string $msg
     * @param array $data
     * @return array
     */
    protected function renderJson($code = self::JSON_SUCCESS_STATUS, $msg = '', $data = [])
    {
        return compact('code', 'msg', 'url', 'data');
    }

    /**
     * 返回操作成功json
     * @param string $msg
     * @param array $data
     * @return array
     */
    protected function renderSuccess($data = [], $msg = 'success')
    {
        return $this->renderJson(self::JSON_SUCCESS_STATUS, $msg, $data);
    }

    /**
     * 返回操作失败json
     * @param string $msg
     * @param array $data
     * @return array
     */
    protected function renderError($msg = 'error', $data = [])
    {

        return $this->renderJson(self::JSON_ERROR_STATUS, $msg, $data);
    }

    /**
     * 获取post数据 (数组)
     * @param $key
     * @return mixed
     */
    protected function postData($key)
    {
        return $this->request->post($key . '/a');
    }

}
