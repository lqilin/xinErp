<?php
declare (strict_types=1);

namespace app\subscribe\storehouse;

use app\logic\storehouse\storehouseLogic;
use app\logic\storehouse\storehousePositionLogic;
use app\model\storehouse\storehouseModel;
use app\model\storehouse\storehousePositionModel;
use think\db\Query;
use think\Exception;
use think\facade\Log;

/**
 * +----------------------------------------------------------------------
 * | Description: 仓库处理时间
 * +----------------------------------------------------------------------
 */
class storehouseSubscribe
{
    /**
     * 事件名称:切换默认仓库
     */
    const STOREHOUSE_SWITCH_DEFAULT = 'StorehouseSwitchDefault';

    /**
     * 事件名称:切换默认仓库仓位
     */
    const STOREHOUSE_POSITION_SWITCH_DEFAULT = 'StorehousePositionSwitchDefault';


    public function handle()
    {
    }

    /**
     * 切换默认仓库
     * @param $event
     * @return bool
     */
    public function onStorehouseSwitchDefault($event)
    {
        [$spreadId, $id] = $event;
        try {
            $update = ['is_default' => storehouseModel::NO_DEFAULT, 'update_time' => time()];
            storehouseLogic::getInstance()->update($update, function (Query $query) use ($spreadId, $id) {
                $query->where('spread_id', $spreadId);
                $query->whereNotIn('id', $id);
            });
        } catch (\Exception $exception) {
            Log::error('设置默认仓库失败，错误原因：' . $exception->getMessage());
            return false;
        }
        Log::info('onStorehouseSwitchDefault 设置默认仓库成功', [$spreadId, $id]);
        return true;
    }

    /**
     * 切换默认仓库仓位
     * @param $event
     * @return bool
     */
    public function onStorehousePositionSwitchDefault($event)
    {
        [$spreadId, $storehouseId, $id] = $event;
        $where = [
            'spread_id' => $spreadId,
            'storehouse_id' => $storehouseId,
            'id' => ['neq', $id],
        ];
        try {
            storehousePositionLogic::getInstance()->update(['is_default' => storehousePositionModel::NO_DEFAULT, 'update_time' => time()], $where);
        } catch (\Exception $exception) {
            Log::error('设置默认仓库仓位失败，错误原因：' . $exception->getMessage());
            return false;
        }
        Log::info('onStorehousePositionSwitchDefault 设置默认仓库仓位操作成功', [$spreadId, $storehouseId, $id]);
        return true;
    }
}
