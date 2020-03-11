<?php
declare (strict_types=1);

namespace app\validate\shop;

use app\logic\shop\shopLogic;
use think\Validate;

class shopValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'id|店铺信息' => 'require|max:20|number',
        'spread_id|平台信息' => 'require|number|max:20',
        'user_id|店铺管理员' => 'require|number|max:20',
        'shop_name|店铺名称' => 'require|max:25|checkName',
        'shop_type|店铺一级分类' => 'require|number|max:11',
        'shop_type_child|店铺二级分类' => 'require|number|max:11',
        'contact_tel|联系电话' => 'require|mobile',
        'contact_qq|联系QQ' => 'max:11|number',
        'contact_email|联系邮箱' => 'max:50|email',
        'province|省份' => 'require|number|max:11',
        'city|市区' => 'require|number|max:11',
        'district|区县' => 'require|number|max:11',
        'address|地址' => 'require|max:100',
        'map_lon|经度' => 'require|float|max:11',
        'map_lat|维度' => 'require|float|max:11',
        'service|经营类目' => 'max:50',
        'remark|备注信息' => 'max:255',
    ];

    /**
     * 验证添加店铺信息
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
        $shop = shopLogic::getInstance()->where(['spread_id' => $data['spread_id'], 'shop_name' => $value])->find();
        if (!empty($shop)) {
            return '店铺已经存在';
        }
        return true;
    }

    /**
     * 验证编辑店铺名称
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
        $oldShop = shopLogic::getInstance()->where(['spread_id' => $data['spread_id'], 'id' => $data['id']])->find();
        if (empty($oldShop)) {
            return '店铺信息不存在或已被删除';
        }
        if ($value != $oldShop['shop_name']) {
            $shop = shopLogic::getInstance()->where(['spread_id' => $data['spread_id'], 'shop_name' => $value])->find();
            if (!empty($shop)) {
                return '店铺已经存在';
            }
        }
        return true;
    }

    /**
     * @return shopValidate 添加店铺信息
     */
    public function sceneAdd()
    {
        $check = ['spread_id', 'user_id', 'shop_name', 'shop_type', 'shop_type_child', 'contact_tel', 'contact_qq', 'contact_email', 'province', 'city', 'district', 'address', 'map_lon', 'map_lat', 'service', 'remark'];
        return $this
            ->only($check);
    }

    /**
     * @return shopValidate 编辑店铺信息
     */
    public function sceneEdit()
    {
        $check = ['id', 'spread_id', 'shop_name', 'shop_type', 'shop_type_child', 'contact_tel', 'contact_qq', 'contact_email', 'province', 'city', 'district', 'address', 'map_lon', 'map_lat', 'service', 'remark'];
        return $this
            ->only($check)
            ->remove('shop_name:checkName')
            ->append('shop_name:checkEditName');
    }
}
