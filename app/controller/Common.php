<?php
declare (strict_types=1);

namespace app\controller;

use app\logic\custom\customLogic;
use app\logic\shop\shopLogic;
use app\logic\spread\spreadLogic;
use app\model\commonModel;
use app\Request;
use think\annotation\Route;

class Common
{
    /**
     * 获取平台列表信息
     * @param Request $request = [
     *          'state' => 1,               //状态：1启用 2禁用
     *          'start_time' => '2019-01-01',  //查询开始时间
     *          'end_time' => '2019-01-01',  //查询结束时间
     *          'keyword' => 'nicai',        //关键词
     * ]
     * @Route("commonSpreadList")
     * @throws \think\db\exception\DbException
     */
    public function commonSpreadList(Request $request)
    {
        $param = $request->only(['state', 'keyword', 'start_time', 'end_time']);
        $page = $request->param('page/d', commonModel::DEFAULT_PAGE);
        $size = $request->param('size/d', commonModel::DEFAULT_SIZE);
        $data = spreadLogic::getInstance()->getSpreadList($param, $page, $size);
        apiPaginate($data);
    }

    /**
     * 获取店铺列表信息
     * @param Request $request = [
     *          'state' => 1,               //状态：1启用 2禁用
     *          'start_time' => '2019-01-01',  //查询开始时间
     *          'end_time' => '2019-01-01',  //查询结束时间
     *          'keyword' => 'nicai',        //关键词
     * ]
     * @Route("commonShopList")
     * @throws \think\db\exception\DbException
     */
    public function commonShopList(Request $request)
    {
        $param = $request->only(['spread_token', 'state', 'keyword', 'start_time', 'end_time']);
        $page = $request->param('page/d', commonModel::DEFAULT_PAGE);
        $size = $request->param('size/d', commonModel::DEFAULT_SIZE);
        $data = shopLogic::getInstance()->getShopList($param, $page, $size);
        apiPaginate($data);
    }

    /**
     * 获取客户列表
     * @param Request $request = [
     *          'state' => 1,                //状态：1启用 2禁用
     *          'start_time' => '2019-01-01',//查询开始时间
     *          'end_time' => '2019-01-01',  //查询结束时间
     *          'keyword' => 'nicai',        //关键词
     * ]
     * @Route("commonCustomList")
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function commonCustomList(Request $request)
    {
        $param = $request->only(['state', 'keyword', 'start_time', 'end_time']);
        $spreadToken = $request->param('spread_token/s');
        if (!empty($spreadToken)) {
            $param['spread_id'] = decodeStr($spreadToken);
        }
        $page = $request->param('page/d', commonModel::DEFAULT_PAGE);
        $size = $request->param('size/d', commonModel::DEFAULT_SIZE);
        $data = customLogic::getInstance()->getCustomList($param, $page, $size);
        apiPaginate($data);
    }
}
