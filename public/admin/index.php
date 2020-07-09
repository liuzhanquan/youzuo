<?php
namespace think;

//define(BASE_PATH,dirname(dirname(dirname(__FILE__))));
//
//define(DS, DIRECTORY_SEPARATOR);
// 加载基础文件
require dirname(dirname(dirname(__FILE__))) . '/thinkphp/base.php';

require_once  dirname(dirname(dirname(__FILE__))) . '/vendor/qiniu/php-sdk/autoload.php';

// 执行应用并响应
Container::get('app')->path(dirname(dirname(dirname(__FILE__))).'/application')->bind('backman')->run()->send();
