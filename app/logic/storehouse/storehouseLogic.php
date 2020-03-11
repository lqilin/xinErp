<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 平台库存逻辑层
 * +----------------------------------------------------------------------
 * DATE 2019-12-06 16:37
 * @package app\logic\storehouse
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\storehouse;

use app\logic\baseLogic;
use app\model\commonModel;
use app\model\storehouse\storehouseModel;
use app\subscribe\storehouse\storehouseSubscribe;
use think\db\Query;
use think\model\concern\SoftDelete;

class storehouseLogic extends baseLogic
{
    use SoftDelete;

    protected $autoObject = [];

    /**
     * @var string 数据表名
     */
    protected $name = 'storehouse';

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
     * 关键词搜索器
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchKeywordAttr(Query $query, $value)
    {
        if (!empty($value)) {
            if (isMobile($value)) {
                $query->where('phone', $value);
            } elseif (isEmail($value)) {
                $query->where('email', $value);
            } else {
                $query->whereLike('storehouse_name', "%" . $value . "%");
            }
        }
    }

    /**
     * 默人仓库搜索器
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchIsDefaultAttr(Query $query, $value)
    {
        if (in_array($value, [storehouseModel::IS_DEFAULT, storehouseModel::NO_DEFAULT])) {
            $query->where('is_default', $value);
        }
    }

    /**
     * 获取仓库列表
     * @param array $param
     * @param int $page
     * @param int $size
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStoreHouse(array $param, int $page, int $size)
    {
        $result = [
            'page' => $page,
            'count' => $size,
            'last_page' => commonModel::DEFAULT_LAST_PAGE,
            'data' => [],
        ];
        $data = $this
            ->withSearch(['keyword', 'create_time', 'state', 'is_default'], $param)
            ->cache(true)->hidden(['spread_id'])
            ->page($page, $size)
            ->paginate($size);

        $result['data'] = $data->items();
        $result['count'] = $data->total();
        $result['last_page'] = $data->lastPage();
        return $result;
    }

    /**
     * 保存平台仓库信息
     * @param $param
     * @return bool
     */
    public function saveSpreadStoreHouse($param)
    {
        $saveData = [
            'storehouse_name' => $param['storehouse_name'] ?: '未知仓库信息' . round(1000, 9999),
            'address' => $param['address'] ?: '未知地址',
            'contact' => $param['contact'] ?: '未知联系人',
            'tel' => $param['tel'] ?: '',
            'phone' => $param['phone'] ?: '',
            'email' => $param['email'] ?: '',
            'remark' => $param['remark'] ?: '',
            'stock_size' => $param['stock_size'] ?: 0,
            'long' => $param['long'] ?: '',
            'lat' => $param['lat'] ?: '',
        ];
        $this->startTrans();
        if (empty($param['id'])) {
            $saveData['create_time'] = time();
            $saveData['opening_inventory'] = $param['opening_inventory'] ?: 0;
            $saveData['stock_count'] = $param['opening_inventory'] ?: 0;
            if (!$storehouse = $this->create($saveData)) {
                $this->rollback();
                recordError('操作失败，请稍后再试');
                return false;
            }
            $id = $storehouse['id'];
        } else {
            $saveData['id'] = $param['id'];
            if (!$this->save($saveData)) {
                $this->rollback();
                recordError('操作失败，请稍后再试');
                return false;
            }
            $id = $param['id'];
        }
        //如果设置当前的仓库为默认仓库，那么改变其他仓库为非默认状态
        if (!empty($param['is_default']) && $param['is_default'] == storehouseModel::IS_DEFAULT) {
            event(storehouseSubscribe::STOREHOUSE_SWITCH_DEFAULT, [$this->spreadId, $id]);
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
        $info = $this->cache(true)->find($id);
        if (empty($info)) {
            recordError('仓库信息不存在或已被删除');
            return false;
        }
        if (!$info->delete()) {
            recordError('删除失败，请稍后再试');
            return false;
        }
        return true;
    }
}
