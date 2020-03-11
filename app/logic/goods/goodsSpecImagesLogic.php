<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 逻辑层
 * +----------------------------------------------------------------------
 * DATE 2020-03-02 22:38
 * @package app\logic\goods
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\goods;

use app\logic\BaseLogic;
use app\logic\mall\mallGoodsSpecItemLogic;
use app\logic\spread\spreadLogic;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;

class goodsSpecImagesLogic extends BaseLogic
{
    use SoftDelete;

    /**
     * @var string 数据表名
     */
    protected $name = 'goods_spec_images';

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

    protected $relevance = [
        'spread',
        'spec_item',
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
     * @return BelongsTo 关联规格箱信息
     */
    public function specItem(): BelongsTo
    {
        return $this->belongsTo(mallGoodsSpecItemLogic::class, 'spec_item_id');
    }

    /**
     * @return BelongsTo 关联商品信息
     */
    public function goods(): BelongsTo
    {
        return $this->belongsTo(goodsLogic::class, 'goods_id');
    }

    /**
     * 删除数据信息
     * @param int $id 数据ID
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function deleteInfo(int $id): bool
    {
        $info = $this->find($id);
        if (empty($info)) {
            recordError('信息不存在或已被删除');
            return false;
        }
        if (!$info->delete()) {
            recordError('删除信息失败，请稍候再试');
            return false;
        }
        return true;
    }
}