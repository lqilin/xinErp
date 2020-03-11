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
use think\model\relation\HasMany;

class mallGoodsSpecLogic extends BaseLogic
{
    use SoftDelete;

    /**
     * @var string 数据表名
     */
    protected $name = 'mall_goods_spec';

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
        'type',
        'spec_item'
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
    public function spread()
    {
        return $this->belongsTo(spreadLogic::class, 'spread_id');
    }

    /**
     * @return BelongsTo 关联平台商品类型
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(mallGoodsTypeLogic::class, 'type_id');
    }

    /**
     * @return HasMany 关联规格子项表
     */
    public function specItem(): HasMany
    {
        return $this->hasMany(mallGoodsSpecItemLogic::class, 'spec_id');
    }
}