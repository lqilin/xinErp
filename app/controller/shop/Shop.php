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
declare (strict_types = 1);

namespace app\controller\shop;

use app\controller\Base;
use app\logic\shop\shopLogic;
use app\model\authModel;
use app\model\commonModel;
use app\Request;
use app\validate\shop\shopValidate;
use think\annotation\Route;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use app\middleware\AllowOriginMiddleware;
use app\middleware\AuthTokenMiddleware;
use think\exception\ValidateException;

/**
 * @Group("shop")
 * @package app\controller\shop
 * project xinErp
 */
class Shop extends Base
{
    /**
     * 获取店铺列表信息
     * @param Request $request = [
     *          'start_time' => '2019-01-01',
     *          'end_time' => '2019-12-12',
     *          'state' => 1,           //状态1正常 2禁用
     *          'keyword' => 'sd',      //联系电话、联系邮箱、地址|店铺名称
     *          'province' => 10,       //省份id
     *          'city' => 10,           //城市id
     *          'district' => 10,       //区县id
     * ]
     * @Route("shopList")
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
    public function shopList(Request $request)
    {
        $this->checkAuthority(authModel::AUTH_SPREAD + authModel::AUTH_ADMIN);
        $param = $request->only(['start_time', 'end_time', 'state', 'keyword', 'province', 'city', 'district']);
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
        $data = shopLogic::getInstance()->getShopList($param, $page, $size);
        apiPaginate($data);
    }

    /**
     * @param Request $request
     */
    public function save(Request $request)
    {
        $this->checkAuthority(authModel::AUTH_SPREAD);
        $param = $request->only(['user_id', 'shop_name', 'shop_type', 'shop_type_child', 'contact_tel', 'contact_email', 'province', 'city', 'district', 'address', 'map_lon', 'map_lat', 'logo_path', 'service', 'remark']);
        $param['spread_id'] = $request->spreadId;
        try {
            if (!empty($param['id'])) {
                validate(shopValidate::class)->scene('edit')->check($param);
            } else {
                validate(shopValidate::class)->scene('add')->check($param);
            }
        } catch (ValidateException $exception) {
            apiError($exception->getMessage());
        }
        if (!shopLogic::getInstance()->saveShopInfo($param)) {
            apiError(getLastError());
        }
        apiSuccess('操作成功');
    }
}
