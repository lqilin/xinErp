<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 逻辑层
 * +----------------------------------------------------------------------
 * DATE 2020-02-21 15:00
 * @package app\logic\mall
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\mall;

use app\logic\BaseLogic;
use app\logic\spread\spreadLogic;
use app\model\commonModel;
use think\db\Query;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;
use think\model\relation\HasMany;

class mallGoodsModelLogic extends BaseLogic
{
    use SoftDelete;

    /**
     * @var string 数据表名
     */
    protected $name = 'mall_goods_model';

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
        'attrs',
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
     * @return HasMany 关联平台属性表
     */
    public function attrs(): HasMany
    {
        return $this->hasMany(mallGoodsAttrLogic::class, 'model_id', 'id');
    }

    /**
     * 关键词搜索器
     * @param Query $query
     * @param       $value
     * @param       $data
     */
    public function searchKeywordAttr(Query $query, $value, $data)
    {
        if (!empty($value)) {
            $query->whereLike('model_name', "%$value%");
        }
    }

    /**
     * 创建时间检索
     * @param Query $query
     * @param       $value
     * @param       $data
     */
    public function searchCreateTimeAttr(Query $query, $value, $data)
    {
        if (!empty($data['start_time']) && !empty($data['end_time'])) {
            $query->whereBetweenTime('create_time', $data['start_time'], $data['end_time']);
        } else {
            if (!empty($data['start_time'])) {
                $query->whereTime('create_time', '>=', $data['start_time']);
            } elseif (!empty($data['end_time'])) {
                $query->whereTime('create_time', '<=', $data['end_time']);
            }
        }
    }

    /**
     * 获取平台商品模型列表
     * @param array $param
     * @param int   $page
     * @param int   $size
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getMallGoodsModelList(array $param, int $page, int $size)
    {
        $result = [
            'page' => $page,
            'last_page' => commonModel::DEFAULT_LAST_PAGE,
            'count' => commonModel::DEFAULT_COUNT,
            'data' => [],
        ];

        $data = $this
            ->with(['attrs'])
            ->withSearch(['state', 'create_time', 'keyword'], $param)
            ->order('create_time ASC')
            ->paginate($size);

        $result['count'] = $data->count();
        $result['last_page'] = $data->lastPage();
        $result['data'] = $data->items();
        return $result;
    }

    /**
     * 保存平台商品模型
     * @param array  $param
     * @param string $attrs
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveModel(array $param, string $attrs): bool
    {
        $attrs = !empty($attrs) ? (array)json_decode($attrs, true) : [];
        if (!empty($param['id'])) {
            if (!$this->editModel($param, $attrs)) {
                return false;
            }
        } else {
            if (!$this->addModel($param, $attrs)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 添加平台商品模型
     * @param array $param
     * @param array $attrs
     * @return bool
     */
    public function addModel(array $param, array $attrs)
    {
        $this->startTrans();
        if (!$this->save($param)) {
            $this->rollback();
            recordError('保存失败，请稍后再试');
            return false;
        }
        if (!empty($attrs)) {
            if (!$this->attrs()->data($attrs)->saveAll($attrs)) {
                $this->rollback();
                recordError('保存失败，请稍后再试');
                return false;
            }
        }
        $this->commit();
        return true;
    }

    /**
     * 编辑平台商品模型
     * @param array $param
     * @param array $attrs
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function editModel(array $param, array $attrs)
    {
        $info = $this->find($param['id']);
        if (empty($info)) {
            recordError('模型不存在或已被删除');
            return false;
        }
        $this->startTrans();
        if (!$info->save($param)) {
            $this->rollback();
            recordError('保存失败，请稍后再试');
            return false;
        }
        if (!empty($attrs)) {
            $oldAttrIds = $info->attrs()->column('id');
            $sameAttrIds = array_intersect(array_column($attrs, 'id'), $oldAttrIds);
            $needDeleteAttrIds = array_diff($oldAttrIds, $sameAttrIds);
            if (!empty($needDeleteAttrIds)) {
                foreach ($needDeleteAttrIds as $attrId) {
                    $attrInfo = mallGoodsAttrLogic::getInstance()->find($attrId);
                    if (!empty($attrInfo)) {
                        if (!$attrInfo->delete()) {
                            $this->rollback();
                            recordError('解除属性信息失败，请稍后再试');
                            return false;
                        }
                    }
                }
            }
            if (!$info->attrs()->saveAll($attrs)) {
                $this->rollback();
                recordError('保存失败，请稍后再试');
                return false;
            }
        }
        $this->commit();
        return true;
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