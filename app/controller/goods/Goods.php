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

namespace app\controller\goods;

use app\controller\Base;
use app\logic\goods\goodsLogic;
use app\model\authModel;
use app\model\commonModel;
use app\Request;
use app\validate\goods\goodsValidate;
use think\exception\ValidateException;
use app\middleware\AllowOriginMiddleware;
use app\middleware\AuthTokenMiddleware;
use think\annotation\route\Middleware;
use think\annotation\Route;

class Goods extends Base
{
    /**
     * 获取商品列表
     * @param Request $request
     * @Route("getGoodsList")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @throws \think\db\exception\DbException
     * @example 成功示例：https://documenter.getpostman.com/view/6090259/SWECWaGL?version=latest#89ef2dd7-c5bd-491e-b755-6345c29b9886
     * {
     *   "code": 1000,
     *   "msg": "操作成功",
     *   "data": []
     *   }
     */
    public function getGoodsList(Request $request)
    {
        $param = $request->only(['supplier_id', 'storehouse_id', 'storehouse_position_id', 'brand_id', 'keyword', 'relevance']);
        $page = $request->param('page/d', commonModel::DEFAULT_PAGE);
        $size = $request->param('size/d', commonModel::DEFAULT_SIZE);
        $data = goodsLogic::getInstance()->getGoodsList($param, $page, $size);
        apiPaginate($data);
    }


    /**
     * 保存商品信息
     * @param Request $request
     * @Route("saveGoods")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @throws \think\db\exception\DbException
     * @example 成功示例：https://documenter.getpostman.com/view/6090259/SWECWaGL?version=latest#89ef2dd7-c5bd-491e-b755-6345c29b9886
     * {
     *   "code": 1000,
     *   "msg": "操作成功",
     *   "data": []
     *   }
     */
    public function saveGoods(Request $request)
    {
        $fields = ['id', 'supplier_id', 'storehouse_id', 'storehouse_position_id', 'brand_id', 'goods_name', 'unit', 'cost_price', 'purchase_price', 'trade_price', 'price', 'stock', 'thumb', 'desc', 'remark', 'state', 'is_top', 'is_hot', 'is_recommend'];
        $param = $request->only($fields, 'post', 'trim');
        $category = $request->param('category', '', 'trim');
        $keywords = $request->param('keywords');
        $goodsImages = $request->param('goods_images', '', 'trim');
        $specItem = $request->param('spec_item', '', 'trim');
        $itemImg = $request->param('item_img', '', 'trim');
        try {
            if (!empty($param['id'])) {
                validate(goodsValidate::class)->scene('edit')->check($param);
            } else {
                validate(goodsValidate::class)->scene('add')->check($param);
            }
        } catch (ValidateException $exception) {
            apiError($exception->getError());
        }
        if (!goodsLogic::getInstance()->saveGoods($param, $category, $keywords, $goodsImages, $specItem, $itemImg)) {
            apiError(getLastError());
        }
        apiSuccess('操作成功');
    }

    /**
     * 上下架商品信息
     * @param Request $request
     * @Route("upDownGoods")
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
    public function upDownGoods(Request $request)
    {
        $id = $request->post('id/d', 0);
        if (!goodsLogic::getInstance()->upDownGoods($id)) {
            apiError(getLastError());
        }
        apiSuccess('操作成功');
    }

    /**
     * 切换商品新品状态
     * @param Request $request
     * @Route("goodsNew")
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
    public function goodsNew(Request $request)
    {
        $id = $request->post('id/d', 0);
        if (!goodsLogic::getInstance()->goodsNew($id)) {
            apiError(getLastError());
        }
        apiSuccess('操作成功');
    }

    /**
     * 切换商品热销状态
     * @param Request $request
     * @Route("goodsHot")
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
    public function goodsHot(Request $request)
    {
        $id = $request->post('id/d', 0);
        if (!goodsLogic::getInstance()->goodsHot($id)) {
            apiError(getLastError());
        }
        apiSuccess('操作成功');
    }

    /**
     * 切换商品推荐状态
     * @param Request $request
     * @Route("goodsRecommend")
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
    public function goodsRecommend(Request $request)
    {
        $id = $request->post('id/d', 0);
        if (!goodsLogic::getInstance()->goodsRecommend($id)) {
            apiError(getLastError());
        }
        apiSuccess('操作成功');
    }

    /**
     * 删除商品信息
     * @param Request $request
     * @Route("deleteGoods")
     * @Middleware({AllowOriginMiddleware::class, AuthTokenMiddleware::class})
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @see 店铺、平台、总后台
     * @example 成功示例：https://documenter.getpostman.com/view/6090259/SWECWaGL?version=latest#89ef2dd7-c5bd-491e-b755-6345c29b9886
     * {
     *   "code": 1000,
     *   "msg": "操作成功",
     *   "data": []
     *   }
     */
    public function deleteGoods(Request $request)
    {
        $this->checkAuthority(authModel::AUTH_SHOP + authModel::AUTH_SPREAD + authModel::AUTH_ADMIN);
        $id = $request->post('id/d', 0);
        if (!goodsLogic::getInstance()->deleteInfo($id)) {
            apiError(getLastError());
        }
        apiSuccess('操作成功');
    }
}
