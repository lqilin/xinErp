<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 销售退货单逻辑层
 * +----------------------------------------------------------------------
 * DATE 2020-01-15 10:53
 * @package app\logic\order\group
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\order\group;

use app\library\interfaces\OrderInterface;
use app\logic\order\orderLogic;
use app\model\commonModel;

class orderTypeSaleRefundLogic extends orderLogic implements OrderInterface
{
    /**
     * 创建销售退货单
     * @param array $param
     * @return mixed|void
     */
    public function createOrder(array $param)
    {
        echo '创建销售退货单';exit;
    }

    /**
     * 销售退货单列表
     * @param array $param
     * @param int $page
     * @param int $size
     * @return mixed|void
     */
    public function orderList(array $param, int $page, int $size)
    {
        echo '销售退货单列表';exit;
    }

    /**
     * 获取销售退货单信息
     * @param array $param
     * @return mixed|void
     */
    public function getOrder(array $param)
    {
        echo '获取销售退货单信息';exit;
    }

    /**
     * 处理销售退货单
     * @param int $orderId
     * @param int $type
     * @return mixed|void
     */
    public function processOrder(int $orderId, int $type)
    {
        echo '处理销售退货单';exit;
    }
}