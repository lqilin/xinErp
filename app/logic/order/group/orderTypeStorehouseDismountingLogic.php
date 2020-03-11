<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 拆装单逻辑层
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

class orderTypeStorehouseDismountingLogic extends orderLogic implements OrderInterface
{
    /**
     * 创建拆装单
     * @param array $param
     * @return mixed|void
     */
    public function createOrder(array $param)
    {
        echo '创建拆装单';exit;
    }

    /**
     * 拆装单列表
     * @param array $param
     * @param int $page
     * @param int $size
     * @return mixed|void
     */
    public function orderList(array $param, int $page, int $size)
    {
        echo '拆装单列表';exit;
    }

    /**
     * 获取拆装单信息
     * @param array $param
     * @return mixed|void
     */
    public function getOrder(array $param)
    {
        echo '获取拆装单信息';exit;
    }

    /**
     * 处理拆装单
     * @param int $orderId
     * @param int $type
     * @return mixed|void
     */
    public function processOrder(int $orderId, int $type)
    {
        echo '处理拆装单';exit;
    }
}