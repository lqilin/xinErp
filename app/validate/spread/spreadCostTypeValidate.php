<?php
declare (strict_types=1);

namespace app\validate\spread;

use app\logic\spread\spreadCostTypeLogic;
use think\Validate;

class spreadCostTypeValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'id|费用信息' => 'require|number|max:20',
        'spread_id|平台信息' => 'require|number|max:20',
        'cost_name|费用名称' => 'require|max:20|checkCostName',
    ];

    /**
     * 检测费用名称
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkCostName($value, $rule, $data)
    {
        $info = spreadCostTypeLogic::getInstance()->where(['spread_id' => $data['spread_id'], 'cost_name' => $value])->find();
        if (!empty($info)) {
            return '该费用信息已经存在';
        }
        return true;
    }

    /**
     * 检测编辑费用名称
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
        $info = spreadCostTypeLogic::getInstance()->where(['id' => $data['id'], 'spread_id' => $data['spread_id']])->find();
        if (!empty($info)) {
            if ($info['cost_name'] != $value) {
                $newInfo = spreadCostTypeLogic::getInstance()->where(['spread_id' => $data['spread_id'], 'cost_name' => $value])->find();
                if (!empty($newInfo)) {
                    return '该费用信息已经存在';
                }
            }
        }
        return true;
    }

    /**
     * 新增平台费用场景
     * @return spreadCostTypeValidate
     */
    public function sceneAdd()
    {
        return $this->only(['spread_id', 'cost_name']);
    }

    /**
     * 编辑平台费用信息场景
     * @return spreadCostTypeValidate
     */
    public function sceneEdit()
    {
        return $this
            ->only(['id', 'spread_id', 'cost_name'])
            ->remove('cost_name:checkCostName')
            ->append('cost_name:checkEditName');
    }
}
