<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 逻辑层
 * +----------------------------------------------------------------------
 * DATE 2020-01-21 15:15
 * @package app\logic\goods
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\goods;

use app\logic\BaseLogic;
use app\logic\spread\spreadLogic;
use think\facade\Db;
use think\facade\Log;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;
use think\model\relation\BelongsToMany;

class goodsCategoryLogic extends BaseLogic
{
    use SoftDelete;

    /**
     * @var string 数据表名
     */
    protected $name = 'goods_category';

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
     * @return BelongsToMany 关联商品分类中间表
     */
    public function goods(): BelongsToMany
    {
        return $this->belongsToMany(goodsLogic::class, goodsCategoryMiddlewareLogic::class, 'goods_id', 'category_id');
    }

    /**
     * 保存商品分类信息
     * @param goodsLogic $goods
     * @param array      $category
     * @return bool
     * @throws \think\db\exception\DbException
     */
    public function saveGoodsCategory(goodsLogic $goods, array $category)
    {
        $oldDataIds = [];
        $oldData = $goods->category()->select();
        if (!empty($oldData)) {
            $oldDataIds = array_column($oldData->toArray(), 'id');
        }
        $this->startTrans();
        if (!empty($category)) {
            $sameDataIds = array_intersect($category, $oldDataIds);
            $needDeleteDataIds = array_diff($oldDataIds, $sameDataIds);
            $newDataIds = array_diff($category, $sameDataIds);
            if (!empty($needDeleteDataIds)) {
                if (!$goods->category()->detach($needDeleteDataIds)) {
                    $this->rollback();
                    recordError('解除分类信息失败，请稍后再试');
                    return false;
                }
                if (!goodsCategoryLogic::getInstance()->where('id', 'in', $needDeleteDataIds)->save(['goods_count' => Db::raw('goods_count - 1')])) {
                    $this->rollback();
                    Log::notice('saveGoods saveGoodsCategory is failed, 减少商品分类商品数量失败');
                    recordError('减少商品分类商品数量失败');
                    return false;
                }
            }
            if (!empty($newDataIds)) {
                if (!$goods->category()->attach($newDataIds)) {
                    $this->rollback();
                    Log::notice('saveGoods saveGoodsCategory is failed, 附加分类信息失败，请稍后再试');
                    recordError('附加分类信息失败，请稍后再试');
                    return false;
                }
                if (!goodsCategoryLogic::getInstance()->where('id', 'in', $newDataIds)->save(['goods_count' => Db::raw('goods_count + 1')])) {
                    $this->rollback();
                    Log::notice('saveGoods saveGoodsCategory is failed, 增加商品分类商品数量失败');
                    recordError('增加商品分类商品数量失败');
                    return false;
                }
            }
        }
        $this->commit();
        return true;
    }

    /**
     * 删除数据信息
     * @param int $id 数据id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function deleteInfo(int $id)
    {
        $info = $this->find($id);
        if (empty($info)) {
            recordError('分类信息不存在或已被删除');
            return false;
        }
        if (!$info->delete()) {
            recordError('删除分类信息失败，请稍候再试');
            return false;
        }
        return true;
    }
}