<?php
declare (strict_types = 1);

namespace app\controller;

use app\logic\goods\goodsCategoryLogic;
use app\logic\goods\goodsLogic;
use app\middleware\AllowOriginMiddleware;
use app\middleware\AuthTokenMiddleware;
use think\annotation\route\Middleware;
use think\annotation\Route;

class Test extends Base
{
    /**
     * @return bool
     * @Route("testAddKeywords")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function testAddKeywords()
    {
        $goods = goodsLogic::getInstance()->find(22);
        if (!goodsCategoryLogic::getInstance()->saveGoodsCategory($goods, [1,5,7,8])) {
            apiError(getLastError());
        }
        apiSuccess('操作成功');
    }
}
