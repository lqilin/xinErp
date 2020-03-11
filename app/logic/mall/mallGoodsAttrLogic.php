<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 逻辑层
 * +----------------------------------------------------------------------
 * DATE 2020-02-10 17:18
 * @package app\logic\mall
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\mall;

use app\logic\BaseLogic;
use app\logic\spread\spreadLogic;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;

class mallGoodsAttrLogic extends BaseLogic
{
    use SoftDelete;

    /**
     * @var string 数据表名
     */
    protected $name = 'mall_goods_attr';

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
     * @var array 关联数据表信息
     */
    protected $relevance = [
        'spread',
        'mall_goods_model',
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
     * @return BelongsTo 关联平台商品模型
     */
    public function mallGoodsModel(): BelongsTo
    {
        return $this->belongsTo(mallGoodsModelLogic::class, 'model_id');
    }
}