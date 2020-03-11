<?php
declare (strict_types = 1);

namespace app\validate\custom;

use think\Validate;

class customValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
	protected $rule = [
        'custom_category_id|客户类别' => 'require|number|max:20',
        'phone|客户手机号码' => 'require|mobile|max:11|number',
        'sex|性别' => 'number|in:1,2,3',
        'birthday|生日' => 'date|expire:1990-1-1,2020-1-1',
        'remark|备注信息' => 'max:255',
        'password|客户密码' => 'length:6,24',
    ];
}
