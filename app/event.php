<?php

use app\subscribe\admin\adminSubscribe;
use app\subscribe\user\userSubscribe;
use app\subscribe\custom\customSubscribe;
use app\subscribe\shop\shopSubscribe;
use app\subscribe\shop\shopUserSubscribe;
use app\subscribe\spread\spreadUserSubscribe;
use app\subscribe\spread\spreadSubscribe;
use app\subscribe\storehouse\storehouseSubscribe;
use app\subscribe\order\orderSubscribe;
use app\subscribe\goods\goodsSubscribe;


// 事件定义文件
return [
    'bind' => [
    ],

    'listen' => [
        'AppInit' => [],
        'HttpRun' => [],
        'HttpEnd' => [],
        'LogLevel' => [],
        'LogWrite' => [],
        //切换默认仓库
        'StorehouseSwitchDefault' => [],
        //切换默认仓库仓位
        'StorehousePositionSwitchDefault' => [],
        //用户登录
        'UserLogin' => [],
    ],

    'subscribe' => [
        adminSubscribe::class,              //总后台相关事件
        userSubscribe::class,               //用户相关事件
        customSubscribe::class,             //客户相关事件
        shopSubscribe::class,               //店铺相关事件
        shopUserSubscribe::class,           //店铺用户相关事件
        spreadSubscribe::class,             //平台相关事件
        storehouseSubscribe::class,         //仓库相关事件
        spreadUserSubscribe::class,         //平台用户相关事件
        orderSubscribe::class,              //订单相关事件
        goodsSubscribe::class,              //添加商品事件
    ],
];
