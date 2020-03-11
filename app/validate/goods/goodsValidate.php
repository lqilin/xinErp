<?php
declare (strict_types = 1);

namespace app\validate\goods;

use app\logic\goods\goodsLogic;
use think\Validate;

class goodsValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'id|商品信息' => 'require|number|max:22',
        'supplier_id|供应商' => 'require|number|max:22',
        'storehouse_id|仓库信息' => 'require|number|max:22',
        'storehouse_position_id|仓库仓位信息' => 'number|max:22',
        'brand_id|品牌信息' => 'number|max:22',
        'goods_name|商品名称' => 'require|max:100|checkName',
        'unit|单位' => 'require|max:20|chsAlpha',
        'cost_price|成本价' => 'require|float|max:11',
        'purchase_price|采购价' => 'require|float|egt:cost_price|max:11',
        'trade_price|批发价' => 'require|float|egt:cost_price|max:11',
        'price|销售价' => 'require|float|egt:cost_price|max:11',
        'stock|库存' => 'number|max:11',
        'thumb|商品主图' =>  'require|max:255',
        'remark|商品备注' => 'max:255',
        'state|商品状态' => 'require|number|in:1,2',
        'is_top|置顶' => 'require|number|in:1,2',
        'is_hot|热销' => 'require|number|in:1,2',
        'is_recommend|推荐' => 'require|number|in:1,2',
    ];

    /**
     * 验证添加时商品名称
     * @param $value
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkName($value)
    {
        $info = goodsLogic::getInstance()->where('goods_name', $value)->where('spread_id', request()->spreadId)->find();
        if (!empty($info)) {
            return '商品名称重复';
        }
        return true;
    }

    /**
     * 验证
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkEditName($value, $rule, $data)
    {
        $goods = goodsLogic::getInstance()->where('id', $data['id'])->find();
        if (empty($goods)) {
            return '商品信息不存在或已被删除';
        }
        if ($goods['goods_name'] != $value) {
            $info = goodsLogic::getInstance()->where('goods_name', $value)->where('spread_id', request()->spreadId)->find();
            if (!empty($info)) {
                return '商品名称重复';
            }
        }
        return true;
    }

    /**
     * 添加商品场景
     * @return goodsValidate
     */
    public function sceneAdd()
    {
        return $this
            ->only(['supplier_id', 'storehouse_id', 'storehouse_position_id', 'brand_id', 'goods_name', 'unit', 'cost_price', 'purchase_price', 'trade_price', 'price', 'stock', 'thumb', 'remark', 'state', 'is_top', 'is_hot', 'is_recommend']);
    }

    /**
     * @return goodsValidate
     */
    public function sceneEdit()
    {
        return $this
            ->only(['id', 'supplier_id', 'storehouse_id', 'storehouse_position_id', 'brand_id', 'goods_name', 'unit', 'cost_price', 'purchase_price', 'trade_price', 'price', 'stock', 'thumb', 'remark', 'state', 'is_top', 'is_hot', 'is_recommend'])
            ->remove('goods_name', 'checkName')
            ->append('goods_name', 'checkEditName');
    }
}
