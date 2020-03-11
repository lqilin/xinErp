<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 逻辑层
 * +----------------------------------------------------------------------
 * DATE 2020-02-03 16:57
 * @package app\logic\goods
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\goods;

use app\logic\BaseLogic;
use app\logic\spread\spreadLogic;
use think\facade\Db;
use think\facade\Log;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;
use think\model\relation\BelongsToMany;

class goodsKeywordLogic extends BaseLogic
{
    use SoftDelete;

    /**
     * @var string 数据表名
     */
    protected $name = 'goods_keyword';

    /**
     * @var string 数据表主键
     */
    protected $pk = 'id';

    /**
     * @var bool 是否开启字段时间戳字段
     */
    protected $autoWriteTimestamp = true;

    /**
     * @var string 默认软删除字段
     */
    protected $deleteTime = 'delete_time';

    /**
     * @var int 默认软删除时间
     */
    protected $defaultSoftDelete = 0;

    /**
     * @var array 关联数据表信息
     */
    protected $relevance = [
        'spread',
        'goods',
    ];

    /**
     * 初始化
     * goodsKeywordLogic constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->autoObject['spread_id'] = $this->getSpreadId();
        $this->setWithModel();
    }

    /**
     * @return BelongsTo 关联平台信息
     */
    public function spread(): BelongsTo
    {
        return $this->belongsTo(spreadLogic::class, 'spread_id');
    }

    /**
     * @return BelongsToMany 关联商品信息
     */
    public function goods(): BelongsToMany
    {
        return $this->belongsToMany(goodsLogic::class, goodsKeywordMiddlewareLogic::class, 'goods_id', 'keyword_id');
    }

    /**
     * 增加关键词
     * @param goodsLogic $goods
     * @param array      $keywords
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveGoodsKeywords(goodsLogic $goods, array $keywords)
    {
        $keywordIds = [];
        $attachKeywordIds = [];
        $oldKeywordIds = [];
        $oldKeyword = $goods->keywords()->select();
        $this->spreadId = $goods['spread_id'];
        if (!empty($oldKeyword)) {
            $oldKeywordIds = array_column($oldKeyword->toArray(), 'id');
        }
        $this->startTrans();
        if (!empty($keywords)) {
            foreach ($keywords as $keyword) {
                $keyword = trim($keyword);
                if (!empty($keyword)) {
                    $findKeyword = goodsKeywordLogic::getInstance()->where('keyword', $keyword)->find();
                    if (empty($findKeyword)) {
                        if (!$keywordLogic = goodsKeywordLogic::getInstance()->create([
                            'keyword' => $keyword,
                        ])) {
                            $this->rollback();
                            Log::notice('addKeywords is failed, 关键词创建失败，请稍后再试', [$keyword]);
                            recordError('关键词创建失败，请稍后再试');
                            return false;
                        }
                        $keywordId = $keywordLogic['id'];
                    } else {
                        $keywordId = $findKeyword['id'];
                    }
                    if (!in_array($keywordId, $oldKeywordIds)) {
                        array_push($attachKeywordIds, $keywordId);
                    }
                    array_push($keywordIds, $keywordId);
                }
            }
            $sameKeywordIds = array_intersect($oldKeywordIds, $keywordIds);
            $shouldDeleteKeywordIds = array_diff($oldKeywordIds, $sameKeywordIds);
            if (!empty($shouldDeleteKeywordIds)) {
                if (!$goods->keywords()->detach($shouldDeleteKeywordIds)) {
                    $this->rollback();
                    Log::notice('addKeywords is failed, 删除商品应该删除的所有关键词失败');
                    recordError('删除商品所有关键词失败');
                    return false;
                }
                if (!goodsKeywordLogic::getInstance()->where('id', 'in', $shouldDeleteKeywordIds)->save(['goods_count' => Db::raw('goods_count - 1')])) {
                    $this->rollback();
                    Log::notice('addKeywords is failed, 减少关键词商品数量失败');
                    recordError('减少关键词商品数量失败');
                    return false;
                }
            }
            if (!empty($attachKeywordIds)) {
                if (!$goods->keywords()->attach($attachKeywordIds)) {
                    $this->rollback();
                    Log::notice('addKeywords is failed, 增加关键词关联失败');
                    recordError('增加关键词关联失败');
                    return false;
                }
                if (!goodsKeywordLogic::getInstance()->where('id', 'in', $attachKeywordIds)->save(['goods_count' => Db::raw('goods_count + 1')])) {
                    $this->rollback();
                    Log::notice('addKeywords is failed, 增加关键词商品数量失败');
                    recordError('增加关键词商品数量失败');
                    return false;
                }
            }
        } else {
            if (!$goods->keywords()->detach()) {
                $this->rollback();
                Log::notice('addKeywords is failed, 由于未填写关键词，该商品的关键词关联删除，删除失败');
                recordError('关键词关联删除，删除失败');
                return false;
            }
        }
        $this->commit();
        return true;
    }
}