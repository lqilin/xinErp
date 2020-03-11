<?php
declare (strict_types=1);

namespace app\validate\storehouse;

use app\logic\storehouse\storehousePositionLogic;
use app\model\storehouse\storehousePositionModel;
use think\Validate;

class storehousePositionValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'id|仓位信息' => 'require|number|max:20',
        'spread_id|平台信息' => 'require|number|max:20',
        'storehouse_id|指定仓库信息' => 'require|number|max:20',
        'position_name|仓位名称' => 'require|max:100|checkName',
        'address|仓位地址' => 'require|max:100',
        'contact|联系人' => 'max:20',
        'phone|联系人手机' => 'mobile',
        'email|联系人邮箱' => 'email|max:100',
        'is_default|是否默认' => 'require|checkDefault',
    ];

    /**
     * 验证添加仓位信息
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkName($value, $rule, $data)
    {
        $where = [
            'spread_id' => $data['spread_id'],
            'storehouse_id' => $data['storehouse_id'],
            'position_name' => $value
        ];
        $info = storehousePositionLogic::getInstance()->where($where)->find();
        if (!empty($info)) {
            return '仓位已经存在，不可重复添加';
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
        $where = [
            'id' => $data['id'],
            'spread_id' => $data['spread_id'],
            'storehouse_id' => $data['storehouse_id'],
        ];
        $info = storehousePositionLogic::getInstance()->where($where)->find();
        if (empty($info)) {
            return '仓位信息不存在或已被删除';
        }
        if ($info['position_name'] != $value){
            $newWhere = [
                'spread_id' => $data['spread_id'],
                'storehouse_id' => $data['storehouse_id'],
                'position_name' => $value,
            ];
            $newInfo = storehousePositionLogic::getInstance()->where($newWhere)->find();
            if (!empty($newInfo)) {
                return '仓位信息已经存在';
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
        if (in_array($value, [storehousePositionModel::IS_DEFAULT, storehousePositionModel::NO_DEFAULT])) {
            return true;
        }
        return '默认状态有误，请重新选择';
    }

    /**
     * @return storehousePositionValidate 验证新增数据场景
     */
    public function sceneAdd()
    {
        return $this->only(['spread_id', 'storehouse_id', 'position_name', 'address', 'contact', 'phone', 'email', 'is_default']);
    }

    /**
     * @return storehousePositionValidate 验证编辑数据场景
     */
    public function sceneEdit()
    {
        return $this->only(['id', 'spread_id', 'storehouse_id', 'position_name', 'address', 'contact', 'phone', 'email', 'is_default'])
            ->remove(['position_name:checkName'])
            ->append(['position_name:checkEditName']);
    }
}
