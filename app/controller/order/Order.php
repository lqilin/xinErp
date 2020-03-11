<?php
/**
 * 十贰进销存系统
 *
 * ==========================================================================
 * @link      http://erp.chaolizi.cn/
 * @license   http://erp.chaolizi.cn/license.html License
 * ==========================================================================
 *
 * @author    张大宝的程序人生 <1107842285@qq.com>
 *
 */
declare (strict_types=1);

namespace app\controller\order;

use app\controller\Base;
use app\library\repositories\orderRepository;
use app\Request;
use think\annotation\Route;
use think\annotation\route\Middleware;
use app\middleware\AllowOriginMiddleware;
use app\middleware\AuthTokenMiddleware;

class Order extends Base
{
    /**
     *  创建订单
     * @param Request $request
     * @param orderRepository $orderRepository
     * @Route("createOrder")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @see checkAuthority() 接口权限：客户、店铺、平台、总后台
     */
    public function createOrder(Request $request, orderRepository $orderRepository)
    {
        if (!$order = $orderRepository->createOrder($request->post())) {
            apiError(getLastError());
        }
        list($orderId) = $order;
        apiSuccess('订单创建成功', $orderId);
    }
}
