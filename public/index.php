<?php
//namespace think;
//
//define(BASE_PATH,dirname(dirname(__FILE__)));
//define(DS, DIRECTORY_SEPARATOR);
//
//// 加载基础文件
//require dirname(dirname(__FILE__)) . '/thinkphp/base.php';
//
//// 执行应用并响应
//Container::get('app')->path(dirname(dirname(__FILE__)).'/application')->bind('index')->run()->send();
namespace think;
// echo phpinfo();exit();
// 指定允许其他域名访问  
header('Access-Control-Allow-Origin:*');
// // 响应类型  
header('Access-Control-Allow-Methods:*');
// // 响应头设置  
header('Access-Control-Allow-Headers:*');

if(strtoupper($_SERVER['REQUEST_METHOD'])== 'OPTIONS'){
    exit;
}


// if (Request::isOptions()) { // 判断是否为OPTIONS请求
    
//     exit; //因为预检请求第一次是发送OPTIONS请求返回了响应头的内容，但没有返回响应实体response body内容。这个我们不处理业务逻辑，第二次接收的get或post等才是实质的请求返回我们才处理
// }

define("DIR_PATH", dirname(dirname(__FILE__)));

// 加载基础文件
require DIR_PATH . '/thinkphp/base.php';


// 执行应用并响应
Container::get('app')->run()->send();