<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 逻辑层
 * +----------------------------------------------------------------------
 * DATE 2020-03-02 16:04
 * @package app\logic\goods
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\goods;

use app\logic\BaseLogic;
use app\logic\spread\spreadLogic;
use app\model\goods\goodsImagesModel;
use think\facade\Log;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;

class goodsImagesLogic extends BaseLogic
{
    use SoftDelete;
    /**
     * @var string 数据表名
     */
    protected $name = 'goods_images';

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
     * 保存商品橱窗
     * @param goodsLogic $goods
     * @param array      $images
     * @return bool
     * @throws \think\db\exception\DbException
     */
    public function saveGoodsImages(goodsLogic $goods, array $images = [])
    {
        $oldImagesIds = $goods->images()->column('id');
        if (!empty($goods['thumb'])) {
            array_unshift($images, ['src' => $goods['thumb'], 'is_master' => goodsImagesModel::IS_MASTER]);
        }
        $this->startTrans();
        if (!empty($oldImagesIds)) {
            $currentImagesIds = array_column($images, 'id');
            $sameImagesIds = array_intersect($oldImagesIds, $currentImagesIds);
            $needDeleteImagesIds = array_diff($oldImagesIds, $sameImagesIds);
            if (!empty($needDeleteImagesIds)) {
                foreach ($needDeleteImagesIds as $needDeleteId) {
                    $imageItem = $this->find($needDeleteId);
                    if (!empty($imageItem)) {
                        if (!$imageItem->delete()) {
                            $this->rollback();
                            Log::notice('saveGoodsImages is failed, the reason is delete goods images is failed', [$goods->toArray(), $imageItem->toArray()]);
                            recordError('删除商品橱窗失败，请稍后再试');
                            return false;
                        }
                    }
                }
            }
        }
        if (!empty($images)) {
            if (!$goods->images()->saveAll($images)) {
                $this->rollback();
                Log::notice('saveGoodsImages is failed, the reason is save all goods images is failed', [$goods->toArray()]);
                recordError('保存商品橱窗失败，请稍后再试');
                return false;
            }
        }
        $this->commit();
        return true;
    }
}