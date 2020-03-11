<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 逻辑层
 * +----------------------------------------------------------------------
 * DATE 2020-01-09 13:53
 * @package app\logic\user
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\user;

use app\logic\baseLogic;
use think\model\concern\SoftDelete;

class userActionLogLogic extends baseLogic
{
    use SoftDelete;

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
     * @var string 数据表名
     */
    protected $name = 'user_action_log';

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
}