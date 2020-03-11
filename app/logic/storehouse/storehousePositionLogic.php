<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 仓库仓位逻辑层
 * +----------------------------------------------------------------------
 * DATE 2019-12-06 16:37
 * @package app\logic\storehouse
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\storehouse;

use app\logic\baseLogic;
use app\model\commonModel;
use app\model\storehouse\storehousePositionModel;
use app\subscribe\storehouse\storehouseSubscribe;
use think\db\Query;
use think\model\concern\SoftDelete;

class storehousePositionLogic extends baseLogic
{
    use SoftDelete;

    /**
     * @var string 数据表名
     */
    protected $name = 'storehouse_position';

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
                $query->whereLike('position_name', "%" . $value . "%");
            }
        }
    }

    /**
     * 仓库搜索器
     * @param Query $query
     * @param $value
     * @param $data
     */
    public function searchStorehouseIdAttr(Query $query, $value)
    {
        if (!empty($value)) {
            $query->where('storehouse_id', $value);
        }
    }

    /**
     * 状态搜索器
     * @param Query $query
     * @param $value
     * @param $data
     */
    public function searchStateAttr(Query $query, $value, $data)
    {
        if (in_array($value, [storehousePositionModel::STATE_OK, storehousePositionModel::STATE_BAN])) {
            $query->where('state', $value);
        }
    }

    /**
     * 获取仓库仓位信息
     * @param array $param
     * @param int $page
     * @param int $size
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStorehousePosition(array $param, int $page, int $size): array
    {
        $result = [
            'page' => $page,
            'count' => $size,
            'last_page' => commonModel::DEFAULT_LAST_PAGE,
            'data' => [],
        ];

        $data = $this->withSearch(['id', 'storehouse', 'keyword', 'create_time', 'state'], $param)
            ->hidden(['spread_id'])
            ->page($page, $size)
            ->paginate($size);

        $result['count'] = $data->total();
        $result['data'] = $data->items();
        $result['last_page'] = $data->lastPage();
        return $result;
    }

    /**
     * 保存仓库仓位信息
     * @param array $param
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveSpreadStoreHousePosition(array $param): bool
    {
        $saveData = [
            'storehouse_id' => $param['storehouse_id'] ?: 0,
            'spread_id' => $param['spread_id'] ?: 0,
            'position_name' => $param['position_name'] ?: '未知仓位' . round(1000, 9999),
            'address' => $param['address'] ?: '未知地址',
            'contact' => $param['contact'] ?: '未知联系人',
            'phone' => $param['phone'] ?: '',
            'email' => $param['email'] ?: '',
            'remark' => $param['remark'] ?: '',
            'update_time' => time(),
        ];
        $storehouse = storehouseLogic::getInstance()->find($param['storehouse_id']);
        if (empty($storehouse)) {
            recordError('仓库信息不存在或已被删除');
            return false;
        }
        if (empty($param['contact']) && empty($param['email']) && empty($param['phone'])) {
            $saveData['contact'] = $storehouse['contact'];
            $saveData['email'] = $storehouse['email'];
            $saveData['phone'] = $storehouse['phone'];
        }
        $this->startTrans();
        if (empty($param['id'])) {
            $saveData['create_time'] = time();
            if (!$id = $this->insert($saveData)) {
                $this->rollback();
                recordError('操作失败，请稍后再试');
                return false;
            }
        } else {
            $saveData['id'] = $param['id'];
            if (!$this->update($saveData, ['id' => $param['id']])) {
                $this->rollback();
                recordError('操作失败，请稍后再试');
                return false;
            }
            $id = $param['id'];
        }
        //如果设置当前的仓库为默认仓库，那么改变其他仓库为非默认状态
        if (!empty($param['is_default']) && $param['is_default'] == storehousePositionModel::IS_DEFAULT) {
            event(storehouseSubscribe::STOREHOUSE_POSITION_SWITCH_DEFAULT, [$param['spread_id'], $param['storehouse_id'], $id]);
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
    public function deleteInfo(int $id): bool
    {
        $info = $this->find($id);
        if (empty($info)) {
            recordError('仓位信息不存在或已被删除');
            return false;
        }
        if (!$info->delete()) {
            recordError('删除失败，请稍后再试');
            return false;
        }
        return true;
    }
}
