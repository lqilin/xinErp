<?php
// +----------------------------------------------------------------------
// | 鑫erp 我能做的只是去做得更好
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2019 http://erp.chaolizi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 张大宝的程序人生
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;

require __DIR__ . '/../vendor/autoload.php';

define('DS', DIRECTORY_SEPARATOR);

// 执行HTTP应用并响应
$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);
