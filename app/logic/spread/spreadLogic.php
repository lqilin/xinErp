<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 平台信息逻辑层
 * +----------------------------------------------------------------------
 * DATE 2019-12-07 09:42
 * @package app\logic\spread
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\spread;

use app\logic\baseLogic;
use app\model\commonModel;
use app\model\spread\spreadModel;
use think\db\Query;
use think\model\concern\SoftDelete;

class spreadLogic extends baseLogic
{
    use SoftDelete;

    /**
     * @var string 数据表名
     */
    protected $name = 'spread';

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
    }

    /**
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchStateAttr(Query $query, $value, $data)
    {
        if (in_array($value, [spreadModel::STATE_OK, spreadModel::STATE_FORBIDDEN])) {
            $query->where('state', $value);
        }
    }

    /**
     * 关键词搜索器
     * @param Query $query
     * @param $value
     * @param $data
     */
    public function searchKeywordAttr(Query $query, $value)
    {
        if (!empty($value)) {
            if (isMobile($value)) {
                $query->where('contact_phone', $value);
            } elseif (isTel($value)) {
                $query->where('contact_tel', $value);
            } elseif (isEmail($value)) {
                $query->where('contact_email', $value);
            } else {
                $query->whereLike('spread_name|contact_address', "%$value%");
            }
        }
    }

    /**
     * 获取平台列表信息
     * @param array $param
     * @param int $page
     * @param int $size
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getSpreadList(array $param, int $page, int $size)
    {
        $result = [
            'page' => $page,
            'count' => $size,
            'last_page' => commonModel::DEFAULT_LAST_PAGE,
            'data' => [],
        ];
        $data = $this
            ->withSearch(['id', 'state', 'create_time', 'keyword'], $param)
            ->order('create_time DESC')
            ->paginate($size);
        $data->each(function ($item) {
            $item['spread_token'] = encodeId($item['id']);
            return $item;
        });
        $result['count'] = $data->count();
        $result['last_page'] = $data->lastPage();
        $result['data'] = $data->items();
        return $result;
    }

    /**
     * 删除数据信息
     * @param int $spreadId 平台id
     * @param int $id 数据id
     * @param int $userId 用户id
     */
    public function deleteInfo(int $spreadId, int $id, int $userId)
    {

    }
}