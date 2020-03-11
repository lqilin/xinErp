<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 其他出库单(除正常销售外的其他出库，如送给客户赠品等)
 * +----------------------------------------------------------------------
 * DATE 2019-12-07 09:46
 * @package app\validate\order
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\validate\order;

use app\logic\order\OrderLogic;
use app\model\AuthModel;
use think\Validate;

class orderTypeStorehouseOtherOutValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'id|订单信息' => 'require|number|max:20|checkOrder',
        'spread_id|平台信息' => 'require|number|max:20',
        'department_id|部门信息' => 'require|number|max:20',
        'storehouse_id|仓库信息' => 'require|number|max:20',
        'storehouse_position_id|仓位信息' => 'number|max:20',
        'user_id|用户信息' => 'require|number|max:20',
        'form_id|角色信息' => 'require|number|max:20',
        'form_type|角色登录终端' => 'require|number|max:4',
        'order_type|订单类型' => 'require|checkOrderType',
    ];

    /**
     * 验证其他出库单
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     */
    public function checkOrderType($value, $rule, $data)
    {
        $allType = OrderLogic::getInstance()->allType;
        if (!in_array($value, array_keys($allType))) {
            return '订单类型错误，请稍后再试';
        }
        if (!method_exists(OrderLogic::getInstance(), $allType[$value])) {
            return '操作类型错误，请稍后再试';
        }
        return true;
    }

    /**
     * 验证其他出库单信息
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkOrder($value, $rule, $data)
    {
        $info = OrderLogic::getInstance()->find($value);
        if (empty($info)) {
            return '订单信息不存在，请稍后再试';
        }
        if ($info['spread_id'] != $data['spread_id']) {
            return '平台权限验证失败，请稍后再试';
        }
        if ($data['form_type'] & AuthModel::AUTH_CUSTOM) {
            if ($info['form_id'] != $data['form_id']) {
                return '你没有此订单的操作权限';
            }
        }
        return true;
    }

    /**
     * 创建其他出库单场景
     * @return orderTypeStorehouseOtherOutValidate
     */
    public function sceneCreate()
    {
        return $this->only(['spread_id', 'department_id', 'storehouse_id', 'storehouse_position_id', 'user_id', 'form_id', 'form_type', 'order_type']);
    }

    /**
     * 编辑其他出库单场景
     * @return orderTypeStorehouseOtherOutValidate
     */
    public function sceneUpdate()
    {
        return $this->only(['id']);
    }
}
