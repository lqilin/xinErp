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
use app\logic\storehouse\storehouseLogic;
use app\logic\storehouse\storehousePositionLogic;
use app\logic\supplier\supplierLogic;
use app\model\commonModel;
use app\model\goods\goodsModel;
use app\model\supplier\supplierModel;
use app\subscribe\goods\goodsSubscribe;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;
use think\model\relation\BelongsToMany;
use think\model\relation\HasMany;

class goodsLogic extends BaseLogic
{
    use SoftDelete;

    /**
     * @var string 数据表名
     */
    protected $name = 'goods';

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
     * @var array 获取商品关联表中的那些信息
     */
    protected $relevance = [
        'spread',
        'supplier',
        'brand',
        'storehouse',
        'storehouse_position',
        'category',
        'keywords',
        'images',
        'spec',
        'attr',
        'spec_img',
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
        $this->getWithModel();
    }

    /**
     * @return \think\model\relation\BelongsTo 关联平台表（所属品牌）
     */
    public function spread(): BelongsTo
    {
        return $this->belongsTo(spreadLogic::class, 'spread_id');
    }

    /**
     * @return \think\model\relation\BelongsTo 关联供应商表（所属供应商）
     */
    public function supplier(): BelongsTo
    {
        return $this
            ->belongsTo(supplierLogic::class, 'supplier_id')
            ->where('state', supplierModel::STATE_BAN)
            ->visible(['name', 'mnemonic_code', 'address', 'tel', 'contact', 'phone', 'post_code', 'email']);
    }

    /**
     * @return \think\model\relation\belongsTo 关联品牌表（所属品牌）
     */
    public function brand(): BelongsTo
    {
        return $this
            ->belongsTo(goodsBrandLogic::class, 'brand_id');
    }

    /**
     * @return \think\model\relation\BelongsTo 关联仓库表（所属仓库）
     */
    public function storehouse(): BelongsTo
    {
        return $this
            ->belongsTo(storehouseLogic::class, 'storehouse_id');
    }

    /**
     * @return \think\model\relation\BelongsTo 关联所属仓位表（所属仓位）
     */
    public function storehousePosition(): BelongsTo
    {
        return $this
            ->belongsTo(storehousePositionLogic::class, 'storehouse_position_id');
    }

    /**
     * @return \think\model\relation\BelongsToMany 关联商品分类中间表
     */
    public function category(): BelongsToMany
    {
        return $this
            ->belongsToMany(goodsCategoryLogic::class, goodsCategoryMiddlewareLogic::class, 'category_id', 'goods_id')
            ->visible(['id', 'spread_id', 'parent_id', 'level', 'category_count', 'goods_count', 'category_name'])
            ->order('update_time asc');
    }

    /**
     * @return \think\model\relation\BelongsToMany 关联商品标签表
     */
    public function keywords(): BelongsToMany
    {
        return $this
            ->belongsToMany(goodsKeywordLogic::class, goodsKeywordMiddlewarePivotLogic::class, 'keyword_id', 'goods_id')
            ->visible(['id', 'spread_id', 'keyword', 'goods_count', 'category_name'])
            ->order('update_time asc');
    }

    /**
     * @return \think\model\relation\HasMany 关联商品图片表
     */
    public function images(): HasMany
    {
        return $this
            ->hasMany(goodsImagesLogic::class, 'goods_id')
            ->visible(['id', 'spread_id', 'goods_id', 'src', 'is_master'])
            ->order('is_master asc,update_time desc');
    }

    /**
     * @return \think\model\relation\HasMany 关联商品规格表
     */
    public function spec(): HasMany
    {
        return $this
            ->hasMany(goodsSpecLogic::class, 'goods_id')
            ->order('create_time asc');
    }

    /**
     * @return \think\model\relation\HasMany 关联商品属性表
     */
    public function attr(): HasMany
    {
        return $this
            ->hasMany(goodsAttrLogic::class, 'goods_id')
            ->order('create_time asc');
    }

    /**
     * @return \think\model\relation\HasMany 关联商品规格图片表
     */
    public function specImg(): HasMany
    {
        return $this
            ->hasMany(goodsSpecImagesLogic::class, 'goods_id', 'id')
            ->order('create_time asc');
    }

    /**
     * 获取商品列表
     * @author 张大宝的程序人生
     * @param array $param
     * @param int   $page
     * @param int   $size
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getGoodsList(array $param, int $page, int $size)
    {
        $result = [
            'page' => $page,
            'count' => $size,
            'last_page' => commonModel::DEFAULT_LAST_PAGE,
            'data' => [],
        ];
        $data = $this
            ->with($this->withModel)
            ->paginate($size);

        $result['data'] = $data->items();
        $result['count'] = $data->total();
        $result['last_page'] = $data->lastPage();
        return $result;
    }

    /**
     * 保存商品信息
     * @param array  $param       保存商品的参数
     * @param string $categoryIds 分类ID（以英文','隔开）
     * @param string $keywords    分类ID（以空格符隔开）
     * @param string $attrs       商品属性信息（json对象）
     * @param string $goodsImages 商品图片信息（json对象）
     * @param string $specItem    商品规格信息（json对象）
     * @param string $itemImg     商品规格图片信息（json对象）
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveGoods(array $param, string $categoryIds = '', string $keywords = '', string $attrs = '', string $goodsImages = '', string $specItem = '', string $itemImg = '')
    {
        $categories = !empty($categoryIds) ? explode(',', $categoryIds) : [];
        $keywordArr = !empty($keywords) ? explode(' ', $keywords) : [];
        $goodsImages = !empty($goodsImages) ? json_decode($goodsImages, true) : [];
        $specItem = !empty($specItem) ? json_decode($specItem, true) : [];
        $itemImg = !empty($itemImg) ? json_decode($itemImg, true) : [];
        $attrs = !empty($attrs) ? json_decode($attrs, true) : [];
        if (!empty($param['id'])) {
            return $this->editGoods($param, $categories, $keywordArr, $attrs, $goodsImages, $specItem, $itemImg);
        }
        return $this->addGoods($param, $categories, $keywordArr, $attrs, $goodsImages, $specItem, $itemImg);
    }

    /**
     * 新增商品信息
     * @param array $param       保存商品的参数
     * @param array $categories  商品关联分类信息
     * @param array $keywords    商品关联关键词信息
     * @param array $attrs       商品关联属性信息
     * @param array $goodsImages 商品图片信息
     * @param array $specItem    商品规格信息
     * @param array $itemImg     商品规格图片信息
     * @return bool
     */
    private function addGoods(array $param, array $categories, array $keywords, array $attrs = [], array $goodsImages = [], array $specItem = [], array $itemImg = []): bool
    {
        $this->startTrans();
        //新增商品基础信息
        if (!$goodsInfo = $this->create($param)) {
            $this->rollback();
            recordError('新增失败，请稍后再试');
            return false;
        }
        $this->commit();
        //保存商品信息后置操作
        event(goodsSubscribe::AFTER_SAVE_GOODS, [$goodsInfo['id'], $categories, $keywords, $attrs, $goodsImages, $specItem, $itemImg]);
        return true;
    }

    /**
     * 编辑商品信息
     * @param array $param       保存商品的参数
     * @param array $categories  商品关联分类信息
     * @param array $keywords    商品关联关键词信息
     * @param array $attrs       商品关联属性信息
     * @param array $goodsImages 商品图片信息
     * @param array $specItem    商品规格信息
     * @param array $itemImg     商品规格图片信息
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function editGoods(array $param, array $categories = [], array $keywords = [], array $attrs = [], array $goodsImages = [], array $specItem = [], array $itemImg = []): bool
    {
        $info = $this->where('id', $param['id'])->find();
        if (empty($info)) {
            recordError('商品不存在或已被删除');
            return false;
        }
        $this->startTrans();
        if (!$info->save($param)) {
            $this->rollback();
            recordError('保存失败，请稍后再试');
            return false;
        }
        $this->commit();
        //保存商品信息后置操作
        event(goodsSubscribe::AFTER_SAVE_GOODS, [$info['id'], $categories, $keywords, $attrs, $goodsImages, $specItem, $itemImg]);
        return true;
    }

    /**
     * 切换商品上下架状态
     * @param int $id 商品ID
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function upDownGoods(int $id): bool
    {
        $goodsInfo = $this->find($id);
        if (empty($goodsInfo)) {
            recordError('商品信息不存在或已被删除');
            return false;
        }
        if ($goodsInfo['state'] === goodsModel::STATE_UP) {
            $goodsInfo->state = goodsModel::STATE_DOWN;
        } else {
            $goodsInfo->state = goodsModel::STATE_UP;
        }
        if (!$goodsInfo->save()) {
            recordError('切换商品上下架状态失败，请稍后再试');
            return false;
        }
        return true;
    }

    /**
     * 切换商品上新品状态
     * @param int $id 商品ID
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function goodsNew(int $id): bool
    {
        $goodsInfo = $this->find($id);
        if (empty($goodsInfo)) {
            recordError('商品信息不存在或已被删除');
            return false;
        }
        if ($goodsInfo['is_hot'] === goodsModel::IS_HOT) {
            $goodsInfo->is_hot = goodsModel::NOT_HOT;
        } else {
            $goodsInfo->is_hot = goodsModel::IS_HOT;
        }
        if (!$goodsInfo->save()) {
            recordError('切换商品上新品状态失败，请稍后再试');
            return false;
        }
        return true;
    }

    /**
     * 切换商品上热销状态
     * @param int $id 商品ID
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function goodsHot(int $id): bool
    {
        $goodsInfo = $this->find($id);
        if (empty($goodsInfo)) {
            recordError('商品信息不存在或已被删除');
            return false;
        }
        if ($goodsInfo['is_hot'] === goodsModel::IS_HOT) {
            $goodsInfo->is_hot = goodsModel::NOT_HOT;
        } else {
            $goodsInfo->is_hot = goodsModel::IS_HOT;
        }
        if (!$goodsInfo->save()) {
            recordError('切换商品上热销状态失败，请稍后再试');
            return false;
        }
        return true;
    }

    /**
     * 切换商品上推荐状态
     * @param int $id 商品ID
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function goodsRecommend(int $id): bool
    {
        $goodsInfo = $this->find($id);
        if (empty($goodsInfo)) {
            recordError('商品信息不存在或已被删除');
            return false;
        }
        if ($goodsInfo['is_recommend'] === goodsModel::IS_RECOMMEND) {
            $goodsInfo->is_recommend = goodsModel::NOT_RECOMMEND;
        } else {
            $goodsInfo->is_recommend = goodsModel::IS_RECOMMEND;
        }
        if (!$goodsInfo->save()) {
            recordError('切换商品上推荐状态失败，请稍后再试');
            return false;
        }
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
        $goodsInfo = $this->find($id);
        if (empty($goodsInfo)) {
            recordError('商品信息不存在或已被删除');
            return false;
        }
        if (!$goodsInfo->delete()) {
            recordError('删除商品信息失败，请稍候再试');
            return false;
        }
        return true;
    }
}