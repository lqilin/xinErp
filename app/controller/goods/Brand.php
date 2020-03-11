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

namespace app\controller\goods;

use app\controller\Base;
use app\logic\goods\goodsBrandLogic;
use app\model\authModel;
use app\model\commonModel;
use app\Request;
use app\validate\goods\goodsBrandValidate;
use think\annotation\Route;
use app\middleware\AllowOriginMiddleware;
use app\middleware\AuthTokenMiddleware;
use think\annotation\route\Middleware;
use think\exception\ValidateException;

class Brand extends Base
{
    /**
     * 获取品牌信息列表
     * @param Request $request
     * @Route("brandList")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @throws \think\db\exception\DbException
     */
    public function brandList(Request $request)
    {
        $param = $request->only(['id', 'start_time', 'end_time', 'keyword'], [], 'trim');
        $page = $request->param('page/d', commonModel::DEFAULT_PAGE);
        $size = $request->param('size/d', commonModel::DEFAULT_SIZE);
        $data = goodsBrandLogic::getInstance()->getBrandList($param, $page, $size);
        apiPaginate($data);
    }

    /**
     * 保存品牌信息
     * @param Request $request
     * @Route("saveBrand")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveBrand(Request $request)
    {
        $this->checkAuthority(authModel::AUTH_SPREAD);
        $param = $request->only(['id', 'brand_name'], 'post', 'trim');
        try {
            if (!empty($param['id'])) {
                validate(goodsBrandValidate::class)->scene('edit')->check($param);
            }else{
                validate(goodsBrandValidate::class)->scene('add')->check($param);
            }
        }catch (ValidateException $exception) {
            apiError($exception->getError());
        }
        if (!goodsBrandLogic::getInstance()->saveBrand($param)) {
            apiError(getLastError());
        }
        apiSuccess('操作成功');
    }

    /**
     * 删除品牌信息
     * @param Request $request
     * @Route("deleteBrand")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function deleteBrand(Request $request)
    {
        $this->checkAuthority(authModel::AUTH_SPREAD + authModel::AUTH_ADMIN);
        $id = $request->param('id/d', 0);
        if (!goodsBrandLogic::getInstance()->deleteInfo($id)) {
            apiError(getLastError());
        }
        apiSuccess('操作成功');
    }
}
