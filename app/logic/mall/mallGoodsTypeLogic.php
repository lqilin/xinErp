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
use app\model\commonModel;
use think\facade\Log;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;
use think\model\relation\HasMany;
use think\model\relation\HasManyThrough;

class mallGoodsTypeLogic extends BaseLogic
{
    use SoftDelete;

    /**
     * @var string 数据表名
     */
    protected $name = 'mall_goods_type';

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
        'spec',
        'spec_item'
    ];

    /**
     * mallTypeLogic constructor.
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
     * @return HasMany 关联商品规格表
     */
    public function spec(): HasMany
    {
        return $this->hasMany(mallGoodsSpecLogic::class, 'type_id');
    }

    /**
     * @return HasManyThrough 远程关联规格子项表
     */
    public function specItem(): HasManyThrough
    {
        return $this->hasManyThrough(mallGoodsSpecItemLogic::class, mallGoodsSpecLogic::class, 'type_id', 'spec_id', 'id');
    }

    /**
     * 获取平台商品类型列表
     * @param array $param
     * @param int   $page
     * @param int   $size
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getTypeList(array $param, int $page, int $size)
    {
        $result = [
            'page' => $page,
            'last_page' => commonModel::DEFAULT_LAST_PAGE,
            'count' => commonModel::DEFAULT_COUNT,
            'data' => [],
        ];

        $data = $this
            ->with(['spec'])
            ->order('create_time DESC')
            ->paginate($size)
            ->each(function ($item) {
                if (!empty($item['spec'])) {
                    foreach ($item['spec'] as $k => $spec) {
                        $item['spec'][$k]['spec_item'] = $spec->specItem()->select();
                    }
                }
                return $item;
            });
        $result['count'] = $data->count();
        $result['last_page'] = $data->lastPage();
        $result['data'] = $data->items();
        return $result;
    }

    /**
     * 保存平台商品类型
     * @param array $param
     * @param array $spec
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveType(array $param, array $spec = [])
    {
        $typeInfo = $this->where('name', $param['name'])->find();
        $this->startTrans();
        if (empty($typeInfo)) {
            if (!$this->save(['name' => $param['name']])) {
                $this->rollback();
                recordError('保存平台商品类型失败，请稍后再试');
                return false;
            }
            $typeInfo = $this;
        }
        $oldDataIds = $typeInfo->spec()->column('id');
        $sameDataIds = array_intersect(array_column($spec, 'id'), $oldDataIds);
        $needDeleteDataIds = array_diff($oldDataIds, $sameDataIds);
        if (!empty($needDeleteDataIds)) {
            foreach ($needDeleteDataIds as $specId) {
                $specInfo = mallGoodsSpecLogic::getInstance()->find($specId);
                if (!empty($specInfo)) {
                    $needDeleteDealIds = $specInfo->specItem()->column('id');
                    if (!empty($needDeleteDealIds)) {
                        foreach ($needDeleteDealIds as $deleteId) {
                            $deleteItem = mallGoodsSpecItemLogic::getInstance()->find($deleteId);
                            if (!empty($deleteItem)) {
                                if (!$deleteItem->delete()) {
                                    $this->rollback();
                                    Log::notice('saveType is failed, 规格子项删除失败，请稍后再试', [$spec]);
                                    recordError('规格子项删除失败，请稍后再试');
                                    return false;
                                }
                            }
                        }
                    }
                }
            }
        }
        $specItemIds = [];
        $attachItemIds = [];
        if (!empty($spec)) {
            foreach ($spec as $item) {
                $item['type_id'] = $typeInfo->id;
                if (empty($item['id'])) {
                    if (!$itemInfo = mallGoodsSpecLogic::create($item, ['name', 'order', 'type_id', 'spread_id', 'is_upload_image', 'is_search', 'update_time'])) {
                        $this->rollback();
                        Log::notice('saveType is failed, 更新规格信息失败，请稍后再试', [$item]);
                        recordError('更新规格信息失败，请稍后再试');
                        return false;
                    }
                } else {
                    $itemInfo = mallGoodsSpecLogic::getInstance()->find($item['id']);
                    if (empty($itemInfo)) {
                        $this->rollback();
                        Log::notice('saveType is failed, 规格信息不存在或已被删除', [$item]);
                        recordError('规格信息不存在或已被删除');
                        return false;
                    }
                }
                $oldItemIds = $itemInfo->specItem()->column('id');
                if (!empty($item['item'])) {
                    foreach ($item['item'] as $specItem) {
                        $findItem = mallGoodsSpecItemLogic::getInstance()
                            ->where('spec_id', $itemInfo['id'])
                            ->where('item', $specItem['item'])
                            ->find();
                        if (empty($findItem)) {
                            if (!$specItemLogic = mallGoodsSpecItemLogic::getInstance()->create([
                                'item' => $specItem['item'],
                                'spec_id' => $itemInfo['id'],
                            ])) {
                                $this->rollback();
                                Log::notice('saveType is failed, 规格子项创建失败，请稍后再试', [$spec]);
                                recordError('规格子项创建失败，请稍后再试');
                                return false;
                            }
                            $specItemId = $specItemLogic['id'];
                        } else {
                            $specItemId = $findItem['id'];
                        }
                        if (!in_array($specItemId, $oldItemIds)) {
                            array_push($attachItemIds, $specItemId);
                        }
                        array_push($specItemIds, $specItemId);
                    }
                }
                $sameItemIds = array_intersect($oldItemIds, $specItemIds);
                $shouldDeleteItemIds = array_diff($oldItemIds, $sameItemIds);
                if (!empty($shouldDeleteItemIds)) {
                    foreach ($shouldDeleteItemIds as $deleteId) {
                        $deleteItem = mallGoodsSpecItemLogic::getInstance()->find($deleteId);
                        if (!empty($deleteItem)) {
                            if (!$deleteItem->delete()) {
                                $this->rollback();
                                Log::notice('saveType is failed, 规格子项删除失败，请稍后再试', [$spec]);
                                recordError('规格子项删除失败，请稍后再试');
                                return false;
                            }
                        }
                    }
                }
            }
        }
        $this->commit();
        return true;
    }
}