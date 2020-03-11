<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 逻辑层
 * +----------------------------------------------------------------------
 * DATE 2020-02-21 15:01
 * @package app\logic\mall
 * project shier-erp管理系统
 */
declare (strict_types = 1);

namespace app\logic\mall;

use app\logic\BaseLogic;
use app\logic\spread\spreadLogic;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;

class mallGoodsSpecItemLogic extends BaseLogic
{
    use SoftDelete;

    /**
     * @var string 数据表名
     */
    protected $name = 'mall_goods_spec_item';

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
     * @var array 关联表信息
     */
    protected $relevance = [
        'spread',
        'spec'
    ];

    /**
     * 初始化
     * spreadLogic constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->autoObject['spread_id'] = $this->getSpreadId();
        $this->setWithModel();
    }

    /**
     * @return BelongsTo 关联平台信息
     */
    public function spread(): BelongsTo
    {
        return $this->belongsTo(spreadLogic::class, 'spread_id');
    }

    /**
     * @return BelongsTo 关联平台规格信息
     */
    public function spec(): BelongsTo
    {
        return $this->belongsTo(mallGoodsSpecLogic::class, 'spec_id');
    }
}