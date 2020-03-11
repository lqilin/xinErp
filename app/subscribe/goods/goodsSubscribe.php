<?php
declare (strict_types=1);

namespace app\subscribe\goods;

use app\job\goods\saveGoodsJob;
use think\facade\Log;
use think\facade\Queue;

class goodsSubscribe
{
    /**
     * 后置处理保存商品其他信息
     */
    const AFTER_SAVE_GOODS = 'AfterSaveGoods';

    public function handle()
    {

    }

    /**
     * 保存商品其他信息后置操作
     * @param $event = [$goodsId, $categories, $keywords, $attrs, $goodsImages, $item, $itemImg];
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function onAfterSaveGoods($event)
    {
        //发送消息推送到队列中
        if (!Queue::push('app\job\goods\saveGoodsJob', $event, saveGoodsJob::SAVE_GOODS_DESC)) {
            Log::notice('push to queue is failed, the queue is ' . saveGoodsJob::SAVE_GOODS_DESC, $event);
            if (!saveGoodsJob::getInstance()->doneJob($event)) {
                Log::error('onAfterSaveGoods is failed, the queue is ' . saveGoodsJob::SAVE_GOODS_DESC, $event);
                return false;
            }
        }
        Log::info('push to queue is success, the queue is ' . saveGoodsJob::SAVE_GOODS_DESC, [date('Y-m-d H:i:s', time()), $event]);
        return true;
    }
}
