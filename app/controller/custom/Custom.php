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

namespace app\controller\custom;

use app\controller\Base;
use app\logic\custom\customLogic;
use app\model\authModel;
use app\model\commonModel;
use app\Request;
use app\validate\custom\customValidate;
use think\annotation\Route;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use app\middleware\AllowOriginMiddleware;
use app\middleware\AuthTokenMiddleware;
use think\exception\ValidateException;

/**
 * Class Custom
 * @Group("custom")
 * @package app\controller\custom
 */
class Custom extends Base
{
    /**
     * 获取客户信息列表
     * @param \app\Request $request = [
     *          'state' => 1,       //状态：1启用 2禁用
     * ]
     * @Route("customList")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
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
    public function customList(Request $request)
    {
        $param = $request->only(['start_time', 'end_time', 'keyword', 'custom_category_id', 'user_id', 'state']);
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
        $data = customLogic::getInstance()->getCustomList($param, $page, $size);
        apiSuccess('获取成功', $data);
    }

    /**
     * 保存客户信息
     * @param Request $request = [
     *          'custom_category_id' => 1,          //客户分类信息
     *          'password' => 'dsadas',             //客户登录密码
     *          'phone' => '18233333333',           //手机号码
     *          'sex' => 1,                         //性别：1男 2女 3未知
     *          'birthday' => '2000-01-01',         //生日
     *          'remark' => '备注信息'              //备注信息
     * ]
     * @Route("saveCustom")
     * @see 权限：平台、管理员
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
    public function saveCustom(Request $request)
    {
        $this->checkAuthority(authModel::AUTH_ADMIN + authModel::AUTH_SPREAD);
        $param = $request->only(['custom_category_id', 'password', 'phone', 'sex', 'birthday', 'remark'], 'post');
        try {
            validate(customValidate::class)->check($param);
        } catch (ValidateException $exception) {
            apiError($exception->getMessage());
        }
        if (!customLogic::getInstance()->saveCustom($param)) {
            apiError(getLastError());
        }
        apiSuccess('操作成功');
    }


}
