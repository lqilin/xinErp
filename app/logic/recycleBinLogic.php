<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 逻辑层
 * +----------------------------------------------------------------------
 * DATE 2019-12-09 15:36
 * @package app\logic
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic;

use app\model\authModel;
use app\model\commonModel;
use app\model\recycleBinModel;
use think\db\Query;
use think\facade\Db;
use think\model\concern\SoftDelete;

class recycleBinLogic extends baseLogic
{
    use SoftDelete;

    /**
     * @var string 数据表名
     */
    protected $name = 'recycle_bin';

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
     * recycleBinLogic constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->autoObject = [
            'spread_id' => $this->spreadId,
            'user_id' => $this->userId,
        ];
    }

    /**
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchStateAttr(Query $query, $value, $data)
    {
        if (in_array($value, [recycleBinModel::STATE_DAFT, recycleBinModel::STATE_RECOVER])) {
            $query->where('state', $value);
        }
    }

    /**
     * 保存回收站信息
     * @param array $param
     * @return bool
     */
    public function saveData(array $param): bool
    {
        if (empty($param['object_id']) || empty($param['table_name'])) {
            recordError('回收站数据异常');
            return false;
        }
        $tableName = getTableComment($param['table_name']);
        if (empty($tableName)) {
            recordError('表注释未填写');
            return false;
        }
        $saveData = [
            'object_id' => $param['object_id'] ?? 0,
            'table_name' => env('database.prefix', 'erp_') . $param['table_name'],
            'name' => $tableName . '-' . $param['object_id'],
            'zn_name' => $tableName,
        ];
        if (!$this->save($saveData)) {
            recordError('加入回收站失败');
            return false;
        }
        return true;
    }

    /**
     * 获取回收站列表信息
     * @param array $param
     * @param int $page
     * @param int $size
     * @param int $loginTerminal
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function recycleList(array $param, int $page, int $size)
    {
        $result = [
            'page' => $page,
            'count' => $size,
            'last_page' => commonModel::DEFAULT_LAST_PAGE,
            'data' => [],
        ];
        if (empty($param)) {
            return $result;
        }
        $data = $this
            ->withSearch(['table', 'state', 'shop_id', 'user_id', 'create_time', 'state'], $param)
            ->paginate($size);
        $result['count'] = $data->count();
        $result['last_page'] = $data->lastPage();
        $result['data'] = $data->items();
        return $result;
    }

    /**
     * 恢复回收站数据
     * @param int $id
     * @param $authInfo
     * @param int $loginTerminal
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function recoverData(int $id, $authInfo, int $loginTerminal)
    {
        if (empty($id) || empty($authInfo) || empty($loginTerminal)) {
            recordError('参数缺失');
            return false;
        }
        $info = $this->find($id);
        if (empty($info)) {
            recordError('回收站信息不存在');
            return false;
        }
        if ($info['state'] != recycleBinModel::STATE_DAFT) {
            recordError('收据已被恢复，不可重复恢复');
            return false;
        }
        if ($loginTerminal == authModel::AUTH_CUSTOM) {
            if ($info['spread_id'] != $authInfo['spread_id']) {
                recordError('操作失败，平台异常');
                return false;
            }
            if ($info['user_id'] != $authInfo['user_id']) {
                recordError('你不是本人无法恢复此数据');
                return false;
            }
        } elseif ($loginTerminal == authModel::AUTH_SHOP) {
            if ($info['spread_id'] != $authInfo['spread_id']) {
                recordError('操作失败，平台异常');
                return false;
            }
            if ($info['shop_id'] != $authInfo['shop_id']) {
                recordError('店铺信息异常，无法删除');
                return false;
            }
        } elseif ($loginTerminal == authModel::AUTH_SPREAD) {
            if ($info['spread_id'] != $authInfo['spread_id']) {
                recordError('操作失败，平台异常');
                return false;
            }
        }

        $data = Db::table($info->table_name)->where('id', $info->object_id)->find();
        if (empty($data)) {
            recordError('原始数据不存在，恢复失败');
            return false;
        }
        try {
            Db::table($info->table_name)->where('id', $info->object_id)->save(['delete_time' => 0, 'update_time' => time()]);
            //软删除操作已删除的数据
            $info->state = recycleBinModel::STATE_RECOVER;
            $info->save();
            //提交修改
            $this->commit();
        } catch (\Throwable $exception) {
            $this->rollback();
            recordError($exception->getMessage());
            return false;
        }
        return true;
    }

    /***
     * 删除数据信息
     * @param int $id
     * @param $authInfo
     * @param int $loginTerminal
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function deleteInfo(int $id, $authInfo, int $loginTerminal)
    {
        $info = $this->find($id);
        if (empty($info)) {
            recordError('回收站信息不存在');
            return false;
        }
        if ($loginTerminal == authModel::AUTH_CUSTOM) {
            if ($info['spread_id'] != $authInfo['spread_id']) {
                recordError('操作失败，平台异常');
                return false;
            }
            if ($info['user_id'] != $authInfo['user_id']) {
                recordError('你不是本人无法恢复此数据');
                return false;
            }
        } elseif ($loginTerminal == authModel::AUTH_SHOP) {
            if ($info['spread_id'] != $authInfo['spread_id']) {
                recordError('操作失败，平台异常');
                return false;
            }
            if ($info['shop_id'] != $authInfo['shop_id']) {
                recordError('店铺信息异常，无法删除');
                return false;
            }
        } elseif ($loginTerminal == authModel::AUTH_SPREAD) {
            if ($info['spread_id'] != $authInfo['spread_id']) {
                recordError('操作失败，平台异常');
                return false;
            }
        }

        $info->delete_time = time();
        if (!$info->save()) {
            recordError('删除失败，请稍后再试');
            return false;
        }
        return true;
    }
}