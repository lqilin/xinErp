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

namespace app\controller\storehouse;

use app\controller\Base;
use app\logic\storehouse\storehouseLogic;
use app\logic\storehouse\storehousePositionLogic;
use app\model\authModel;
use app\model\commonModel;
use app\model\storehouse\storehouseModel;
use app\model\storehouse\storehousePositionModel;
use app\Request;
use app\validate\storehouse\storehousePositionValidate;
use app\validate\storehouse\storehouseValidate;
use think\annotation\Route;
use think\annotation\route\Middleware;
use app\middleware\AllowOriginMiddleware;
use app\middleware\AuthTokenMiddleware;
use think\exception\ValidateException;

class Storehouse extends Base
{
    /**
     * 获取仓库列表信息
     * @param Request $request = [
     *      'keyword' => '撒旦教解散',       //搜索关键词（手机号，邮箱，仓库名称）
     *      'start_time' => '2019-01-01',    //搜索开始时间
     *      'end_time' => '2019-02-02',      //搜索结算时间
     *      'state' => 1,                    //状态: 1启用 2禁用
     * ]
     * @Route("storehouse")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @see 权限：客户、店铺、平台、总后台
     * @example 成功示例：https://documenter.getpostman.com/view/6090259/SWECWaGL?version=latest#89ef2dd7-c5bd-491e-b755-6345c29b9886
     */
    public function storehouse(Request $request)
    {
        $param = $request->only(['keyword', 'start_time', 'end_time', 'state']);
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
        $data = storehouseLogic::getInstance()->getStoreHouse($param, $page, $size);
        apiSuccess('获取成功', $data);
    }

    /**
     * 保存仓库信息
     * @param Request $request = [
     *          'id' => 1,          //仓库信息id
     *          'storehouse_name' => 'dsa',//仓库信息名称
     *          'address' => '成都',  //仓库地址
     *          'contact' => '一',    //联系人
     *          'email' => '110@qq.com',//联系人邮箱
     *          'tel' => '028-3306',    //联系电话
     *          'phone' => '135555555555',//手机号码
     *          'is_default' => 1,  //是否是默认仓库1默认 2不是默认
     * ]
     * @Route("saveStorehouse", method="post")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @see 权限：客户、店铺、平台、总后台
     * @example 成功示例：https://documenter.getpostman.com/view/6090259/SWECWaGL?version=latest#89ef2dd7-c5bd-491e-b755-6345c29b9886
     */
    public function saveStorehouse(Request $request)
    {
        $param = $request->only(['id', 'storehouse_name', 'address', 'contact', 'email', 'tel', 'phone', 'is_default', 'remark', 'opening_inventory', 'stock_size', 'long', 'lat'], 'post');
        try {
            if (!empty($param['id'])) {
                validate(storehouseValidate::class)->scene('edit')->check($param);
            } else {
                validate(storehouseValidate::class)->scene('add')->check($param);
            }
        } catch (ValidateException $exception) {
            apiError($exception->getMessage());
        }
        if (!storehouseLogic::getInstance()->saveSpreadStoreHouse($param)) {
            apiError(getLastError());
        }
        apiSuccess('操作成功');
    }

    /**
     * 启用（禁用）仓库信息
     * @param Request $request = [
     *          'id' => 1,          //仓库信息id
     * ]
     * @Route("switchStorehouse")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @see 权限：平台、总后台
     * @example 成功示例：https://documenter.getpostman.com/view/6090259/SWECWaGL?version=latest#89ef2dd7-c5bd-491e-b755-6345c29b9886
     */
    public function switchStorehouse(Request $request)
    {
        $this->checkAuthority(authModel::AUTH_SPREAD + authModel::AUTH_ADMIN);
        $id = $request->param('id/d', 0);
        if (empty($id)) {
            apiError('参数缺失');
        }
        $info = storehouseLogic::getInstance()->find($id);
        if (empty($info)) {
            apiError('仓库信息不存在或已被删除');
        }
        switch ($info['state']) {
            case storehouseModel::STATE_BAN:
                $info->state = storehouseModel::STATE_OK;
                break;
            default:
                $info->state = storehouseModel::STATE_BAN;
        }
        if (!$info->save()) {
            apiError('操作失败，请稍后再试');
        }
        apiSuccess('操作成功');
    }

    /**
     * 删除仓库信息
     * @param Request $request = [
     *          'id' => 1,          //仓库信息id
     * ]
     * @Route("deleteStorehouse")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @see 权限：平台、总后台
     * @example 成功示例：https://documenter.getpostman.com/view/6090259/SWECWaGL?version=latest#89ef2dd7-c5bd-491e-b755-6345c29b9886
     */
    public function deleteStorehouse(Request $request)
    {
        $this->checkAuthority(authModel::AUTH_SPREAD + authModel::AUTH_ADMIN);
        $id = $request->param('id/d', 0);
        if (empty($id)) {
            apiError('参数缺失');
        }
        if (!storehouseLogic::getInstance()->deleteInfo($id)) {
            apiError(getLastError());
        }
        apiSuccess('操作成功');
    }

    /**
     * 获取仓库仓位列表
     * @param Request $request = [
     *          'spread_id' => 1,                //平台id
     *          'storehouse_id' => 1,            //所属仓库
     *          'keyword' => '撒旦教解散',       //搜索关键词（手机号，邮箱，仓库名称）
     *          'start_time' => '2019-01-01',    //搜索开始时间
     *          'end_time' => '2019-02-02',      //搜索结算时间
     *          'state' => 1,                    //状态: 1启用 2禁用
     * ]
     * @Route("storehousePosition")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @see 权限：客户、店铺、平台、总后台
     * @example 成功示例：https://documenter.getpostman.com/view/6090259/SWECWaGL?version=latest#89ef2dd7-c5bd-491e-b755-6345c29b9886
     * {
     *   "code": 1000,
     *   "msg": "操作成功",
     *   "data": []
     *   }
     */
    public function storehousePosition(Request $request)
    {
        $param = $request->only(['keyword', 'storehouse_id', 'start_time', 'end_time', 'state']);
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
        $data = storehousePositionLogic::getInstance()->getStorehousePosition($param, $page, $size);
        apiSuccess('获取成功', $data);
    }

    /**
     *  保存仓库仓位信息
     * @param Request $request = [
     *          'id' => 1,          //仓位信息id
     *          'storehouse_id' => 1,   //所属仓库id
     *          'position_name' => 'dsa',//仓位信息名称
     *          'address' => '成都',  //仓库地址
     *          'contact' => '一',    //联系人
     *          'email' => '110@qq.com',//联系人邮箱
     *          'phone' => '135555555555',//手机号码
     *          'is_default' => 1,  //是否是默认仓库1默认 2不是默认
     * ];
     * @Route("saveStorehousePosition", method="post")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @see 权限：平台、总后台
     * @example 成功示例：https://documenter.getpostman.com/view/6090259/SWECWaGL?version=latest#89ef2dd7-c5bd-491e-b755-6345c29b9886
     * {
     *   "code": 1000,
     *   "msg": "操作成功",
     *   "data": []
     *   }
     */
    public function saveStorehousePosition(Request $request)
    {
        $this->checkAuthority(authModel::AUTH_SPREAD + authModel::AUTH_ADMIN);
        $param = $request->only(['id', 'storehouse_id', 'position_name', 'address', 'contact', 'email', 'phone', 'is_default', 'remark'], 'post');
        if ($request->loginTerminal == authModel::AUTH_ADMIN) {
            $param['spread_id'] = $request->param('spread_id/d', 0);
        } else {
            $param['spread_id'] = $request->spreadId;
        }
        try {
            if (!empty($param['id'])) {
                validate(storehousePositionValidate::class)->scene('edit')->check($param);
            } else {
                validate(storehousePositionValidate::class)->scene('add')->check($param);
            }
        } catch (ValidateException $exception) {
            apiError($exception->getMessage());
        }
        if (!storehousePositionLogic::getInstance()->saveSpreadStoreHousePosition($param)) {
            apiError($exception->getMessage());
        }
        apiSuccess('操作成功');
    }

    /**
     * 开启（禁用）仓库仓位信息
     * @param Request $request = [
     *      'id' => 1,              //仓位信息id
     * ]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @see 权限：平台、总后台
     * @Route("switchStorehousePosition")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @example 成功示例：https://documenter.getpostman.com/view/6090259/SWECWaGL?version=latest#89ef2dd7-c5bd-491e-b755-6345c29b9886
     * {
     *   "code": 1000,
     *   "msg": "操作成功",
     *   "data": []
     *   }
     */
    public function switchStorehousePosition(Request $request)
    {
        $this->checkAuthority(authModel::AUTH_SPREAD + authModel::AUTH_ADMIN);
        $id = $request->param('id/d', 0);
        if (empty($id)) {
            apiError('参数缺失');
        }
        $info = storehousePositionLogic::getInstance()->find($id);
        if (empty($info)) {
            apiError('仓位信息不存在或已被删除');
        }
        switch ($info['state']) {
            case storehousePositionModel::STATE_BAN:
                $info->state = storehousePositionModel::STATE_OK;
                break;
            default:
                $info->state = storehousePositionModel::STATE_BAN;
        }
        if (!$info->save()) {
            apiError('操作失败，请稍后再试');
        }
        apiSuccess('操作成功');
    }

    /**
     * 删除仓库仓位信息
     * @param Request $request = [
     *      'id' => 1,              //仓位信息id
     * ]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @see 权限：平台、总后台
     * @Route("deleteStorehousePosition")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @example 成功示例：https://documenter.getpostman.com/view/6090259/SWECWaGL?version=latest#89ef2dd7-c5bd-491e-b755-6345c29b9886
     * {
     *   "code": 1000,
     *   "msg": "操作成功",
     *   "data": []
     *   }
     */
    public function deleteStorehousePosition(Request $request)
    {
        $this->checkAuthority(authModel::AUTH_SPREAD + authModel::AUTH_ADMIN);
        $id = $request->param('id/d', 0);
        if (empty($id)) {
            apiError('参数缺失');
        }
        if (!storehousePositionLogic::getInstance()->deleteInfo($id)) {
            apiError(getLastError());
        }
        apiSuccess('操作成功');
    }
}
