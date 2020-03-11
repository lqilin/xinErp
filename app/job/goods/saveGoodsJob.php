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

namespace app\job\goods;

use app\library\traits\instanceTrait;
use app\logic\goods\goodsAttrLogic;
use app\logic\goods\goodsCategoryLogic;
use app\logic\goods\goodsImagesLogic;
use app\logic\goods\goodsKeywordLogic;
use app\logic\goods\goodsLogic;
use app\logic\goods\goodsSpecLogic;
use think\facade\Log;
use think\queue\Job;

class saveGoodsJob
{
    use instanceTrait;

    /**
     * desc 队列标签：saveGoodsQueue 保存商品队列
     */
    const SAVE_GOODS_DESC = 'saveGoodsJob';

    /**
     * @param Job $job
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function fire(Job $job, array $data)
    {
        $attempts = $job->attempts() + 1;
        if (!$this->doJob($job, $data)) {
            print('<info>[' . date('Y-m-d H:i:s', time()) . "] 保存商品后置操作任务执行失败，即将执行第" . $attempts . "次任务</info>\n");
            $job->release();
        }
        print('<info>[' . date('Y-m-d H:i:s', time()) . "] 保存商品后置操作任务执行完成，任务销毁</info>\n");
        Log::info('saveGoodsJob The queue consumption was successful and the destruction task' . self::SAVE_GOODS_DESC . ' was imminent.');
        $job->delete();
    }

    /**
     * 执行任务
     * @param Job $job
     * @param array $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function doJob(Job $job, array $data): bool
    {
        Log::info('saveGoodsJob is begin, the param is', $data);
        print('<info>[' . date('Y-m-d H:i:s', time()) . "] 保存商品后置操作任务执行第" . $job->attempts() . "次任务</info>\n");
        if (!$this->doneJob($data)) {
            return false;
        }
        return true;
    }

    /**
     * @param array $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function doneJob(array $data): bool
    {
        [$goodsId, $categories, $keywords, $attrs, $goodsImages, $goodsItem, $itemImg] = $data;
        //保存商品关键词
        $goodsInfo = goodsLogic::getInstance()->find($goodsId);
        if (empty($goodsInfo)) {
            Log::notice('saveGoodsJob The item information does not exist or has been deleted.', $data);
            recordError('商品信息不存在或已被删除');
            return false;
        }
        //保存商品分类
        if (!goodsCategoryLogic::getInstance()->saveGoodsCategory($goodsInfo, $categories)) {
            return false;
        }
        //保存商品关键词
        if (!goodsKeywordLogic::getInstance()->saveGoodsKeywords($goodsInfo, $keywords)) {
            return false;
        }
        //保存商品属性信息
        if (!goodsAttrLogic::getInstance()->saveGoodsAttr($goodsInfo, $attrs)) {
            return false;
        }
        //保存商品商品图片
        if (!goodsImagesLogic::getInstance()->saveGoodsImages($goodsInfo, $goodsImages)) {
            return false;
        }
        //保存商品规格信息
        if (!goodsSpecLogic::getInstance()->saveGoodsSpec($goodsInfo, $goodsItem, $itemImg)) {
            return false;
        }
        Log::info('saveGoodsJob is over, the param is', $data);
        return true;
    }
}
