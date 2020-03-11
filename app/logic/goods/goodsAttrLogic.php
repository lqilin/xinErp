<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 逻辑层
 * +----------------------------------------------------------------------
 * DATE 2020-02-10 17:18
 * @package app\logic\goods
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\goods;

use app\logic\BaseLogic;
use app\logic\spread\spreadLogic;
use think\facade\Log;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;

class goodsAttrLogic extends BaseLogic
{
    use SoftDelete;

    /**
     * @var string 数据表名
     */
    protected $name = 'goods_attr';

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
        'goods',
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
     * @return BelongsTo 关联商品信息
     */
    public function goods(): BelongsTo
    {
        return $this->belongsTo(goodsLogic::class, 'goods_id');
    }

    /**
     * 保存商品属性
     * @param goodsLogic $goods
     * @param array      $attrs
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveGoodsAttr(goodsLogic $goods, array $attrs): bool
    {
        $oldDataIds = $goods->attr()->column('id');
        if (!empty($oldDataIds)) {
            $currentDataIds = array_column($attrs, 'id');
            $sameDataIds = array_intersect($oldDataIds, $currentDataIds);
            $needDeleteDataIds = array_diff($oldDataIds, $sameDataIds);
            if (!empty($needDeleteDataIds)) {
                foreach ($needDeleteDataIds as $needDeleteDataId) {
                    $attrItem = $this->find($needDeleteDataId);
                    if (!empty($attrItem)) {
                        if (!$attrItem->delete()) {
                            $this->rollback();
                            Log::notice('saveGoodsAttr is failed, the reason is delete goods attr is failed', [$goods->toArray(), $attrItem->toArray()]);
                            recordError('删除商品属性失败，请稍后再试');
                            return false;
                        }
                    }
                }
            }
        }
        if (!empty($attrs)) {
            if (!$goods->attr()->saveAll($attrs)) {
                $this->rollback();
                Log::notice('saveGoodsAttr is failed, the reason is save all goods attr is failed', [$goods->toArray(), $attrs]);
                recordError('保存商品属性失败，请稍后再试');
                return false;
            }
        }
        $this->commit();
        return true;
    }
}