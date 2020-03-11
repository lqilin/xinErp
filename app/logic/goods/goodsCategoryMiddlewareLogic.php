<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 逻辑层
 * +----------------------------------------------------------------------
 * DATE 2020-01-21 15:16
 * @package app\logic\goods
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\goods;

use app\library\traits\erpAuthTrait;
use app\library\traits\erpModelEventTrait;
use app\library\traits\instanceTrait;
use app\logic\spread\spreadLogic;
use think\model\Pivot;
use think\model\relation\BelongsTo;

class goodsCategoryMiddlewareLogic extends Pivot
{
    use instanceTrait;
    use erpAuthTrait;
    use erpModelEventTrait;

    /**
     * @var string 数据表名
     */
    protected $name = 'goods_category_middleware';

    /**
     * @var string 数据表主键
     */
    protected $pk = 'id';

    /**
     * @var bool 是否开启字段时间戳字段
     */
    protected $autoWriteTimestamp = true;

    /**
     * @var array 关联表信息
     */
    protected $relevance = [
        'spread',
        'goods',
        'category',
    ];

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        if (isset(request()->authInfo)) $this->setAuthInfo(request()->authInfo);
        if (isset(request()->userInfo)) $this->setUserInfo(request()->userInfo);
        if (isset(request()->spreadInfo)) $this->setSpreadInfo(request()->spreadInfo);
        if (isset(request()->shopInfo)) $this->setShopInfo(request()->shopInfo);
        if (isset(request()->spreadId)) $this->setSpreadId(request()->spreadId);
        if (isset(request()->userId)) $this->setUserId(request()->userId);
        if (isset(request()->shopId)) $this->setShopId(request()->shopId);
        if (isset(request()->fromId)) $this->setFromId(request()->fromId);
        if (isset(request()->loginTerminal)) $this->setLoginTerminal(request()->loginTerminal);
        $this->autoObject['spread_id'] = $this->getSpreadId();
    }

    /**
     * @return BelongsTo 关联平台信息
     */
    public function spread(): BelongsTo
    {
        return $this->belongsTo(spreadLogic::class, 'category_id');
    }

    /**
     * @return BelongsTo 关联商品分类表
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(goodsCategoryLogic::class, 'category_id');
    }

    /**
     * @return BelongsTo 关联商品信息
     */
    public function goods(): BelongsTo
    {
        return $this->belongsTo(goodsLogic::class, 'goods_id');
    }
}