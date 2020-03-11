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
use app\model\commonModel;
use think\db\Query;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;
use think\model\relation\HasMany;

class goodsBrandLogic extends BaseLogic
{
    use SoftDelete;

    /**
     * @var string 数据表名
     */
    protected $name = 'goods_brand';

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
     * @var array 关联表信息
     */
    protected $relevance = ['spread'];

    /**
     * @return BelongsTo 关联平台信息
     */
    public function spread(): BelongsTo
    {
        return $this->belongsTo(spreadLogic::class, 'spread_id');
    }

    /**
     * @return HasMany 关联商品信息表
     */
    public function goods(): HasMany
    {
        return $this->hasMany(goodsLogic::class, 'brand_id');
    }

    /**
     * 关键词搜素器
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchKeywordAttr(Query $query, $value)
    {
        if (!empty($value)) {
            $query->whereLike('brand_name', '%' . $value . '%');
        }
    }

    /**
     * @param array $param
     * @param int   $page
     * @param int   $size
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getBrandList(array $param, int $page, int $size)
    {
        $result = [
            'page' => $page,
            'last_page' => commonModel::DEFAULT_LAST_PAGE,
            'count' => commonModel::DEFAULT_COUNT,
            'data' => [],
        ];
        $data = $this
            ->withSearch(['id', 'state', 'keyword', 'create_time'], $param)
            ->order('create_time DESC')
            ->paginate($size);
        $result['count'] = $data->count();
        $result['last_page'] = $data->lastPage();
        $result['data'] = $data->items();
        return $result;
    }

    /**
     * 获取品牌信息
     * @param $param
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getBrand($param)
    {
        $info = $this->withSearch(['id', 'keyword', 'create_time'])->where($param)->find();
        return $info;
    }

    /**
     * 保存品牌信息
     * @param $param
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveBrand($param)
    {
        if (empty($param['id'])) {
            if (!$this->create($param)) {
                recordError('新增失败，请稍候再试');
                return true;
            }
        } else {
            $info = $this->find($param['id']);
            if (empty($info)) {
                recordError('品牌信息不存在或已被删除');
                return false;
            }
            if (!$info->save($param)) {
                recordError('保存失败，请稍候再试');
                return false;
            }
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
    public function deleteInfo(int $id): bool
    {
        $info = $this->find($id);
        if (empty($info)) {
            recordError('品牌信息不存在或已被删除');
            return false;
        }
        if (!$info->delete()) {
            recordError('删除失败，请稍后再试');
            return false;
        }
        return true;
    }
}