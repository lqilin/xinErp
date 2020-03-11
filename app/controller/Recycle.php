<?php
declare (strict_types=1);

namespace app\controller;

use app\logic\recycleBinLogic;
use app\model\authModel;
use app\model\commonModel;
use app\model\recycleBinModel;
use app\Request;
use think\annotation\Route;
use think\annotation\route\Middleware;
use app\middleware\AllowOriginMiddleware;
use app\middleware\AuthTokenMiddleware;
use think\facade\Db;
use think\Paginator;

class Recycle extends Base
{
    /**
     * 回收站列表
     * @param Request $request
     * @Route("recycleList")
     * @throws \think\db\exception\DbException
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @see 权限：客户、店铺、平台、管理员
     * @example 成功示例：https://documenter.getpostman.com/view/6090259/SWECWaGL?version=latest#89ef2dd7-c5bd-491e-b755-6345c29b9886：
     * {
     *   "code": 1000,
     *   "msg": "获取成功",
     *   "data": {
     *       "page": 1,
     *       "count": 1,
     *       "last_page": 1,
     *       "data": [
     *               {
     *                   "id": 1,                    //信息id
     *                   "spread_id": 1,                //平台id
     *                   "object_id": 1,                //对象信息id
     *                   "table_name": "user",        //表名称
     *                   "name": "超级管理员",        //删除内容名称
     *                   "user_id": 1,                //操作用户id
     *                   "zn_name": "用户表",        //表名称中文
     *                   "state": 1,                    //状态：1回收站 2已恢复
     *                   "create_time": "2019-09-19 14:14:51",    //创建时间
     *                   "update_time": "2019-12-11 15:26:37",    //编辑时间
     *                   "delete_time": 0
     *               }
     *           ]
     *       }
     *   }
     */
    public function recycleList(Request $request)
    {
        $param = $request->only(['start_time', 'end_time', 'table']);
        $page = $request->param('page/d', commonModel::DEFAULT_PAGE);
        $size = $request->param('size/d', commonModel::DEFAULT_SIZE);
        if ($request->loginTerminal == authModel::AUTH_ADMIN) {
            $param['state'] = [recycleBinModel::STATE_DAFT, recycleBinModel::STATE_RECOVER];
        } elseif ($request->loginTerminal == authModel::AUTH_SPREAD) {
            $param['state'] = [recycleBinModel::STATE_DAFT, recycleBinModel::STATE_RECOVER];
            $param['spread_id'] = $request->spreadId;
        } elseif ($request->loginTerminal == authModel::AUTH_SHOP) {
            $param['state'] = recycleBinModel::STATE_DAFT;
            $param['spread_id'] = $request->spreadId;
            $param['shop_id'] = $request->shopId;
        } elseif ($request->loginTerminal == authModel::AUTH_CUSTOM) {
            $param['state'] = recycleBinModel::STATE_DAFT;
            $param['spread_id'] = $request->spreadId;
            $param['shop_id'] = $request->shopId;
            $param['user_id'] = $request->userId;
        }
        if (!empty($param['start_time']) && !empty($param['end_time'])) {
            $param['create_time'] = [$param['start_time'], $param['end_time'] . ' 23:59:59'];
        } else {
            if (!empty($param['start_time'])) {
                $param['create_time'] = [$param['start_time'], 0];
            } elseif (!empty($param['end_time'])) {
                $param['create_time'] = [0, $param['end_time'] . ' 23:59:59'];
            }
        }
        $data = recycleBinLogic::getInstance()->recycleList($param, $page, $size);
        apiSuccess('获取成功', $data);
    }

    /**
     * 恢复回收站数据
     * @param Request $request = [
     *          "id" => 1,      //回收站数据id
     * ]
     * @Route("recover")
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @see 权限：所有人
     * @see 权限：客户、店铺、平台、管理员
     * @example 成功示例：https://documenter.getpostman.com/view/6090259/SWECWaGL?version=latest#89ef2dd7-c5bd-491e-b755-6345c29b9886
     * {
     *   "code": 1000,
     *   "msg": "操作成功",
     *   "data": []
     *   }
     */
    public function recover(Request $request)
    {
        $id = $request->param('id/d', 0);
        if (!recycleBinLogic::getInstance()->recoverData($id, $request->authInfo, $request->loginTerminal)) {
            apiError(getLastError());
        }
        apiSuccess('操作成功');
    }

    /**
     * 删除回收站信息
     * @param Request $request = [
     *          "id" => 1,      //回收站数据id
     * ]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @see 权限：所有人
     * @Route("deleteRecycle")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @example 成功示例：https://documenter.getpostman.com/view/6090259/SWECWaGL?version=latest#89ef2dd7-c5bd-491e-b755-6345c29b9886
     * {
     *   "code": 1000,
     *   "msg": "操作成功",
     *   "data": []
     *   }
     */
    public function deleteRecycle(Request $request)
    {
        $id = $request->param('id/d', 0);
        if (!recycleBinLogic::getInstance()->deleteInfo($id, $request->authInfo, $request->loginTerminal)) {
            apiError(getLastError());
        }
        apiSuccess('操作成功');
    }

    /**
     * 所有表
     * @param Request $request
     * @see 权限：所有人
     * @Route("tables")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     */
    public function tables(Request $request)
    {
        $this->checkAuthority(authModel::AUTH_SPREAD + authModel::AUTH_ADMIN);
        $tables = Db::query('show table status');
        $size = $request->param('size/d', commonModel::DEFAULT_SIZE);
        $page = $request->param('page/d', commonModel::DEFAULT_PAGE);
        $data = Paginator::make($tables, $size, $page, count($tables), false);
        $result = [
            'page' => $data->currentPage(),
            'count' => $data->total(),
            'last_page' => $data->lastPage(),
            'data' => $data->getCollection(),
        ];
        apiPaginate($result);
    }
}
