<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 逻辑层
 * +----------------------------------------------------------------------
 * DATE 2020-01-02 16:05
 * @package app\logic
 * project shier-erp管理系统
 */
declare (strict_types = 1);

namespace app\logic;

use think\model\concern\SoftDelete;

class regionLogic extends baseLogic
{
    use SoftDelete;

    /**
     * @var string 数据表名
     */
    protected $name = 'region';

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