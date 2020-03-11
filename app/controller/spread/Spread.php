<?php
/**
 * 十贰进销存系统
 *
 * ==========================================================================
 * @link      http://erp.chaolizi.cn/
 * @license   http://erp.chaolizi.cn/license.html License
 * ==========================================================================
 *
 * @author    张大宝的程序人生 <1107842285@qq.com>
 *
 */
declare (strict_types=1);

namespace app\controller\spread;

use app\controller\Base;
use app\logic\spread\spreadClearFormLogic;
use app\logic\spread\spreadCostTypeLogic;
use app\model\authModel;
use app\model\commonModel;
use app\model\spread\spreadClearFormModel;
use app\model\spread\spreadCostTypeModel;
use app\Request;
use app\validate\spread\spreadClearFormValidate;
use app\validate\spread\spreadCostTypeValidate;
use think\annotation\Route;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use app\middleware\AllowOriginMiddleware;
use app\middleware\AuthTokenMiddleware;
use think\exception\ValidateException;

/**
 * @Group("spread")
 */
class Spread extends Base
{
    /**
     * 获取平台费用信息列表
     * @param Request $request = [
     *          'state' => 1,       //状态：1启用 2禁用
     * ]
     * @Route("costType")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @throws \think\db\exception\DbException
     * @example 成功示例：https://documenter.getpostman.com/view/6090259/SWECWaGL?version=latest#89ef2dd7-c5bd-491e-b755-6345c29b9886：
     * {
     *   "code": 1000,
     *   "msg": "获取成功",
     *   "data": {
     *       "page": 1,
     *       "count": 1,
     *       "last_page": 3,
     *       "data": [
     *               {
     *                   "id": 1,                //费用信息id
     *                   "cost_name": "房租",    //费用名称
     *                   "state": 1,                //状态：1正常 2禁用
     *                   "create_time": "2019-11-29 15:24:39",    //创建时间
     *                   "update_time": "2019-11-29 15:24:39",    //修改时间
     *                   "delete_time": 0                        //删除时间
     *               }
     *           ]
     *       }
     *   }
     */
    public function costType(Request $request)
    {
        $param = $request->only(['state', 'start_time', 'end_time']);
        $param['spread_id'] = $request->spreadId;
        $page = $request->param('page/d', commonModel::DEFAULT_PAGE);
        $size = $request->param('size/d', commonModel::DEFAULT_SIZE);
        if (!empty($param['start_time']) && !empty($param['end_time'])) {
            $param['create_time'] = [$param['start_time'], $param['end_time'] . ' 23:59:59'];
        } else {
            if (!empty($param['start_time'])) {
                $param['create_time'] = [$param['start_time'], 0];
            } elseif (!empty($param['end_time'])) {
                $param['create_time'] = [0, $param['end_time'] . ' 23:59:59'];
            }
        }
        $data = spreadCostTypeLogic::getInstance()->getSpreadCostType($param, $page, $size);
        apiSuccess('获取成功', $data);
    }

    /**
     *  保存平台费用信息
     * @param Request $request = [
     *      'id' => 1,              //费用信息id
     *      'spread_id' => 1,       //平台id
     *      'cost_name' => '附加费',//费用名称
     * ]
     * @Route("saveCostType", method="post")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @example 成功示例：https://documenter.getpostman.com/view/6090259/SWECWaGL?version=latest#89ef2dd7-c5bd-491e-b755-6345c29b9886
     * {
     *   "code": 1000,
     *   "msg": "操作成功",
     *   "data": []
     *   }
     */
    public function saveCostType(Request $request)
    {
        $this->checkAuthority(authModel::AUTH_SHOP + authModel::AUTH_SPREAD + authModel::AUTH_ADMIN);
        $param = $request->only(['id', 'cost_name'], 'post');
        if ($request->loginTerminal == authModel::AUTH_ADMIN) {
            $param['spread_id'] = $request->param('spread_id/d', 0);
        } else {
            $param['spread_id'] = $request->spreadId;
        }
        try {
            if (!empty($param['id'])) {
                validate(spreadCostTypeValidate::class)->scene('edit')->check($param);
            } else {
                validate(spreadCostTypeValidate::class)->scene('add')->check($param);
            }
        } catch (ValidateException $exception) {
            apiError($exception->getMessage());
        }
        if (!spreadCostTypeLogic::getInstance()->saveCostName($param)) {
            apiError(getLastError());
        }
        apiSuccess('操作成功');
    }

    /**
     * 开启（禁用）平台费用类型信息
     * @param Request $request = [
     *      'id' => 1,              //费用类型id
     * ]
     * @Route("switchCostType")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @example 成功示例：https://documenter.getpostman.com/view/6090259/SWECWaGL?version=latest#89ef2dd7-c5bd-491e-b755-6345c29b9886
     * {
     *   "code": 1000,
     *   "msg": "操作成功",
     *   "data": []
     *   }
     */
    public function switchCostType(Request $request)
    {
        $this->checkAuthority(authModel::AUTH_SPREAD + authModel::AUTH_ADMIN);
        $id = $request->param('id/d', 0);
        if (empty($id)) {
            apiError('参数缺失');
        }
        $where = [
            'id' => $id
        ];
        if ($request->loginTerminal == authModel::AUTH_SPREAD) {
            $where['spread_id'] = $request->spreadId;
        }
        $info = spreadCostTypeLogic::getInstance()->where($where)->find();
        if (empty($info)) {
            apiError('平台费用信息不存在或已被删除');
        }
        if ($info['state'] == spreadCostTypeModel::STATE_OK) {
            $info->state = spreadCostTypeModel::STATE_BAN;
        } else {
            $info->state = spreadCostTypeModel::STATE_OK;
        }
        if (!$info->save()) {
            apiError('操作失败，请稍后再试');
        }
        apiSuccess('操作成功');
    }

    /**
     * 删除平台费用信息
     * @param Request $request = [
     *      'id' => 1,              //费用信息id
     * ]
     * @Route("deleteCostType")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @example 成功示例：https://documenter.getpostman.com/view/6090259/SWECWaGL?version=latest#89ef2dd7-c5bd-491e-b755-6345c29b9886
     * {
     *   "code": 1000,
     *   "msg": "操作成功",
     *   "data": []
     *   }
     */
    public function deleteCostType(Request $request)
    {
        $this->checkAuthority(authModel::AUTH_SPREAD + authModel::AUTH_ADMIN);
        $id = $request->param('id/d', 0);
        if (empty($id)) {
            apiError('参数缺失');
        }
        if (!spreadCostTypeLogic::getInstance()->deleteInfo($id)) {
            apiError(getLastError());
        }
        apiSuccess('操作成功');
    }

    /**
     * 获取平台结算方式信息
     * @param Request $request = [
     *      'spread_id' => 1,       //平台id
     * ]
     * @Route("clearForm")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @throws \think\db\exception\DbException
     * @example 成功示例：https://documenter.getpostman.com/view/6090259/SWECWaGL?version=latest#89ef2dd7-c5bd-491e-b755-6345c29b9886：
     * {
     *   "code": 1000,
     *   "msg": "获取成功",
     *   "data": {
     *       "page": 1,             //当前页码数
     *       "count": 1,            //总计多少条
     *       "last_page": 1,        //最后页
     *       "data": [
     *               {
     *                   "id": 1,                               //结算方式id
     *                   "clearing_form": "线下结算",           //结算方式名称
     *                   "state": 1,                            //状态：1启用 2禁用
     *                   "create_time": "2019-12-13 13:50:40", //创建时间
     *                   "update_time": "2019-12-13 13:50:40", //编辑时间
     *                   "delete_time": 0                       //删除时间
     *               }
     *           ]
     *       }
     *   }
     */
    public function clearForm(Request $request)
    {
        $param = $request->only(['state']);
        if ($request->loginTerminal != authModel::AUTH_ADMIN) {
            $param['spread_id'] = $request->spreadId;
        }
        $page = $request->param('page/d', commonModel::DEFAULT_PAGE);
        $size = $request->param('size/d', commonModel::DEFAULT_SIZE);
        if (!empty($param['start_time']) && !empty($param['end_time'])) {
            $param['create_time'] = [$param['start_time'], $param['end_time'] . ' 23:59:59'];
        } else {
            if (!empty($param['start_time'])) {
                $param['create_time'] = [$param['start_time'], 0];
            } elseif (!empty($param['end_time'])) {
                $param['create_time'] = [0, $param['end_time'] . ' 23:59:59'];
            }
        }
        $data = spreadClearFormLogic::getInstance()->getSpreadClearForm($param, $page, $size);
        apiSuccess('获取成功', $data);
    }

    /**
     * 保存结算方式信息
     * @param Request $request = [
     *      'id' => 1,              //费用信息id
     *      'spread_id' => 1,       //平台id
     *      'clearing_form' => '线上结算',//结算方式
     * ]
     * @Route("saveClearForm", method="post")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @example 成功示例：https://documenter.getpostman.com/view/6090259/SWECWaGL?version=latest#89ef2dd7-c5bd-491e-b755-6345c29b9886
     * {
     *   "code": 1000,
     *   "msg": "操作成功",
     *   "data": []
     *   }
     */
    public function saveClearForm(Request $request)
    {
        $this->checkAuthority(authModel::AUTH_SHOP + authModel::AUTH_SPREAD + authModel::AUTH_ADMIN);
        $param = $request->only(['id', 'clearing_form'], 'post');
        if ($request->loginTerminal == authModel::AUTH_ADMIN) {
            $param['spread_id'] = $request->param('spread_id/d', 0);
        } else {
            $param['spread_id'] = $request->spreadId;
        }
        try {
            if (!empty($param['id'])) {
                validate(spreadClearFormValidate::class)->scene('edit')->check($param);
            } else {
                validate(spreadClearFormValidate::class)->scene('add')->check($param);
            }
        } catch (ValidateException $exception) {
            apiError($exception->getMessage());
        }
        if (!spreadClearFormLogic::getInstance()->saveClear($param)) {
            apiError(getLastError());
        }
        apiSuccess('操作成功');
    }

    /**
     * 开启（禁用）平台结算类型信息
     * @param Request $request = [
     *      'id' => 1,              //结算信息id
     * ]
     * @Route("switchClearForm")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @example 成功示例：https://documenter.getpostman.com/view/6090259/SWECWaGL?version=latest#89ef2dd7-c5bd-491e-b755-6345c29b9886
     * {
     *   "code": 1000,
     *   "msg": "操作成功",
     *   "data": []
     *   }
     */
    public function switchClearForm(Request $request)
    {
        $this->checkAuthority(authModel::AUTH_SPREAD + authModel::AUTH_ADMIN);
        $id = $request->param('id/d', 0);
        if (empty($id)) {
            apiError('参数缺失');
        }
        $where = [
            'id' => $id
        ];
        if ($request->loginTerminal == authModel::AUTH_SPREAD) {
            $where['spread_id'] = $request->spreadId;
        }
        $info = spreadClearFormLogic::getInstance()->where($where)->find();
        if (empty($info)) {
            apiError('结算信息不存在或已被删除');
        }
        if ($info['state'] == spreadClearFormModel::STATE_OK) {
            $info->state = spreadClearFormModel::STATE_BAN;
        } else {
            $info->state = spreadClearFormModel::STATE_OK;
        }
        if (!$info->save()) {
            apiError('操作失败，请稍后再试');
        }
        apiSuccess('操作成功');
    }

    /**
     * 删除平台结算类型信息
     * @param Request $request = [
     *          'id' => 1,          //结算类型id
     * ]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @see 权限：平台、总部
     * @Route("deleteClearForm")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @example 成功示例：https://documenter.getpostman.com/view/6090259/SWECWaGL?version=latest#89ef2dd7-c5bd-491e-b755-6345c29b9886
     * {
     *   "code": 1000,
     *   "msg": "操作成功",
     *   "data": []
     *   }
     */
    public function deleteClearForm(Request $request)
    {
        $this->checkAuthority(authModel::AUTH_SPREAD + authModel::AUTH_ADMIN);
        $id = $request->param('id/d', 0);
        if (empty($id)) {
            apiError('参数缺失');
        }
        if (!spreadClearFormLogic::getInstance()->deleteInfo($id)) {
            apiError(getLastError());
        }
        apiSuccess('操作成功');
    }


}
