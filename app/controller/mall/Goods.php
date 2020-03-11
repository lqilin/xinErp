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

namespace app\controller\mall;

use app\logic\mall\mallGoodsModelLogic;
use app\logic\mall\mallGoodsTypeLogic;
use app\model\commonModel;
use app\Request;
use app\validate\mall\mallGoodsModelValidate;
use app\validate\mall\mallGoodsTypeValidate;
use think\annotation\Route;
use think\annotation\route\Group;
use think\annotation\route\Middleware;
use app\middleware\AllowOriginMiddleware;
use app\middleware\AuthTokenMiddleware;
use think\exception\ValidateException;

/**
 * @Group("mall")
 * @package app\controller\mall
 * project xinErp
 */
class Goods
{
    /**
     * 获取平台商品模型列表
     * @param Request $request
     * @Route("getMallGoodsModel")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @throws \think\db\exception\DbException
     */
    public function getMallGoodsModel(Request $request)
    {
        $param = $request->only(['start_time', 'end_time', 'keyword', 'state']);
        $page = $request->param('page/d', commonModel::DEFAULT_PAGE);
        $size = $request->param('size/d', commonModel::DEFAULT_SIZE);
        $data = mallGoodsModelLogic::getInstance()->getMallGoodsModelList($param, $page, $size);
        apiPaginate($data);
    }

    /**
     * 保存平台商品模型
     * @param Request $request
     * @Route("saveMallGoodsModel")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveMallGoodsModel(Request $request)
    {
        $param = $request->only(['id', 'model_name', 'state'], 'post', 'trim');
        $attrs = $request->param('attrs');
        try{
            if (!empty($param['id'])){
                validate(mallGoodsModelValidate::class)->scene('edit')->check($param);
            }else {
                validate(mallGoodsModelValidate::class)->scene('add')->check($param);
            }
        }catch (ValidateException $exception) {
            apiError($exception->getError());
        }
        if (!mallGoodsModelLogic::getInstance()->saveModel($param, $attrs)) {
            apiError(getLastError());
        }
        apiSuccess('操作成功');
    }

    public function deleteModel(Request $request)
    {
        //
    }

    /**
     * 获取平台商品类型列表
     * @param Request $request
     * @Route("getTypeList")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getTypeList(Request $request)
    {
        $param = $request->only(['start_time', 'end_time', 'keyword'], 'get', 'trim');
        $page = $request->param('page/d', commonModel::DEFAULT_PAGE);
        $size = $request->param('size/d', commonModel::DEFAULT_SIZE);
        $data = mallGoodsTypeLogic::getInstance()->getTypeList($param, $page, $size);
        apiPaginate($data);
    }

    /**
     * 保存平台商品类型
     * @param Request $request
     * @Route("saveType")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveType(Request $request)
    {
        $param = $request->only(['name'], 'post', 'trim');
        $spec = $request->post('spec', '', 'trim');
        $spec = json_decode($spec, true);
        try {
            validate(mallGoodsTypeValidate::class)->check($param);
        }catch (ValidateException $exception) {
            apiError($exception->getError());
        }
        if (!mallGoodsTypeLogic::getInstance()->saveType($param, $spec)) {
            apiError(getLastError());
        }
        apiSuccess('操作成功');
    }
}
