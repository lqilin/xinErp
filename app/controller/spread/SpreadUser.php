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

namespace app\controller\spread;

use app\controller\Base;
use app\logic\spread\spreadUserLogic;
use app\model\authModel;
use app\model\commonModel;
use think\annotation\Route;
use think\annotation\route\Middleware;
use app\middleware\AllowOriginMiddleware;
use app\middleware\AuthTokenMiddleware;
use think\Request;

class SpreadUser extends Base
{
    /**
     * 获取平台用户列表信息
     * @param \app\Request $request
     * @Route("spreadUserList")
     * @throws \think\db\exception\DbException
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @example 成功示例：https://documenter.getpostman.com/view/6090259/SWECWaGL?version=latest#89ef2dd7-c5bd-491e-b755-6345c29b9886
     * {
     *   "code": 1000,
     *   "msg": "操作成功",
     *   "data": []
     *   }
     */
    public function spreadUserList(Request $request)
    {
        $this->checkAuthority(authModel::AUTH_ADMIN + authModel::AUTH_SPREAD);
        $param = $request->only(['id', 'state', 'start_time', 'end_time', 'keyword'], [], 'trim');
        $page = $request->param('page/d', commonModel::DEFAULT_PAGE);
        $size = $request->param('size/d', commonModel::DEFAULT_SIZE);
        $data = spreadUserLogic::getInstance()->getUserList($param, $page, $size);
        apiPaginate($data);
    }
}
