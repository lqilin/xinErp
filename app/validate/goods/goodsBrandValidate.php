<?php
declare (strict_types = 1);

namespace app\validate\goods;

use app\logic\goods\goodsBrandLogic;
use think\Validate;

class goodsBrandValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'id|品牌信息' => 'require|number|max:20',
        'brand_name|品牌名称' => 'require|max:20|checkBrandName',
    ];

    /**
     * 检测品牌名称信息
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
	public function checkBrandName($value, $rule, $data)
    {
        $info = goodsBrandLogic::getInstance()->where([
            'spread_id' => request()->spreadId,
            'brand_name' => $value,
        ])->find();
        if (!empty($info)) {
            return '品牌信息已经存在，请稍候再试';
        }
        return true;
    }

    /**
     * 检测编辑场景的品牌信息
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkEditBrandName($value, $rule, $data)
    {
        $info = goodsBrandLogic::getInstance()->where('id', $data['id'])->find();
        if (empty($info)) {
            return '品牌信息不存在或已被删除';
        }
        if ($info['brand_name'] != $value) {
            $newInfo = goodsBrandLogic::getInstance()->where([
                'spread_id' => request()->spreadId,
                'brand_name' => $value,
            ])->find();
            if (!empty($nerInfo)) {
                return '品牌信息已经存在，请稍候再试';
            }
        }
        return true;
    }

    /**
     * 添加品牌场景
     * @return goodsBrandValidate
     */
    public function sceneAdd()
    {
        return $this
            ->only(['brand_name']);
    }

    /**
     * 编辑品牌场景
     * @return goodsBrandValidate
     */
    public function sceneEdit()
    {
        return $this->only(['id', 'spread_id', 'clearing_form'])
            ->remove('brand_name:checkBrandName')
            ->append('brand_name:checkEditBrandName');
    }
}
