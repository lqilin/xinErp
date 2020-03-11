<?php
declare (strict_types=1);

namespace app\validate\spread;

use app\logic\spread\spreadClearFormLogic;
use think\Validate;

class spreadClearFormValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'id|结算类型信息' => 'require|number|max:20',
        'spread_id|平台信息' => 'require|number|max:20',
        'clearing_form|结算方式' => 'require|max:25|checkForm',
    ];

    /**
     * 验证添加数据信息
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkForm($value, $rule, $data)
    {
        $info = spreadClearFormLogic::getInstance()->where(['spread_id' => $data['spread_id'], 'clearing_form' => $value])->find();
        if (!empty($info)) {
            return '结算信息已经存在';
        }
        return true;
    }

    /**
     * 验证编辑数据信息
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkEditForm($value, $rule, $data)
    {
        $info = spreadClearFormLogic::getInstance()->where(['spread_id' => $data['spread_id'], 'id' => $data['id']])->find();
        if (!empty($info)) {
            if ($info['clearing_form'] != $value) {
                $newInfo = spreadClearFormLogic::getInstance()->where(['spread_id' => $data['spread_id'], 'clearing_form' => $value])->find();
                if (!empty($newInfo)) {
                    return '结算信息已经存在';
                }
            }
        }
        return true;
    }


    /**
     * 添加结算类型场景
     * @return spreadClearFormValidate
     */
    public function sceneAdd()
    {
        return $this
            ->only(['spread_id', 'clearing_form']);
    }

    /**
     * 编辑结算类型信息场景
     * @return spreadClearFormValidate
     */
    public function sceneEdit()
    {
        return $this->only(['id', 'spread_id', 'clearing_form'])
            ->remove('clearing_form:checkForm')
            ->append('clearing_form:checkEditForm');
    }
}
