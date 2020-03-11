<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 订单费用逻辑层
 * +----------------------------------------------------------------------
 * DATE 2019-12-06 16:37
 * @package app\logic\user
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\order;

use app\logic\baseLogic;
use think\model\concern\SoftDelete;

class orderCostLogic extends baseLogic
{
    use SoftDelete;

    /**
     * @var string 数据表名
     */
    protected $name = 'order_cost';

    /**
     * @var string 数据表主键
     */
    protected $pk = 'id';

    /**
     * @var bool 是否开启字段时间戳字段
     */
    protected $autoWriteTimestamp = true;

    /**
     * @var string 默认软删除字段
     */
    protected $deleteTime = 'delete_time';

    /**
     * @var int 默认软删除时间
     */
    protected $defaultSoftDelete = 0;

    /**
     * 初始化
     * spreadLogic constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->autoObject['spread_id'] = $this->getSpreadId();
    }

    /**
     * 删除数据信息
     * @param int $spreadId 平台id
     * @param int $id 数据id
     * @param int $userId 用户id
     */
    public function deleteInfo(int $spreadId, int $id, int $userId)
    {

    }
}
