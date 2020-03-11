<?php
declare (strict_types=1);

namespace app\validate\storehouse;

use app\logic\storehouse\storehouseLogic;
use app\model\storehouse\storehouseModel;
use think\Validate;

class storehouseValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'id|仓库信息' => 'require|number|max:20',
        'storehouse_name|仓库名称' => 'require|max:100|checkName',
        'address|仓库地址' => 'require|max:100',
        'contact|联系人' => 'require|max:20',
        'tel|联系人电话(座机)' => 'checkTel',
        'phone|联系人手机' => 'require|mobile',
        'email|联系人邮箱' => 'require|email|max:100',
        'is_default|是否默认' => 'require|checkDefault',
        'stock_size|库存容量' => 'max:11|number',
        'opening_inventory|期初库存' => 'max:11|number|checkOpening',
        'long|经度' => 'require|float|max:11',
        'lat|纬度' => 'require|float|max:11',
    ];

    /**
     * 验证联系人电话号码输入是否正确
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     */
    public function checkTel($value, $rule, $data)
    {
        if (!isTel($value)) {
            return '联系人电话号码输入有误';
        }
        return true;
    }

    /**
     * 验证添加仓库信息
     * @param $value
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkName($value)
    {
        $info = storehouseLogic::getInstance()->where(['spread_id' => request()->spreadId, 'storehouse_name' => $value])->find();
        if (!empty($info)) {
            return '仓库信息已经存在';
        }
        return true;
    }

    /**
     * 验证编辑仓库信息
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
        $info = storehouseLogic::getInstance()->find($data['id']);
        if (empty($info)) {
            return '仓库信息不存在或已被删除';
        }
        if ($info['storehouse_name'] != $value) {
            $infoNew = storehouseLogic::getInstance()->where(['spread_id' => request()->spreadId, 'storehouse_name' => $value])->find();
            if (!empty($infoNew)) {
                return '仓库信息已经存在';
            }
        }
        return true;
    }

    /**
     * 验证期初库存
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     */
    public function checkOpening($value, $rule, $data)
    {
        if (!empty($data['stock_size'])) {
            if ($value > $data['stock_size']) {
                return '期初库存不能大于仓库容量';
            }
        }
        return true;
    }

    /**
     * 验证是否默认
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     */
    public function checkDefault($value, $rule, $data)
    {
        if (in_array($value, [storehouseModel::IS_DEFAULT, storehouseModel::NO_DEFAULT])) {
            return true;
        }
        return '默认状态有误，请重新选择';
    }

    /**
     * 添加仓库场景
     * @return storehouseValidate
     */
    public function sceneAdd()
    {
        return $this
            ->only(['spread_id', 'storehouse_name', 'address', 'contact', 'tel', 'phone', 'email', 'is_default', 'opening_inventory', 'stock_size']);
    }

    /**
     * 编辑仓库信息验证场景
     * @return storehouseValidate
     */
    public function sceneEdit()
    {
        return $this
            ->only(['spread_id', 'storehouse_name', 'address', 'contact', 'tel', 'phone', 'email', 'is_default', 'opening_inventory', 'stock_size'])
            ->remove('storehouse_name', 'checkName')
            ->append('storehouse_name', 'checkEditName');
    }
}
