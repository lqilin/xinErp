<?php
declare (strict_types=1);

namespace app\validate;

use think\Validate;

class authValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'shop_id|店铺信息' => 'require|number',
        'username|登录账号' => 'require|length:2,50',
        'password|密码' => 'require',
        'spread_token|平台信息' => 'require|max:20',
        'user_login|用户名' => 'require|chsDash',
        'mobile|电话号码' => 'require|mobile',
        'confirm_password|重复密码' => 'require|confirm:password',
    ];

    /**
     * 客户登录场景
     * @return authValidate
     */
    public function sceneCustomLogin()
    {
        return $this->only(['spread_token', 'username', 'password']);
    }

    /**
     * 平台登录场景
     * @return authValidate
     */
    public function sceneSpreadLogin()
    {
        return $this->only(['spread_token', 'username', 'password']);
    }

    /**
     * 店铺登录场景
     * @return authValidate
     */
    public function sceneShopLogin()
    {
        return $this->only(['spread_token', 'shop_id', 'username', 'password']);
    }

    /**
     * 平台登录场景
     * @return authValidate
     */
    public function sceneAdminLogin()
    {
        return $this->only(['username', 'password']);
    }
}
