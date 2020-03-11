<?php
declare (strict_types=1);

namespace app\validate\mall;

use app\logic\mall\mallGoodsModelLogic;
use think\Validate;

class mallGoodsModelValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'id|模型信息' => 'require|number|max:20',
        'model_name|模型名称' => 'require|max:50|checkName',
        'state|模型状态' => 'number|in:1,2'
    ];

    /**
     * 验证模型名称是否已经存在
     * @param $value
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkName($value)
    {
        $info = mallGoodsModelLogic::getInstance()->where('model_name', $value)->find();
        if (!empty($info)) {
            return '模型已经存在';
        }
        return true;
    }

    /**
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
        $info = mallGoodsModelLogic::getInstance()->where('id', $data['id'])->find();
        if (empty($info)) {
            return '模型信息不存在或已被删除';
        }
        if ($info['model_name'] != $value) {
            $newInfo = mallGoodsModelLogic::getInstance()->where('model_name', $value)->find();
            if (!empty($newInfo)) {
                return '模型已经存在';
            }
        }
        return true;
    }

    /**
     * 添加数据场景
     * @return mallGoodsModelValidate
     */
    public function sceneAdd()
    {
        return $this->only(['model_name', 'state']);
    }

    /**
     * 编辑数据场景
     * @return mallGoodsModelValidate
     */
    public function sceneEdit()
    {
        return $this->only(['id', 'model_name', 'state'])
            ->remove(['model_name' => 'checkName'])
            ->append(['model_name' => 'checkEditName']);
    }
}
