<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 逻辑层
 * +----------------------------------------------------------------------
 * DATE 2019-12-26 00:22
 * @package app\logic\custom
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\custom;

use app\logic\baseLogic;
use app\logic\spread\spreadLogic;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;

class customCategoryLogic extends baseLogic
{
    use SoftDelete;

    /**
     * @var string 数据表名
     */
    protected $name = 'custom_category';

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
     * @return BelongsTo 关键平台表信息（所属）
     */
    public function spread(): BelongsTo
    {
        return $this->belongsTo(spreadLogic::class, 'spread_id');
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
            recordError('客户分类信息不存在或已被删除');
            return false;
        }
        if (!$info->delete()) {
            recordError('删除客户分类信息失败，请稍候再试');
            return false;
        }
        return true;
    }
}