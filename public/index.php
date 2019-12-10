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

define("DIR_PATH", dirname(dirname(__FILE__)));

// 加载基础文件
require DIR_PATH . '/thinkphp/base.php';


// 执行应用并响应
Container::get('app')->run()->send();
