<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 库存调整单逻辑层
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

class orderTypeStorehouseStockChangeLogic extends orderLogic implements OrderInterface
{
    /**
     * 创建库存调整单
     * @param array $param
     * @return mixed|void
     */
    public function createOrder(array $param)
    {
        echo '创建库存调整单';exit;
    }

    /**
     * 库存调整单列表
     * @param array $param
     * @param int $page
     * @param int $size
     * @return mixed|void
     */
    public function orderList(array $param, int $page, int $size)
    {
        echo '库存调整单列表';exit;
    }

    /**
     * 获取库存调整单信息
     * @param array $param
     * @return mixed|void
     */
    public function getOrder(array $param)
    {
        echo '获取库存调整单信息';exit;
    }

    /**
     * 处理库存调整单
     * @param int $orderId
     * @param int $type
     * @return mixed|void
     */
    public function processOrder(int $orderId, int $type)
    {
        echo '处理库存调整单';exit;
    }
}