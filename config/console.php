<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
use app\command\Logic;
use app\command\Cache;
use app\command\order\Validate;
use app\command\order\GroupLogic;
return [
    // 指令定义
    'commands' => [
        'make:logic' => Logic::class,                   //自动生成逻辑层
        'make:cache' => Cache::class,                   //自动生成缓存层
        'order:validate' => Validate::class,            //自动生成订单模块验证层
        'order:groupLogic' => GroupLogic::class,        //自动生成订单逻辑层
    ],
];
