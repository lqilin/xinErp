<?php
namespace app\library\interfaces;

/**
 * 单据相关接口类
 * Interface OrderInterface
 * @package app\admin\interfaces
 */
interface OrderInterface
{
    /**
     * 创建订单
     * @param array $param
     * @return mixed
     */
    public function createOrder(array $param);

    /**
     * 获取订单列表信息
     * @param array $where 搜索条件
     * @param int $page
     * @param int $size
     * @return mixed
     */
    public function orderList(array $where, int $page, int $size);

    /**
     * 获取订单信息
     * @param array $where 搜索条件
     * @return mixed
     */
    public function getOrder(array $where);

    /**
     * 处理订单
     * @param int $orderId 订单id
     * @param int $type 操作类型
     * @return mixed
     */
    public function processOrder(int $orderId, int $type);
}