<?php
namespace app\library\repositories;

use app\library\exceptions\ErpException;
use app\library\traits\instanceTrait;
use app\model\codeModel;
use app\Request;

class orderRepository
{
    use instanceTrait;

    /**
     * @var object 订单对象
     */
    protected $order;

    /**
     * orderRepository constructor.
     * @param Request $request
     * @throws ErpException
     */
    public function __construct(Request $request)
    {
        $typeList = config('erp.order_type');
        $type = $request->param('order_type/d', 0);
        if (!isset($typeList[$type]['class'])) {
           throw new ErpException('订单类型错误', codeModel::ERROR);
        }
        $this->order = $typeList[$type]['class']::getInstance();
    }

    /**
     * 创建订单
     * @param array $param
     * @return mixed
     */
    public function createOrder(array $param)
    {
        return $this->order->createOrder($param);
    }

    /**
     * 订单列表
     * @param array $param
     * @param int $page
     * @param int $size
     * @return mixed
     */
    public function orderList(array $param, int $page, int $size)
    {
        return $this->order->orderList($param, $page, $size);
    }

    /**
     * 获取订单信息
     * @param array $param
     * @return mixed
     */
    public function getOrder(array $param)
    {
        return $this->order->getOrder($param);
    }

    /**
     * 处理订单
     * @param int $orderId
     * @param int $type
     * @return mixed
     */
    public function processOrder(int $orderId, int $type)
    {
        return $this->order->processOrder($orderId, $type);
    }
}