<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 逻辑层
 * +----------------------------------------------------------------------
 * DATE 2020-02-03 16:57
 * @package app\logic\goods
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\goods;

use app\logic\spread\spreadLogic;
use think\model\relation\BelongsTo;

class goodsKeywordMiddlewarePivotLogic extends goodsKeywordMiddlewareLogic
{
    /**
     * @var string 数据表名
     */
    protected $name = 'goods_keyword_middleware';

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
        'keyword',
        'goods',
    ];

    /**
     * 初始化
     * goodsLogic constructor.
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
     * @return BelongsTo 关联关键词信息
     */
    public function keyword(): BelongsTo
    {
        return $this->belongsTo(goodsKeywordLogic::class, 'keyword_id');
    }

    /**
     * @return BelongsTo 管理商品信息
     */
    public function goods()
    {
        return $this->belongsTo(goodsLogic::class, 'goods_id');
    }
}