<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 逻辑层
 * +----------------------------------------------------------------------
 * DATE 2020-03-02 22:21
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

class goodsSpecLogic extends BaseLogic
{
    use SoftDelete;

    /**
     * @var string 数据表名
     */
    protected $name = 'goods_spec';

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
     * 保存商品规格信息
     * @param goodsLogic $goods
     * @param array      $goodsItem
     * @param array      $itemImg
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveGoodsSpec(goodsLogic $goods, array $goodsItem, array $itemImg)
    {
        $oldSpecIds = $goods->spec()->column('id');
        $oldImgIds = $goods->specImg()->column('id');
        $this->startTrans();
        if (!empty($oldSpecIds)) {
            $currentSpecIds = array_column($goodsItem, 'id');
            $sameSpecIds = array_intersect($oldSpecIds, $currentSpecIds);
            $needDeleteSpecIds = array_diff($oldSpecIds, $sameSpecIds);
            if (!empty($needDeleteSpecIds)) {
                foreach ($needDeleteSpecIds as $needDeleteSpecId) {
                    $specItem = $this->find($needDeleteSpecId);
                    if (!empty($specItem)) {
                        if (!$specItem->delete()) {
                            $this->rollback();
                            Log::notice('saveGoodsSpec is failed, the reason is delete goods spec is failed', [$goods->toArray(), $specItem->toArray()]);
                            recordError('删除商品规格失败，请稍后再试');
                            return false;
                        }
                    }
                }
            }
        }
        if (!empty($oldImgIds)) {
            $currentImgIds = array_column($itemImg, 'id');
            $sameImgIds = array_intersect($oldImgIds, $currentImgIds);
            $needDeleteImgIds = array_diff($oldImgIds, $sameImgIds);
            if (!empty($needDeleteImgIds)) {
                foreach ($needDeleteImgIds as $needDeleteImgId) {
                    $specImg = goodsSpecImagesLogic::getInstance()->find($needDeleteImgId);
                    if (!empty($specImg)) {
                        if (!$specImg->delete()) {
                            $this->rollback();
                            Log::notice('saveGoodsSpec is failed, the reason is delete goods spec images is failed', [$goods->toArray(), $specImg->toArray()]);
                            recordError('删除商品规格项图片失败，请稍后再试');
                            return false;
                        }
                    }
                }
            }
        }
        if (!empty($goodsItem)) {
            if (!$goods->spec()->saveAll($goodsItem)) {
                $this->rollback();
                Log::notice('saveGoodsSpec is failed, the reason is save all goods spec is failed', [$goods->toArray(), $goodsItem]);
                recordError('保存商品规格失败，请稍后再试');
                return false;
            }
        }
        if (!empty($itemImg)) {
            if (!$goods->specImg()->saveAll($itemImg)) {
                $this->rollback();
                Log::notice('saveGoodsSpec is failed, the reason is save all goods spec img is failed', [$goods->toArray(), $itemImg]);
                recordError('保存商品规格项图片失败，请稍后再试');
                return false;
            }
        }
        $this->commit();
        return true;
    }
}